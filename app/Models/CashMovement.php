<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    protected $fillable = [
        'cash_register_id',
        'tipo',
        'monto',
        'descripcion',
        'payment_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
        ];
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            'ingreso' => 'Ingreso',
            'egreso' => 'Egreso',
            default => ucfirst($this->tipo),
        };
    }

    public function getTipoColorAttribute(): string
    {
        return match ($this->tipo) {
            'ingreso' => 'emerald',
            'egreso' => 'red',
            default => 'gray',
        };
    }
}
