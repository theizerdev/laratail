<?php

namespace App\Models;

use App\Traits\Multitenantable;
use App\Traits\HasSpanishActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use Multitenantable, HasSpanishActivityLog, LogsActivity, SoftDeletes;

    protected $fillable = [
        'order_id',
        'amount',
        'metodo_pago',
        'referencia',
        'fecha_pago',
        'estado',
        'notas',
        'comprobante_path',
        'user_id',
        'empresa_id',
        'sucursal_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fecha_pago' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['order_id', 'amount', 'metodo_pago', 'estado', 'referencia'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByEstado($query, string $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopeByMetodo($query, string $metodo)
    {
        return $query->where('metodo_pago', $metodo);
    }

    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    // Accessors
    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            'pendiente' => 'amber',
            'completado' => 'emerald',
            'fallido' => 'red',
            'reembolsado' => 'blue',
            default => 'gray',
        };
    }

    public function getMetodoPagoLabelAttribute(): string
    {
        return match ($this->metodo_pago) {
            'pago_movil' => 'Pago Móvil',
            default => ucfirst(str_replace('_', ' ', $this->metodo_pago)),
        };
    }

    /**
     * Auto-update order payment status after save.
     */
    protected static function booted(): void
    {
        static::saved(function (Payment $payment) {
            $payment->syncOrderPaymentStatus();
        });

        static::deleted(function (Payment $payment) {
            if (!$payment->isForceDeleting()) {
                $payment->syncOrderPaymentStatus();
            }
        });
    }

    public function syncOrderPaymentStatus(): void
    {
        $order = $this->order;
        if (!$order) return;

        $totalPagado = $order->payments()
            ->where('estado', 'completado')
            ->sum('amount');

        if ($totalPagado >= $order->total) {
            $order->update(['estado_pago' => 'pagado', 'fecha_pago' => now()]);
        } elseif ($totalPagado > 0) {
            $order->update(['estado_pago' => 'parcial']);
        } else {
            $order->update(['estado_pago' => 'pendiente']);
        }
    }
}
