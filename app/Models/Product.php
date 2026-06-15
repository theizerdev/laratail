<?php

namespace App\Models;

use App\Traits\Multitenantable;
use App\Traits\HasSpanishActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use Multitenantable, HasSpanishActivityLog, LogsActivity, SoftDeletes;

    protected $fillable = [
        'nombre',
        'slug',
        'sku',
        'descripcion_corta',
        'descripcion',
        'precio',
        'precio_oferta',
        'precio_compra',
        'stock',
        'stock_minimo',
        'rastrear_inventario',
        'peso',
        'largo',
        'ancho',
        'alto',
        'category_id',
        'brand_id',
        'tiene_variantes',
        'destacado',
        'nuevo',
        'fecha_publicacion',
        'meta_title',
        'meta_description',
        'imagen_principal',
        'status',
        'empresa_id',
        'sucursal_id',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'precio_oferta' => 'decimal:2',
            'precio_compra' => 'decimal:2',
            'stock' => 'integer',
            'stock_minimo' => 'integer',
            'rastrear_inventario' => 'boolean',
            'peso' => 'decimal:3',
            'largo' => 'decimal:2',
            'ancho' => 'decimal:2',
            'alto' => 'decimal:2',
            'tiene_variantes' => 'boolean',
            'destacado' => 'boolean',
            'nuevo' => 'boolean',
            'fecha_publicacion' => 'date',
            'status' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'sku', 'precio', 'stock', 'status', 'category_id', 'brand_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->nombre);
            }
            if (empty($product->sku)) {
                $product->sku = 'PRD-' . strtoupper(Str::random(8));
            }
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('nombre') && !$product->isDirty('slug')) {
                $product->slug = Str::slug($product->nombre);
            }
        });
    }

    /**
     * Precio final (con oferta si aplica)
     */
    public function getPrecioFinalAttribute(): float
    {
        return $this->precio_oferta ?? $this->precio;
    }

    /**
     * Tiene descuento activo
     */
    public function getTieneDescuentoAttribute(): bool
    {
        return $this->precio_oferta !== null && $this->precio_oferta < $this->precio;
    }

    /**
     * Porcentaje de descuento
     */
    public function getPorcentajeDescuentoAttribute(): int
    {
        if (!$this->tiene_descuento || $this->precio <= 0) {
            return 0;
        }
        return (int) round((($this->precio - $this->precio_oferta) / $this->precio) * 100);
    }

    /**
     * Stock bajo
     */
    public function getStockBajoAttribute(): bool
    {
        return $this->stock <= $this->stock_minimo;
    }

    /**
     * Stock total incluyendo variantes
     */
    public function getStockTotalAttribute(): int
    {
        if (!$this->tiene_variantes) {
            return $this->stock;
        }
        return $this->variants()->sum('stock');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('orden');
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values')
            ->withPivot('attribute_id', 'valor_personalizado')
            ->withTimestamps();
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
