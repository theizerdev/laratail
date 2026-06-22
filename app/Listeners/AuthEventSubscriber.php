<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Events\Dispatcher;
use App\Models\SessionHistory;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthEventSubscriber
{
    /**
     * Handle user login events.
     */
    public function handleUserLogin(Login $event): void
    {
        // Registro en session_histories
        SessionHistory::create([
            'user_id' => $event->user->id,
            'empresa_id' => $event->user->empresa_id,
            'sucursal_id' => $event->user->sucursal_id,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'login_at' => now(),
            'session_id' => Request::session()->getId(),
        ]);

        // Registro en auditoría principal (activity_log)
        activity('acceso_sistema')
            ->causedBy($event->user)
            ->withProperties([
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'empresa_id' => $event->user->empresa_id,
                'sucursal_id' => $event->user->sucursal_id,
                'status' => 'success'
            ])
            ->log('Inicio de sesión exitoso');
    }

    /**
     * Handle user failed login events.
     */
    public function handleUserFailedLogin(Failed $event): void
    {
        $email = $event->credentials['email'] ?? 'desconocido';
        $ip = Request::ip();
        
        // Obtener la cantidad de intentos fallidos. Laravel usa "email|IP" como llave.
        $throttleKey = Str::transliterate(Str::lower($email).'|'.$ip);
        $attempts = RateLimiter::attempts($throttleKey);

        $activity = activity('acceso_sistema')
            ->withProperties([
                'ip_address' => $ip,
                'user_agent' => Request::userAgent(),
                'email_attempted' => $email,
                'attempts' => $attempts,
                'status' => 'failed'
            ]);

        // Si el usuario existe en BD pero falló la clave, registramos quién era
        if ($event->user) {
            $activity->causedBy($event->user);
        }

        $activity->log('Intento de inicio de sesión fallido');
    }

    /**
     * Handle user logout events.
     */
    public function handleUserLogout(Logout $event): void
    {
        if ($event->user) {
            // Buscamos la última sesión abierta del usuario.
            // Omitimos la búsqueda por session_id porque Laravel regenera el ID 
            // de la sesión inmediatamente después del evento de Login por seguridad,
            // por lo que el ID guardado al inicio no coincidirá con el del cierre.
            $sessionHistory = SessionHistory::withoutGlobalScopes()
                ->where('user_id', $event->user->id)
                ->whereNull('logout_at')
                ->latest('login_at')
                ->first();

            if ($sessionHistory) {
                $sessionHistory->logout_at = now();
                $sessionHistory->saveQuietly();
            }
            
            activity('acceso_sistema')
                ->causedBy($event->user)
                ->withProperties([
                    'ip_address' => Request::ip(),
                    'user_agent' => Request::userAgent(),
                    'status' => 'logout'
                ])
                ->log('Cierre de sesión');
        }
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            Login::class,
            [AuthEventSubscriber::class, 'handleUserLogin']
        );

        $events->listen(
            Failed::class,
            [AuthEventSubscriber::class, 'handleUserFailedLogin']
        );

        $events->listen(
            Logout::class,
            [AuthEventSubscriber::class, 'handleUserLogout']
        );
    }
}
