<?php

namespace App\Livewire\Auth;

use App\Models\Empresa;
use App\Models\Pais;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.auth')]
#[Title('Recuperar Contraseña')]
class ForgotPassword extends Component
{
    // ── Flujo ──────────────────────────────────────────────────────────────────
    /** Paso actual: 'phone' | 'verify' | 'reset' */
    public string $step = 'phone';

    // ── Paso 1: Teléfono ───────────────────────────────────────────────────────
    public string $countryCode = '';
    public string $countryName = '';
    public string $countryIso  = '';
    public string $phone       = '';
    public array  $countries   = [];

    // ── Paso 2: OTP ───────────────────────────────────────────────────────────
    public string $otp         = '';
    public int    $otpAttempts = 0;

    // ── Paso 3: Nueva contraseña ───────────────────────────────────────────────
    public string $password              = '';
    public string $password_confirmation = '';

    // ── Estado general ─────────────────────────────────────────────────────────
    public ?string $errorMessage   = null;
    public ?string $successMessage = null;
    public bool    $otpSent        = false;

    // ── OTP TTL ────────────────────────────────────────────────────────────────
    private const OTP_TTL      = 600; // 10 minutos
    private const MAX_ATTEMPTS = 3;

    // ──────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        // Cargar países disponibles (activos)
        $this->countries = Pais::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo_iso2', 'codigo_telefonico'])
            ->toArray();

        // Pre-seleccionar el país de la empresa principal
        $empresa = Empresa::with('pais')->find(1);

