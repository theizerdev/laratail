<?php

namespace App\Models;

use App\Traits\HasGroupRoles;
use App\Traits\HasSpanishActivityLog;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property int|null $group_id
 * @property string|null $telefono
 * @property int|null $empresa_id
 * @property int|null $sucursal_id
 * @property string|null $whatsapp_otp
 * @property Carbon|null $phone_verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'group_id', 'telefono', 'empresa_id', 'sucursal_id', 'whatsapp_otp', 'phone_verified_at', 'provider', 'provider_id', 'avatar', 'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasGroupRoles, HasRoles, HasSpanishActivityLog, LogsActivity, Notifiable {
        HasRoles::hasPermissionTo as spatieHasPermissionTo;
        HasRoles::hasAnyPermission as spatieHasAnyPermission;
        HasRoles::hasAllPermissions as spatieHasAllPermissions;
        HasGroupRoles::hasRole insteadof HasRoles;
        HasGroupRoles::hasAnyRole insteadof HasRoles;
        HasGroupRoles::hasAllRoles insteadof HasRoles;
        HasGroupRoles::hasPermissionTo insteadof HasRoles;
        HasGroupRoles::hasAnyPermission insteadof HasRoles;
        HasGroupRoles::hasAllPermissions insteadof HasRoles;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Check if the user has Two-Factor Authentication enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_confirmed_at);
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'status', 'empresa_id', 'sucursal_id', 'group_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the group that the user belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the empresa that this user belongs to.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Get the sucursal that this user belongs to.
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }
}
