<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = [
        'nombre',
        'codigo_postal_inicio',
        'codigo_postal_fin',
        'pais_codigo',
        'costo_base',
        'costo_por_kg',
        'dias_entrega_min',
        'dias_entrega_max',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'costo_base' => 'decimal:2',
            'costo_por_kg' => 'decimal:2',
            'dias_entrega_min' => 'integer',
            'dias_entrega_max' => 'integer',
            'activo' => 'boolean',
        ];
    }

    /**
     * Find matching zone for a postal code and calculate shipping cost.
     */
    public static function estimate(string $codigoPostal, float $pesoKg = 1): ?array
    {
        $zone = static::where('activo', true)
            ->where(function ($q) use ($codigoPostal) {
                $q->where(function ($q2) use ($codigoPostal) {
                    $q2->whereNotNull('codigo_postal_inicio')
                        ->whereNotNull('codigo_postal_fin')
                        ->where('codigo_postal_inicio', '<=', $codigoPostal)
                        ->where('codigo_postal_fin', '>=', $codigoPostal);
                })->orWhere(function ($q2) {
                    $q2->whereNull('codigo_postal_inicio')
                        ->whereNull('codigo_postal_fin');
                });
            })
            ->first();

        if (!$zone) {
            // Default zone
            $zone = static::where('activo', true)
                ->whereNull('codigo_postal_inicio')
                ->first();
        }

        if (!$zone) {
            return null;
        }

        $costo = $zone->costo_base + ($zone->costo_por_kg * max(0, $pesoKg - 1));

        return [
            'zona' => $zone->nombre,
            'costo' => round($costo, 2),
            'dias_min' => $zone->dias_entrega_min,
            'dias_max' => $zone->dias_entrega_max,
            'gratis_desde' => 100, // Free shipping over $100
        ];
    }
}
