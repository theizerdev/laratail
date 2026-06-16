<?php

namespace App\Models;

use App\Traits\Multitenantable;
use App\Traits\HasSpanishActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Shipment extends Model
{
    use Multitenantable, HasSpanishActivityLog, LogsActivity;

    protected $fillable = [
        'numero',
        'order_id',
        'carrier_name',
        'tracking_number',
        'estado',
        'fecha_envio',
        'fecha_entrega_esperada',
        'fecha_entrega',
        'peso',
        'costo_envio',
        'direccion_destino',
        'ciudad_destino',
        'estado_destino',
        'codigo_postal_destino',
        'notas',
        'empresa_id',
        'sucursal_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_envio' => 'date',
            'fecha_entrega_esperada' => 'date',
            'fecha_entrega' => 'date',
            'peso' => 'decimal:3',
            'costo_envio' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['numero', 'estado', 'carrier_name', 'tracking_number', 'order_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Shipment $shipment) {
            if (empty($shipment->numero)) {
                $shipment->numero = 'ENV-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            }
        });
    }

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // State management
    public function cambiarEstado(string $nuevoEstado): void
    {
        $this->estado = $nuevoEstado;
        if ($nuevoEstado === 'enviado' && !$this->fecha_envio) {
            $this->fecha_envio = now();
        } elseif ($nuevoEstado === 'entregado') {
            $this->fecha_entrega = now();
        }
        $this->save();
    }

    // Accessors
    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            'preparando' => 'gray',
            'enviado' => 'blue',
            'en_transito' => 'amber',
            'entregado' => 'emerald',
            'devuelto' => 'red',
            default => 'gray',
        };
    }

    public function getEstadoIconAttribute(): string
    {
        return match ($this->estado) {
            'preparando' => 'heroicons:archive-box',
            'enviado' => 'heroicons:paper-airplane-solid',
            'en_transito' => 'heroicons:truck-solid',
            'entregado' => 'heroicons:check-badge-solid',
            'devuelto' => 'heroicons:arrow-uturn-left-solid',
            default => 'heroicons:archive-box',
        };
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'en_transito' => 'En Tránsito',
            default => ucfirst($this->estado),
        };
    }

    public function getRetrasadoAttribute(): bool
    {
        if ($this->estado === 'entregado' || $this->estado === 'devuelto') return false;
        return $this->fecha_entrega_esperada && $this->fecha_entrega_esperada->isPast();
    }
}
