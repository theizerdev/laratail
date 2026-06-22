<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasSpanishActivityLog;

class Pais extends Model
{
    use LogsActivity, HasSpanishActivityLog;
    protected $table = 'pais';

    protected $fillable = [
        'nombre',
        'codigo_iso2',
        'codigo_iso3',
        'codigo_telefonico',
        'moneda_principal',
        'idioma_principal',
        'continente',
        'zona_horaria',
        'formato_fecha',
        'formato_moneda',
        'impuesto_predeterminado',
        'separador_miles',
        'separador_decimales',
        'decimales_moneda',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'impuesto_predeterminado' => 'decimal:2',
            'activo' => 'boolean',
        ];
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