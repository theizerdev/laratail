<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasSpanishActivityLog;
class Sucursal extends Model
{
    use HasFactory, LogsActivity, HasSpanishActivityLog;

    protected $table = 'sucursales';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'telefono',
        'direccion',
        'latitud',
        'longitud',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }
}
