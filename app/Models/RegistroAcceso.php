<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Multitenantable;

class RegistroAcceso extends Model
{
    use HasFactory, Multitenantable;

    protected $fillable = [
        'estudiante_id',
        'tipo',
        'fecha_hora',
        'metodo',
        'estado',
        'empresa_id',
        'sucursal_id'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime'
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }
}
