<?php

namespace App\Models;

use App\Traits\Multitenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Coupon extends Model
{
    use Multitenantable;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'tipo',
        'valor',
        'compra_minima',
        'descuento_maximo',
        'usos_maximos',
        'usos_actuales',
        'usos_por_cliente',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'acumular',
        'empresa_id',
        'sucursal_id',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'compra_minima' => 'decimal:2',
            'descuento_maximo' => 'decimal:2',
            'usos_maximos' => 'integer',
            'usos_actuales' => 'integer',
            'usos_por_cliente' => 'integer',
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'activo' => 'boolean',
            'acumular' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Coupon $coupon) {
            if (empty($coupon->codigo)) {
                $coupon->codigo = strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Verificar si el cupón es válido actualmente
     */
    public function getEsValidoAttribute(): bool
    {
        if (!$this->activo) return false;
        if ($this->usos_maximos && $this->usos_actuales >= $this->usos_maximos) return false;
        if ($this->fecha_inicio && now()->lt($this->fecha_inicio)) return false;
        if ($this->fecha_fin && now()->gt($this->fecha_fin)) return false;
        return true;
    }

    /**
     * Calcular descuento para un monto dado
     */
    public function calcularDescuento(float $subtotal): float
    {
        if (!$this->es_valido || $subtotal < $this->compra_minima) {
            return 0;
        }

        $descuento = $this->tipo === 'porcentaje'
            ? $subtotal * ($this->valor / 100)
            : $this->valor;

        if ($this->descuento_maximo && $descuento > $this->descuento_maximo) {
            $descuento = $this->descuento_maximo;
        }

        return min($descuento, $subtotal);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
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