        if ($empresa && $empresa->pais) {
            $this->countryCode = $empresa->pais->codigo_telefonico ?? '';
            $this->countryName = $empresa->pais->nombre ?? '';
            $this->countryIso  = $empresa->pais->codigo_iso2 ?? '';
        } elseif (!empty($this->countries)) {
            $this->countryCode = $this->countries[0]['codigo_telefonico'] ?? '';
            $this->countryName = $this->countries[0]['nombre'] ?? '';
            $this->countryIso  = $this->countries[0]['codigo_iso2'] ?? '';
        }
    }

    /**
     * Actualiza los datos del país cuando el usuario cambia el selector.
     */
    public function updatedCountryIso(string $iso): void
    {
        $found = collect($this->countries)->firstWhere('codigo_iso2', $iso);
        if ($found) {
            $this->countryCode = $found['codigo_telefonico'];
            $this->countryName = $found['nombre'];
        }
    }

    // ── PASO 1: Enviar OTP ────────────────────────────────────────────────────

    public function sendOtp(): void
    {
        $this->errorMessage   = null;
        $this->successMessage = null;

        $this->validate([
            'phone' => ['required', 'string', 'min:6', 'max:15', 'regex:/^[0-9]+$/'],
        ], [
            'phone.required' => 'El número de teléfono es obligatorio.',
            'phone.min'      => 'El número debe tener al menos 6 dígitos.',
            'phone.max'      => 'El número no puede superar 15 dígitos.',
            'phone.regex'    => 'El número solo puede contener dígitos.',
        ]);

        // Rate limit: máximo 3 envíos por minuto por IP
        $rateLimitKey = 'otp-send:' . request()->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $this->errorMessage = "Demasiados intentos. Espera {$seconds} segundos antes de reintentar.";
            return;
        }
        RateLimiter::hit($rateLimitKey, 60);

        // Construir número completo sin espacios ni símbolos
        // codigo_telefonico ya viene con '+' desde la BD (ej: +58)
        $cleanCode  = preg_replace('/\D/', '', $this->countryCode); // "58"
        $cleanPhone = preg_replace('/\D/', '', $this->phone);       // "4242115948"
        $fullPhone  = $cleanCode . $cleanPhone;                     // "584242115948"

        // ── Buscar usuario por teléfono ─────────────────────────────────────
        // Normalizar el teléfono guardado en BD para comparar solo dígitos
        $user = User::get()->first(function ($u) use ($cleanPhone, $fullPhone) {
            if (!$u->telefono) return false;
            $stored = preg_replace('/\D/', '', $u->telefono);
            return $stored === $cleanPhone || $stored === $fullPhone;
        });

        if (!$user) {
            // Por seguridad, no revelar si el número existe o no
            $this->successMessage = 'Si el número está registrado, recibirás un código OTP por WhatsApp en breve.';
            $this->step    = 'verify';
            $this->otpSent = true;
            // Limpiar cualquier OTP previo en session
            session(['otp_user_id' => null, 'otp_expires_at' => null, 'otp_verified' => false]);
            return;
        }

        // ── Generar OTP de 6 dígitos ─────────────────────────────────────────
        $otpCode   = (string) random_int(100000, 999999);
        $expiresAt = now()->addSeconds(self::OTP_TTL);

        // ── Guardar OTP en BD (campo whatsapp_otp) como hash ─────────────────
        $user->forceFill([
            'whatsapp_otp' => Hash::make($otpCode),
        ])->save();

        // Guardar datos de sesión (sin el código, solo metadatos)
        session([
            'otp_user_id'    => $user->id,
            'otp_expires_at' => $expiresAt->timestamp,
            'otp_attempts'   => 0,
            'otp_verified'   => false,
        ]);

        // ── Enviar OTP vía WhatsApp ───────────────────────────────────────────
        $empresa = Empresa::find(1);
        $appName = $empresa?->razon_social ?? config('app.name');

        $message = "*Codigo de verificacion - {$appName}*\n\n"
                 . "Tu codigo OTP para restablecer tu contrasena es:\n\n"
                 . "*{$otpCode}*\n\n"
                 . "Este codigo expira en 10 minutos.\n"
                 . "No compartas este codigo con nadie.";

        $whatsapp = new WhatsAppService($empresa);
        $result   = $whatsapp->sendMessage($fullPhone, $message, true);

        if ($result === null) {
            // WhatsApp falló — limpiamos lo guardado y notificamos
            $user->forceFill(['whatsapp_otp' => null])->save();
            session()->forget(['otp_user_id', 'otp_expires_at', 'otp_attempts', 'otp_verified']);

            Log::warning('OTP no enviado — WhatsApp retornó null', [
                'user_id' => $user->id,
                'phone'   => $fullPhone,
            ]);

            $this->errorMessage = 'No se pudo enviar el código OTP vía WhatsApp. Verifica que el servicio esté activo e inténtalo de nuevo.';
            return;
        }

        Log::info('OTP enviado para recuperación de contraseña', [
            'user_id'    => $user->id,
            'phone'      => $fullPhone,
            'expires_at' => $expiresAt->toDateTimeString(),
        ]);

        $this->successMessage = 'Se ha enviado un código OTP al número indicado por WhatsApp.';
        $this->step        = 'verify';
        $this->otpSent     = true;
        $this->otpAttempts = 0;
    }

    // ── PASO 2: Verificar OTP ─────────────────────────────────────────────────

    public function verifyOtp(): void
    {
        $this->errorMessage   = null;
        $this->successMessage = null;

        $this->validate([
            'otp' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ], [
            'otp.required' => 'El código OTP es obligatorio.',
            'otp.size'     => 'El código OTP debe tener exactamente 6 dígitos.',
            'otp.regex'    => 'El código OTP solo puede contener dígitos.',
        ]);

        $otpUserId    = session('otp_user_id');
        $otpExpiresAt = session('otp_expires_at');
        $attempts     = session('otp_attempts', 0);

        // Sin sesión activa
        if (!$otpUserId || !$otpExpiresAt) {
            $this->errorMessage = 'El código ha expirado o es inválido. Solicita uno nuevo.';
            $this->_resetToStep1();
            return;
        }

        // Verificar expiración
        if (now()->timestamp > $otpExpiresAt) {
            $this->_clearUserOtp($otpUserId);
            $this->errorMessage = 'El código OTP ha expirado. Por favor solicita uno nuevo.';
            $this->_resetToStep1();
            return;
        }

        // Verificar intentos máximos
        if ($attempts >= self::MAX_ATTEMPTS) {
            $this->_clearUserOtp($otpUserId);
            $this->errorMessage = 'Demasiados intentos fallidos. Por favor solicita un nuevo código.';
            $this->_resetToStep1();
            return;
        }

        // Recuperar el hash del OTP desde la BD
        $user = User::find($otpUserId);
        if (!$user || !$user->whatsapp_otp) {
            $this->errorMessage = 'El código es inválido o ya fue usado. Solicita uno nuevo.';
            $this->_resetToStep1();
            return;
        }

        // Verificar el código contra el hash en BD
        if (!Hash::check($this->otp, $user->whatsapp_otp)) {
            $attempts++;
            session(['otp_attempts' => $attempts]);
            $remaining = self::MAX_ATTEMPTS - $attempts;

            if ($remaining <= 0) {
                $this->_clearUserOtp($otpUserId);
                $this->errorMessage = 'Demasiados intentos fallidos. Por favor solicita un nuevo código.';
                $this->_resetToStep1();
            } else {
                $this->errorMessage = "Código incorrecto. Te quedan {$remaining} intento(s).";
            }
            return;
        }

        // ── OTP válido ── limpiar el hash de la BD y avanzar a paso 3 ────────
        $user->forceFill(['whatsapp_otp' => null])->save();
        session(['otp_verified' => true, 'otp_attempts' => 0]);

        $this->successMessage = '¡Código verificado correctamente! Ahora puedes cambiar tu contraseña.';
        $this->step = 'reset';
        $this->otp  = '';
    }

    // ── PASO 3: Cambiar contraseña ────────────────────────────────────────────

    public function resetPassword(): void
    {
        $this->errorMessage   = null;
        $this->successMessage = null;

        // Doble verificación de seguridad
        if (!session('otp_verified') || !session('otp_user_id')) {
            $this->errorMessage = 'Sesión de verificación inválida. Por favor comienza de nuevo.';
            $this->_resetToStep1();
            return;
        }

        $this->validate([
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'password.required'  => 'La nueva contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $user = User::find(session('otp_user_id'));
        if (!$user) {
            $this->errorMessage = 'No se pudo encontrar el usuario. Por favor intenta de nuevo.';
            $this->_resetToStep1();
            return;
        }

        // Actualizar contraseña
        $user->forceFill([
            'password'       => Hash::make($this->password),
            'remember_token' => \Illuminate\Support\Str::random(60),
            'whatsapp_otp'   => null, // asegurar limpieza
        ])->save();

        // Limpiar sesión OTP
        session()->forget(['otp_user_id', 'otp_expires_at', 'otp_verified', 'otp_attempts']);

        Log::info('Contraseña restablecida vía OTP WhatsApp', ['user_id' => $user->id]);

        session()->flash('status', '¡Tu contraseña ha sido restablecida exitosamente! Inicia sesión con tu nueva contraseña.');
        $this->redirect(route('login'), navigate: true);
    }

    /**
     * Reenviar: volver al paso 1.
     */
    public function resendOtp(): void
    {
        $userId = session('otp_user_id');
        if ($userId) {
            $this->_clearUserOtp($userId);
        }
        $this->step        = 'phone';
        $this->otp         = '';
        $this->otpSent     = false;
        $this->errorMessage   = null;
        $this->successMessage = null;
        session()->forget(['otp_user_id', 'otp_expires_at', 'otp_verified', 'otp_attempts']);
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    /**
     * Limpia el OTP de la BD del usuario.
     */
    private function _clearUserOtp(?int $userId): void
    {
        if ($userId) {
            User::where('id', $userId)->update(['whatsapp_otp' => null]);
        }
    }

    /**
     * Resetea completamente al paso 1.
     */
    private function _resetToStep1(): void
    {
        session()->forget(['otp_user_id', 'otp_expires_at', 'otp_verified', 'otp_attempts']);
        $this->step        = 'phone';
        $this->otp         = '';
        $this->otpSent     = false;
        $this->otpAttempts = 0;
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}