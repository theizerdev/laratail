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

class CreditNote extends Model
{
    use Multitenantable, HasSpanishActivityLog, LogsActivity;

    protected $fillable = [
        'numero',
        'invoice_id',
        'motivo',
        'monto',
        'fecha_emision',
        'estado',
        'empresa_id',
        'sucursal_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'datetime',
            'monto' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['numero', 'estado', 'monto', 'invoice_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }

    protected static function booted(): void
    {
        static::creating(function (CreditNote $note) {
            if (empty($note->numero)) {
                $note->numero = 'NC-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
            }
        });
    }

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CreditNoteItem::class);
    }

    // Methods
    public function anular(): void
    {
        $this->update(['estado' => 'anulada']);
    }

    public function recalcular(): void
    {
        $this->monto = $this->items->sum('subtotal');
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
}
