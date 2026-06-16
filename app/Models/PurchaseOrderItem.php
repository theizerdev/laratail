<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'product_variant_id',
        'nombre_producto',
        'sku',
        'cantidad_pedida',
        'cantidad_recibida',
        'costo_unitario',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_pedida' => 'integer',
            'cantidad_recibida' => 'integer',
            'costo_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getPendienteAttribute(): int
    {
        return max(0, $this->cantidad_pedida - $this->cantidad_recibida);
    }

    public function getCompletadoAttribute(): bool
    {
        return $this->cantidad_recibida >= $this->cantidad_pedida;
    }
}
