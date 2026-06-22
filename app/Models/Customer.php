<?php

namespace App\Models;

use App\Traits\Multitenantable;
use App\Traits\HasSpanishActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends Model
{
    use Multitenantable, HasSpanishActivityLog, LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id', 'nombre', 'apellido', 'email', 'telefono',
        'tipo_documento', 'documento', 'direccion', 'ciudad',
        'estado_region', 'codigo_postal', 'pais_id',
        'empresa_nombre', 'notas', 'whatsapp', 'fuente',
        'fecha_nacimiento', 'activo', 'empresa_id', 'sucursal_id',
        'puntos_acumulados', 'codigo_referido', 'referred_by',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'apellido', 'email', 'telefono', 'activo', 'user_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }

    /**
     * Nombre completo
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre . ' ' . ($this->apellido ?? ''));
    }

    /**
     * Iniciales
     */
    public function getInicialesAttribute(): string
    {
        return strtoupper(
            substr($this->nombre, 0, 1) . (substr($this->apellido ?? 'X', 0, 1))
        );
    }

    /**
     * Tiene cuenta de usuario asociada
     */
    public function getTieneCuentaAttribute(): bool
    {
        return $this->user_id !== null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function defaultAddress(): HasOne
    {
        return $this->hasOne(CustomerAddress::class)->where('predeterminada', true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function loyaltyPoints(): HasMany
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_customer_id');
    }

    public function giftCards(): HasMany
    {
        return $this->hasMany(GiftCard::class);
    }
}
