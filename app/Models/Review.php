<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'product_id',
        'customer_id',
        'rating',
        'titulo',
        'comentario',
        'verificado',
        'aprobado',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'verificado' => 'boolean',
            'aprobado' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Nombre para mostrar (anonimizado si no es público)
     */
    public function getAutorNombreAttribute(): string
    {
        return $this->customer?->nombre ?? 'Cliente verificado';
    }

    /**
     * Iniciales del autor
     */
    public function getAutorInicialesAttribute(): string
    {
        return $this->customer?->iniciales ?? 'CV';
    }
}
