<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Spatie\Activitylog\Models\Activity;

class LoginSuccessListener
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        $request = request();

        activity()
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'evento' => 'login',
                'usuario_nombre' => $user->name,
                'usuario_email' => $user->email,
                'empresa_id' => $user->empresa_id,
                'sucursal_id' => $user->sucursal_id,
            ])
            ->log('Inicio de sesión');
    }
}
