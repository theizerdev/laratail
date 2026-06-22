<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasSpanishActivityLog;
use App\Traits\Multitenantable;

#[Fillable(['nombre', 'apellido', 'dni', 'grado', 'seccion', 'codigo_acceso', 'fecha_nacimiento', 'edad', 'genero', 'email', 'telefono', 'foto_path', 'huella_template', 'empresa_id', 'sucursal_id'])]
class Estudiante extends Model
{
    use HasFactory, LogsActivity, HasSpanishActivityLog, Multitenantable;

    protected $fillable = ['nombre', 'apellido', 'dni', 'grado', 'seccion', 'codigo_acceso', 'fecha_nacimiento', 'edad', 'genero', 'email', 'telefono', 'foto_path', 'huella_template', 'empresa_id', 'sucursal_id'];

    protected $appends = ['foto_url'];

    public function getFotoUrlAttribute(): ?string
    {
        if ($this->foto_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->foto_path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($this->foto_path);
        }
        return null;
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function representantes()
    {
        return $this->belongsToMany(Representante::class, 'estudiante_representante')->withPivot('relacion')->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}
