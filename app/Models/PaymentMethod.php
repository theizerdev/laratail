<?php

namespace App\Models;

use App\Traits\Multitenantable;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use Multitenantable;

    protected $fillable = [
        'nombre',
        'tipo',
        'activo',
        'empresa_id',
        'sucursal_id',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            'efectivo' => 'Efectivo',
            'transferencia' => 'Transferencia',
            'tarjeta' => 'Tarjeta',
            'digital' => 'Digital',
            default => ucfirst($this->tipo),
        };
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
