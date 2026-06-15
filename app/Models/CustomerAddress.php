<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'tipo',
        'alias',
        'nombre_destinatario',
        'telefono_destinatario',
        'direccion',
        'ciudad',
        'estado_region',
        'codigo_postal',
        'pais_id',
        'predeterminada',
    ];

    protected function casts(): array
    {
        return [
            'predeterminada' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class);
    }

    public function getDireccionCompletaAttribute(): string
    {
        $parts = array_filter([
            $this->direccion,
            $this->ciudad,
            $this->estado_region,
            $this->codigo_postal,
        ]);

        return implode(', ', $parts);
    }
}
