<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'nombre',
        'precio',
        'precio_oferta',
        'precio_bs',
        'stock',
        'stock_minimo',
        'peso',
        'imagen',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'precio_oferta' => 'decimal:2',
            'precio_bs' => 'decimal:2',
            'stock' => 'integer',
            'stock_minimo' => 'integer',
            'peso' => 'decimal:3',
            'status' => 'boolean',
        ];
    }

    /**
     * Precio final de la variante (con oferta si aplica)
     */
    public function getPrecioFinalAttribute(): float
    {
        return $this->precio_oferta ?? $this->precio ?? $this->product->precio;
    }

    /**
     * Tiene descuento activo
     */
    public function getTieneDescuentoAttribute(): bool
    {
        return $this->precio_oferta !== null && $this->precio_oferta < $this->precio;
    }

    /**
     * Stock bajo
     */
    public function getStockBajoAttribute(): bool
    {
        return $this->stock <= $this->stock_minimo;
    }

    /**
     * Nombre compuesto a partir de los valores de atributos
     */
    public function getNombreCompuestoAttribute(): string
    {
        if ($this->nombre) {
            return $this->nombre;
        }

        return $this->attributeValues->pluck('valor')->implode(' / ');
    }

    /**
     * URL resuelta de la imagen de la variante
     */
    public function getImagenUrlAttribute(): ?string
    {
        if (!$this->imagen) {
            return null;
        }

        if (Str::startsWith($this->imagen, ['http://', 'https://'])) {
            return $this->imagen;
        }

        if (Str::startsWith($this->imagen, ['app/', 'build/'])) {
            return asset($this->imagen);
        }

        return asset('storage/' . $this->imagen);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'product_variant_attribute_values'
        )->withPivot('attribute_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('orden');
    }
}