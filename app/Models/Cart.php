<?php

namespace App\Models;

use App\Traits\Multitenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Cart extends Model
{
    use Multitenantable;

    protected $fillable = [
        'customer_id',
        'user_id',
        'estado',
        'subtotal',
        'total',
        'coupon_id',
        'notas',
        'expira_en',
        'empresa_id',
        'sucursal_id',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'expira_en' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Cart $cart) {
            if (empty($cart->codigo ?? null)) {
                // No hay columna codigo en la migración existente, usar ID
            }
        });
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items()->sum('cantidad');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Recalcular totales
     */
    public function recalcular(): void
    {
        $this->unsetRelation('items');

        $this->subtotal = $this->items->sum(fn($item) => $item->cantidad * $item->precio);
        $this->total = $this->subtotal;

        if ($this->coupon) {
            $descuento = $this->coupon->calcularDescuento($this->subtotal);
            $this->total = max(0, $this->subtotal - $descuento);
        }

        $this->save();
    }
}
