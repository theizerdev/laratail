<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.auth')]
#[Title('Iniciar Sesión')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    // 2FA Verification State
    public bool $show2FA = false;
    public string $twoFactorCode = '';

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = Str::transliterate(Str::lower($this->email) . '|' . request()->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => __('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        // Find the user first to check if they have 2FA enabled
        $user = User::where('email', $this->email)->first();

        if ($user && $user->hasTwoFactorEnabled()) {
            // Verify password
            if (!Hash::check($this->password, $user->password)) {
                RateLimiter::hit($key);

                activity()
                    ->withProperties([
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'evento' => 'login_failed',
                        'email_attemptado' => $this->email,
                    ])
                    ->log('Intento de inicio de sesión fallido');

                throw ValidationException::withMessages([
                    'email' => __('auth.failed'),
                ]);
            }

            // Credentials correct, but 2FA is active. 
            // Store ID and remember token temporarily and transition to 2FA view
            session()->put('login.id', $user->id);
            session()->put('login.remember', $this->remember);
            session()->put('login.throttle_key', $key);

            $this->show2FA = true;
            $this->twoFactorCode = '';
            return;
        }

        // Standard login logic (no 2FA active)
        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key);

            activity()
                ->withProperties([
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                    'evento' => 'login_failed',
                    'email_attemptado' => $this->email,
                ])
                ->log('Intento de inicio de sesión fallido');

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($key);

        session()->regenerate();

        // If user needs email verification, redirect to verification page
        if (Auth::user() && ! Auth::user()->hasVerifiedEmail()) {
            $this->redirect(route('verification.notice'), navigate: true);

            return;
        }

        $this->redirectIntended(default: route('home'), navigate: true);
    }

    /**
     * Verify the entered 2FA code or recovery code.
     */
    public function verify2FA(): void
    {
        $userId = session()->get('login.id');
        if (!$userId) {
            $this->show2FA = false;
            return;
        }

        $this->validate([
            'twoFactorCode' => 'required|string',
        ]);

        $user = User::findOrFail($userId);
        $code = str_replace(' ', '', $this->twoFactorCode);
        $isValid = false;

        // 1. Verify standard TOTP code
        if (strlen($code) === 6 && is_numeric($code)) {
            try {
                $secret = Crypt::decryptString($user->two_factor_secret);
                $isValid = TwoFactorService::verifyCode($secret, $code);
            } catch (\Exception $e) {
                $isValid = false;
            }
        }

        // 2. Verify recovery code if TOTP code wasn't valid
        if (!$isValid && $user->two_factor_recovery_codes) {
            try {
                $recoveryCodes = json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true) ?: [];
                $cleanCode = strtolower(trim($this->twoFactorCode));

                if (in_array($cleanCode, $recoveryCodes)) {
                    $isValid = true;

                    // Consume/remove recovery code
                    $recoveryCodes = array_diff($recoveryCodes, [$cleanCode]);
                    $user->update([
                        'two_factor_recovery_codes' => Crypt::encryptString(json_encode(array_values($recoveryCodes))),
                    ]);
                }
            } catch (\Exception $e) {
                $isValid = false;
            }
        }

        if (!$isValid) {
            throw ValidationException::withMessages([
                'twoFactorCode' => 'El código de seguridad ingresado es incorrecto o ha expirado.',
            ]);
        }

        // Complete the authentication
        Auth::login($user, session()->get('login.remember', false));

        $key = session()->get('login.throttle_key');
        if ($key) {
            RateLimiter::clear($key);
        }

        session()->forget(['login.id', 'login.remember', 'login.throttle_key']);
        session()->regenerate();

        activity()
            ->performedOn($user)
            ->log('Inicio de sesión exitoso con 2FA');

        if (!$user->hasVerifiedEmail()) {
            $this->redirect(route('verification.notice'), navigate: true);
            return;
        }

        $this->redirectIntended(default: route('home'), navigate: true);
    }

    /**
     * Cancel the 2FA login prompt and go back to email/password inputs.
     */
    public function cancel2FA(): void
    {
        session()->forget(['login.id', 'login.remember', 'login.throttle_key']);
        $this->show2FA = false;
        $this->twoFactorCode = '';
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
