<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasSpanishActivityLog;
class Empresa extends Model
{
    use HasFactory, LogsActivity, HasSpanishActivityLog;

    protected $fillable = [
        'razon_social',
        'documento',
        'logo',
        'direccion',
        'latitud',
        'longitud',
        'representante_legal',
        'telefono',
        'email',
        'status',
        'pais_id',
        'api_key',
        'whatsapp_api_key',
        'whatsapp_api_url',
        'whatsapp_rate_limit',
        'whatsapp_active',
        'whatsapp_phone',
        'whatsapp_status',
        'whatsapp_last_connected'
    ];

    protected $casts = [
        'status' => 'boolean',
        'whatsapp_active' => 'boolean',
        'whatsapp_rate_limit' => 'integer',
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
        'whatsapp_last_connected' => 'datetime'
    ];

    protected $appends = ['logo_url'];

    public function pais()
    {
        return $this->belongsTo(Pais::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            return Storage::disk('public')->url($this->logo);
        }
        return null;
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
