<?php

namespace App\Models;

use App\Traits\Multitenantable;
use App\Traits\HasSpanishActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CashRegister extends Model
{
    use Multitenantable, HasSpanishActivityLog, LogsActivity;

    protected $fillable = [
        'user_id',
        'fecha_apertura',
        'fecha_cierre',
        'monto_inicial',
        'monto_final',
        'total_ingresos',
        'total_egresos',
        'estado',
        'notas',
        'empresa_id',
        'sucursal_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_apertura' => 'datetime',
            'fecha_cierre' => 'datetime',
            'monto_inicial' => 'decimal:2',
            'monto_final' => 'decimal:2',
            'total_ingresos' => 'decimal:2',
            'total_egresos' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['estado', 'monto_inicial', 'monto_final', 'user_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    // Methods
    public function abrir(float $montoInicial = 0): void
    {
        $this->update([
            'estado' => 'abierta',
            'fecha_apertura' => now(),
            'monto_inicial' => $montoInicial,
        ]);
    }

    public function cerrar(float $montoFinal): void
    {
        $this->update([
            'estado' => 'cerrada',
            'fecha_cierre' => now(),
            'monto_final' => $montoFinal,
            'total_ingresos' => $this->movements()->where('tipo', 'ingreso')->sum('monto'),
            'total_egresos' => $this->movements()->where('tipo', 'egreso')->sum('monto'),
        ]);
    }

    public function totalActual(): float
    {
        $ingresos = $this->movements()->where('tipo', 'ingreso')->sum('monto');
        $egresos = $this->movements()->where('tipo', 'egreso')->sum('monto');
        return (float) $this->monto_inicial + $ingresos - $egresos;
    }

    // Accessors
    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            'abierta' => 'emerald',
            'cerrada' => 'gray',
            default => 'gray',
        };
    }

    public function getEsperadoAttribute(): float
    {
        return $this->totalActual();
    }

    public function getDiferenciaAttribute(): ?float
    {
        if ($this->estado === 'cerrada' && $this->monto_final !== null) {
            return (float) $this->monto_final - $this->totalActual();
        }
        return null;
    }
}
