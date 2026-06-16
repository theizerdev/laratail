<?php

namespace App\Models;

use App\Traits\Multitenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class InventoryMovement extends Model
{
    use Multitenantable;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'tipo',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'costo_unitario',
        'sucursal_origen_id',
        'sucursal_destino_id',
        'referencia',
        'motivo',
        'user_id',
        'empresa_id',
        'sucursal_id',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'stock_anterior' => 'integer',
            'stock_nuevo' => 'integer',
            'costo_unitario' => 'decimal:2',
        ];
    }

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function sucursalOrigen(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_origen_id');
    }

    public function sucursalDestino(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_destino_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeDateRange($query, ?string $from, ?string $to)
    {
        if ($from) $query->whereDate('created_at', '>=', $from);
        if ($to) $query->whereDate('created_at', '<=', $to);
        return $query;
    }

    // Color for tipo
    public function getTipoColorAttribute(): string
    {
        return match ($this->tipo) {
            'entrada' => 'emerald',
            'salida' => 'red',
            'ajuste' => 'amber',
            'transferencia' => 'blue',
            default => 'gray',
        };
    }

    public function getTipoIconAttribute(): string
    {
        return match ($this->tipo) {
            'entrada' => 'heroicons:arrow-down-circle-solid',
            'salida' => 'heroicons:arrow-up-circle-solid',
            'ajuste' => 'heroicons:adjustments-horizontal-solid',
            'transferencia' => 'heroicons:arrows-right-left-solid',
            default => 'heroicons:circle-stack-solid',
        };
    }

    public function getTipoLabelAttribute(): string
    {
        return ucfirst($this->tipo);
    }

    /**
     * Register an inventory movement and update product stock atomically.
     */
    public static function registrar(array $data): self
    {
        return DB::transaction(function () use ($data) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);
            $variant = null;

            if (!empty($data['product_variant_id'])) {
                $variant = ProductVariant::lockForUpdate()->findOrFail($data['product_variant_id']);
                $stockActual = $variant->stock;
            } else {
                $stockActual = $product->stock;
            }

            $cantidad = (int) $data['cantidad'];
            $tipo = $data['tipo'];

            $stockNuevo = match ($tipo) {
                'entrada' => $stockActual + $cantidad,
                'salida' => max(0, $stockActual - $cantidad),
                'ajuste' => $cantidad, // cantidad IS the new stock for ajustes
                'transferencia' => $stockActual - $cantidad,
                default => $stockActual,
            };

            // For ajustes, cantidad represents the corrected stock
            $movCantidad = $tipo === 'ajuste' ? abs($stockNuevo - $stockActual) : $cantidad;

            $movement = static::create(array_merge($data, [
                'cantidad' => $movCantidad,
                'stock_anterior' => $stockActual,
                'stock_nuevo' => $stockNuevo,
                'user_id' => $data['user_id'] ?? auth()->id(),
                'empresa_id' => $data['empresa_id'] ?? auth()->user()?->empresa_id,
                'sucursal_id' => $data['sucursal_id'] ?? auth()->user()?->sucursal_id,
            ]));

            // Update stock
            if ($variant) {
                $variant->update(['stock' => $stockNuevo]);
            } else {
                $product->update(['stock' => $stockNuevo]);
            }

            return $movement;
        });
    }
}
