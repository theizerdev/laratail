<?php

namespace App\Models;

use App\Traits\Multitenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GiftCard extends Model
{
    use Multitenantable;

    protected $fillable = [
        'codigo', 'valor_inicial', 'saldo', 'destinatario_email',
        'destinatario_nombre', 'mensaje', 'customer_id',
        'redeemed_by_customer_id', 'fecha_envio', 'fecha_canje',
        'fecha_expiracion', 'estado', 'order_id', 'empresa_id',
    ];

    protected function casts(): array
    {
        return [
            'valor_inicial' => 'decimal:2',
            'saldo' => 'decimal:2',
            'fecha_envio' => 'datetime',
            'fecha_canje' => 'datetime',
            'fecha_expiracion' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (GiftCard $gc) {
            if (empty($gc->codigo)) {
                $gc->codigo = 'GC-' . strtoupper(Str::random(12));
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function redeemedBy(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'redeemed_by_customer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getEstaActivoAttribute(): bool
    {
        return $this->estado === 'activo' && $this->saldo > 0
            && (!$this->fecha_expiracion || $this->fecha_expiracion->isFuture());
    }

    /**
     * Redeem gift card for an order.
     */
    public function canjear(Customer $customer, float $monto): float
    {
        if (!$this->esta_activo) return 0;

        $montoUsado = min($monto, $this->saldo);
        $this->saldo -= $montoUsado;
        $this->redeemed_by_customer_id = $customer->id;
        $this->fecha_canje = now();
        if ($this->saldo <= 0) {
            $this->estado = 'canjeado';
        }
        $this->save();

        return $montoUsado;
    }
}
