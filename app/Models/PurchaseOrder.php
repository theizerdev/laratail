<?php

namespace App\Models;

use App\Traits\Multitenantable;
use App\Traits\HasSpanishActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseOrder extends Model
{
    use Multitenantable, HasSpanishActivityLog, LogsActivity;

    protected $fillable = [
        'numero',
        'supplier_id',
        'estado',
        'fecha',
        'fecha_entrega_esperada',
        'fecha_recepcion',
        'subtotal',
        'impuesto',
        'total',
        'notas',
        'user_id',
        'empresa_id',
        'sucursal_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'fecha_entrega_esperada' => 'date',
            'fecha_recepcion' => 'date',
            'subtotal' => 'decimal:2',
            'impuesto' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['numero', 'supplier_id', 'estado', 'total', 'fecha'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (PurchaseOrder $po) {
            if (empty($po->numero)) {
                $po->numero = 'OC-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            }
        });
    }

    // Relationships
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // State management
    public function cambiarEstado(string $nuevoEstado): void
    {
        $this->estado = $nuevoEstado;
        if (in_array($nuevoEstado, ['recibida', 'recibida_parcial'])) {
            $this->fecha_recepcion = now();
        }
        $this->save();
    }

    public function recalcular(): void
    {
        $this->subtotal = $this->items->sum('subtotal');
        $this->total = $this->subtotal + $this->impuesto;
        $this->save();
    }

    // Accessors
    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            'borrador' => 'gray',
            'enviada' => 'blue',
            'aprobada' => 'indigo',
            'recibida_parcial' => 'amber',
            'recibida' => 'emerald',
            'cancelada' => 'red',
            default => 'gray',
        };
    }

    public function getEstadoIconAttribute(): string
    {
        return match ($this->estado) {
            'borrador' => 'heroicons:document',
            'enviada' => 'heroicons:paper-airplane-solid',
            'aprobada' => 'heroicons:check-circle-solid',
            'recibida_parcial' => 'heroicons:clock-solid',
            'recibida' => 'heroicons:check-badge-solid',
            'cancelada' => 'heroicons:x-circle-solid',
            default => 'heroicons:document',
        };
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'recibida_parcial' => 'Recibida Parcial',
            default => ucfirst($this->estado),
        };
    }

    public function getPendienteRecepcionAttribute(): bool
    {
        return in_array($this->estado, ['aprobada', 'enviada', 'recibida_parcial']);
    }

    public function getTotalRecibidoAttribute(): int
    {
        return $this->items->sum('cantidad_recibida');
    }

    public function getTotalPedidoAttribute(): int
    {
        return $this->items->sum('cantidad_pedida');
    }

    public function getPorcentajeRecepcionAttribute(): int
    {
        $pedido = $this->total_pedido;
        if ($pedido <= 0) return 0;
        return (int) round(($this->total_recibido / $pedido) * 100);
    }
}
