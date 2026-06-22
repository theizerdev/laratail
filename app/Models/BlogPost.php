<?php

namespace App\Models;

use App\Traits\Multitenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use Multitenantable;

    protected $fillable = [
        'titulo', 'slug', 'resumen', 'contenido', 'imagen', 'autor',
        'category_id', 'meta_title', 'meta_description', 'tags',
        'publicado', 'fecha_publicacion', 'views', 'empresa_id',
    ];

    protected function casts(): array
    {
        return [
            'publicado' => 'boolean',
            'fecha_publicacion' => 'datetime',
            'views' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (BlogPost $post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->titulo);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getImagenUrlAttribute(): ?string
    {
        if (!$this->imagen) return null;
        if (Str::startsWith($this->imagen, ['http://', 'https://'])) return $this->imagen;
        return asset('storage/' . $this->imagen);
    }

    public function incrementViews(): void
    {
        $this->increment('views');
    }
}
