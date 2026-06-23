<?php

namespace App\Livewire\Admin\Profile;

use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Mi Perfil')]
class Index extends Component
{
    // Profile Info
    public string $name = '';
    public string $email = '';
    public string $telefono = '';

    // Password Update
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    // 2FA Activation State
    public bool $isConfiguring2FA = false;
    public string $tempSecret = '';
    public array $tempRecoveryCodes = [];
    public string $tempQrCodeUrl = '';
    public string $twoFactorCode = '';

    // 2FA Deactivation State
    public bool $isDisabling2FA = false;
    public string $confirmPasswordFor2FA = '';

    // Active tab (for UI navigation)
    public string $activeTab = 'info';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->telefono = $user->telefono ?? '';
    }

    /**
     * Update user basic profile information.
     */
    public function updateProfile(): void
    {
        $user = Auth::user();

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'telefono' => 'nullable|string|max:20',
        ]);

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'telefono' => $this->telefono ?: null,
        ]);

        // Log the activity
        activity()
            ->performedOn($user)
            ->withProperties([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('Perfil de usuario actualizado');

        session()->flash('success_profile', 'Tu perfil ha sido actualizado correctamente.');
    }

    /**
     * Update user password.
     */
    public function updatePassword(): void
    {
        $user = Auth::user();

        $this->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($this->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'La contraseña actual no es correcta.',
            ]);
        }

        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        // Log the activity
        activity()
            ->performedOn($user)
            ->log('Contraseña de usuario actualizada');

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        session()->flash('success_password', 'Tu contraseña ha sido cambiada correctamente.');
    }

    /**
     * Initialize 2FA configuration flow.
     */
    public function initTwoFactor(): void
    {
        $user = Auth::user();

        $this->tempSecret = TwoFactorService::generateSecret();
        $this->tempRecoveryCodes = TwoFactorService::generateRecoveryCodes();
        
        // Generate QR code URL
        $this->tempQrCodeUrl = TwoFactorService::getQRCodeUrl(
            $user->email,
            $this->tempSecret,
            config('app.name', 'Laratail')
        );

        $this->isConfiguring2FA = true;
        $this->twoFactorCode = '';
    }

    /**
     * Cancel 2FA configuration flow.
     */
    public function cancelTwoFactorSetup(): void
    {
        $this->reset(['isConfiguring2FA', 'tempSecret', 'tempRecoveryCodes', 'tempQrCodeUrl', 'twoFactorCode']);
    }

    /**
     * Confirm 2FA code and enable it on the account.
     */
    public function confirmTwoFactor(): void
    {
        $this->validate([
            'twoFactorCode' => 'required|string|size:6',
        ]);

        $isValid = TwoFactorService::verifyCode($this->tempSecret, $this->twoFactorCode);

        if (!$isValid) {
            $this->addError('twoFactorCode', 'El código ingresado es incorrecto o ha expirado.');
            return;
        }

        $user = Auth::user();
        
        $user->update([
            'two_factor_secret' => Crypt::encryptString($this->tempSecret),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($this->tempRecoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ]);

        activity()
            ->performedOn($user)
            ->log('Autenticación de dos pasos (2FA) habilitada');

        // We keep temporary recovery codes in the state for display in this session
        $this->isConfiguring2FA = false;
        
        session()->flash('success_2fa', 'La autenticación de dos pasos (2FA) ha sido habilitada exitosamente.');
    }

    /**
     * Start the 2FA deactivation flow.
     */
    public function startDisableTwoFactor(): void
    {
        $this->isDisabling2FA = true;
        $this->confirmPasswordFor2FA = '';
    }

    /**
     * Confirm password and disable 2FA.
     */
    public function disableTwoFactor(): void
    {
        $user = Auth::user();

        $this->validate([
            'confirmPasswordFor2FA' => 'required|string',
        ]);

        if (!Hash::check($this->confirmPasswordFor2FA, $user->password)) {
            $this->addError('confirmPasswordFor2FA', 'La contraseña ingresada no es correcta.');
            return;
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        activity()
            ->performedOn($user)
            ->log('Autenticación de dos pasos (2FA) deshabilitada');

        $this->reset(['isDisabling2FA', 'confirmPasswordFor2FA']);

        session()->flash('success_2fa', 'La autenticación de dos pasos (2FA) ha sido deshabilitada.');
    }

    /**
     * Get the decrypted recovery codes for display.
     */
    public function getRecoveryCodesProperty(): array
    {
        $user = Auth::user();
        if (!$user->two_factor_recovery_codes) {
            return [];
        }

        try {
            return json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function render()
    {
        return view('livewire.admin.profile.index', [
            'recoveryCodes' => $this->recoveryCodes,
        ]);
    }
}
