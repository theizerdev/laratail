<?php

namespace App\Models;

use App\Traits\Multitenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Attribute extends Model
{
    use Multitenantable;

    protected $fillable = [
        'nombre',
        'slug',
        'tipo',
        'usado_para_variantes',
        'status',
        'empresa_id',
        'sucursal_id',
    ];

    protected function casts(): array
    {
        return [
            'usado_para_variantes' => 'boolean',
            'status' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Attribute $attr) {
            if (empty($attr->slug)) {
                $attr->slug = Str::slug($attr->nombre);
            }
        });
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
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
