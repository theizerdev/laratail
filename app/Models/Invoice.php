<?php

namespace App\Models;

use App\Traits\Multitenantable;
use App\Traits\HasSpanishActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use Multitenantable, HasSpanishActivityLog, LogsActivity;

    protected $fillable = [
        'numero',
        'order_id',
        'tipo',
        'serie',
        'numero_control',
        'fecha_emision',
        'subtotal',
        'impuesto',
        'total',
        'estado',
        'notas',
        'empresa_id',
        'sucursal_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'datetime',
            'subtotal' => 'decimal:2',
            'impuesto' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['numero', 'tipo', 'estado', 'total', 'order_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->numero)) {
                $prefix = $invoice->tipo === 'proforma' ? 'PRO' : 'FAC';
                $invoice->numero = $prefix . '-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
            }
        });
    }

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    // Scopes
    public function scopeByEstado($query, string $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopeByTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // Methods
    public function anular(): void
    {
        $this->update(['estado' => 'anulada']);
    }

    public function recalcular(): void
    {
        $items = $this->items;
        $this->subtotal = $items->sum('subtotal') - $items->sum('descuento');
        $this->impuesto = $this->subtotal * 0.16;
        $this->total = $this->subtotal + $this->impuesto;
        $this->save();
    }

    // Accessors
    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            'emitida' => 'emerald',
            'anulada' => 'red',
            default => 'gray',
        };
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            'factura' => 'Factura',
            'proforma' => 'Proforma',
            default => ucfirst($this->tipo),
        };
    }
}
