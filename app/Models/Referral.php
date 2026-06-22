<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Referral extends Model
{
    protected $fillable = [
        'referrer_customer_id', 'codigo_referido', 'referred_customer_id',
        'order_id', 'puntos_referrer', 'puntos_referred', 'estado',
    ];

    protected function casts(): array
    {
        return [
            'puntos_referrer' => 'integer',
            'puntos_referred' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Referral $ref) {
            if (empty($ref->codigo_referido)) {
                $ref->codigo_referido = 'REF-' . strtoupper(Str::random(8));
            }
        });
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referrer_customer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referred_customer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Complete referral: award points to both parties.
     */
    public function completar(int $puntosReferrer = 100, int $puntosReferred = 50): void
    {
        if ($this->estado === 'completado') return;

        $this->puntos_referrer = $puntosReferrer;
        $this->puntos_referred = $puntosReferred;
        $this->estado = 'completado';
        $this->save();

        // Award to referrer
        LoyaltyPoint::create([
            'customer_id' => $this->referrer_customer_id,
            'puntos' => $puntosReferrer,
            'tipo' => 'referido',
            'descripcion' => 'Referido completado: ' . ($this->referred?->nombre_completo ?? 'Amigo'),
        ]);
        $this->referrer->increment('puntos_acumulados', $puntosReferrer);

        // Award to referred
        if ($this->referred_customer_id) {
            LoyaltyPoint::create([
                'customer_id' => $this->referred_customer_id,
                'puntos' => $puntosReferred,
                'tipo' => 'referido',
                'descripcion' => 'Bono de bienvenida por referido',
            ]);
            $this->referred->increment('puntos_acumulados', $puntosReferred);
        }
    }
}
