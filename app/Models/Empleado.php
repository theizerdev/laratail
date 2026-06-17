<?php

namespace App\Models;

use App\Traits\Multitenantable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Empleado extends Model
{
    use HasFactory, SoftDeletes, Multitenantable;

    protected $fillable = [
        'nombre',
        'apellidos',
        'documento',
        'email',
        'telefono',
        'cargo',
        'fecha_ingreso',
        'salario',
        'estado',
        'empresa_id',
        'sucursal_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
            'salario' => 'decimal:2',
        ];
    }

    /**
     * Get the full name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellidos}");
    }

    /**
     * Get the initials
     */
    public function initials(): string
    {
        return Str::of($this->full_name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }
}
