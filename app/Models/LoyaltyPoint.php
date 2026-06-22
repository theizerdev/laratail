<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyPoint extends Model
{
    protected $fillable = [
        'customer_id', 'puntos', 'tipo', 'descripcion', 'order_id',
    ];

    protected function casts(): array
    {
        return ['puntos' => 'integer'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Award points for a purchase (configurable rate, default 1 point per $1).
     */
    public static function awardForPurchase(Order $order, int $rate = 1): void
    {
        $customer = $order->customer;
        if (!$customer) return;

        $puntos = (int) floor($order->total * $rate);
        if ($puntos <= 0) return;

        static::create([
            'customer_id' => $customer->id,
            'puntos' => $puntos,
            'tipo' => 'compra',
            'descripcion' => "Compra #{$order->numero}",
            'order_id' => $order->id,
        ]);

        $customer->increment('puntos_acumulados', $puntos);
    }

    /**
     * Redeem points for a discount.
     */
    public static function redeem(Customer $customer, int $puntos, string $descripcion = 'Canje de puntos'): bool
    {
        if ($customer->puntos_acumulados < $puntos) return false;

        static::create([
            'customer_id' => $customer->id,
            'puntos' => -$puntos,
            'tipo' => 'canje',
            'descripcion' => $descripcion,
        ]);

        $customer->decrement('puntos_acumulados', $puntos);
        return true;
    }
}
