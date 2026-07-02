<?php

namespace App\Models;

use App\Traits\Multitenantable;
use App\Traits\HasSpanishActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use Multitenantable, HasSpanishActivityLog, LogsActivity, SoftDeletes;

    protected $fillable = [
        'numero',
        'customer_id',
        'user_id',
        'asignado_a',
        'tipo',
        'estado',
        'estado_pago',
        'subtotal',
        'descuento',
        'impuesto',
        'envio',
        'total',
        'coupon_id',
        'codigo_cupon',
        'direccion_envio',
        'ciudad_envio',
        'estado_envio',
        'codigo_postal_envio',
        'pais_envio_id',
        'metodo_envio',
        'numero_seguimiento',
        'latitud',
        'longitud',
        'metodo_pago',
        'referencia_pago',
        'fecha_pago',
        'fecha_confirmacion',
        'fecha_envio',
        'fecha_entrega',
        'fecha_cancelacion',
        'notas_internas',
        'notas_cliente',
        'empresa_id',
        'sucursal_id',
        'empleado_token',
        'empleado_token_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'descuento' => 'decimal:2',
            'impuesto' => 'decimal:2',
            'envio' => 'decimal:2',
            'total' => 'decimal:2',
            'fecha_pago' => 'datetime',
            'fecha_confirmacion' => 'datetime',
            'fecha_envio' => 'datetime',
            'fecha_entrega' => 'datetime',
            'fecha_cancelacion' => 'datetime',
            'empleado_token_expires_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['numero', 'estado', 'estado_pago', 'total', 'customer_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Order $order) {
            if (empty($order->numero)) {
                $prefix = $order->tipo === 'cotizacion' ? 'COT' : 'ORD';
                $order->numero = $prefix . '-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
            }
        });
    }

    // ── Estados auxiliares ─────────────────────────────

    public function getEsPedidoAttribute(): bool
    {
        return $this->tipo === 'venta';
    }

    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'asignado_a');
    }

    public function getEsCotizacionAttribute(): bool
    {
        return $this->tipo === 'cotizacion';
    }

    public function getEstaPendienteAttribute(): bool
    {
        return in_array($this->estado, ['borrador', 'pendiente']);
    }

    public function getEstaCanceladoAttribute(): bool
    {
        return $this->estado === 'cancelado';
    }

    public function getEstaEntregadoAttribute(): bool
    {
        return $this->estado === 'entregado';
    }

    public function getEstaPagadoAttribute(): bool
    {
        return $this->estado_pago === 'pagado';
    }

    /**
     * Badge color para estado
     */
    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            'borrador' => 'gray',
            'pendiente' => 'yellow',
            'confirmado' => 'blue',
            'asignado' => 'indigo',
            'procesando' => 'indigo',
            'enviado' => 'purple',
            'entregado' => 'emerald',
            'cancelado' => 'red',
            'devuelto' => 'orange',
            default => 'gray',
        };
    }

    /**
     * Badge color para pago
     */
    public function getPagoColorAttribute(): string
    {
        return match ($this->estado_pago) {
            'pendiente' => 'yellow',
            'parcial' => 'orange',
            'pagado' => 'emerald',
            'reembolsado' => 'red',
            default => 'gray',
        };
    }

    /**
     * Label legible del estado
     */
    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'borrador' => 'Borrador',
            'pendiente' => 'Pendiente',
            'confirmado' => 'Confirmado',
            'asignado' => 'Asignado',
            'procesando' => 'Procesando',
            'enviado' => 'Enviado',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
            'devuelto' => 'Devuelto',
            default => ucfirst($this->estado),
        };
    }

    // ── Relaciones ─────────────────────────────────────

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
        return $this->hasMany(OrderItem::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function paisEnvio(): BelongsTo
    {
        return $this->belongsTo(Pais::class, 'pais_envio_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    // ── Helpers ────────────────────────────────────────

    /**
     * Recalcular totales desde los items
     */
    public function recalcular(): void
    {
        $items = $this->items;
        $this->subtotal = $items->sum('subtotal');
        $this->impuesto = $items->sum('impuesto');
        $this->total = $items->sum('subtotal') + $items->sum('impuesto') + $this->envio - $this->descuento;
        $this->save();
    }

    /**
     * Cambiar estado con validación
     */
    public function cambiarEstado(string $nuevoEstado): void
    {
        $this->estado = $nuevoEstado;

        if ($nuevoEstado === 'confirmado') {
            $this->fecha_confirmacion = now();
        } elseif ($nuevoEstado === 'enviado') {
            $this->fecha_envio = now();
        } elseif ($nuevoEstado === 'entregado') {
            $this->fecha_entrega = now();
        } elseif ($nuevoEstado === 'cancelado') {
            $this->fecha_cancelacion = now();
        }

        $this->save();
    }
}