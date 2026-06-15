<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Spatie\Activitylog\Models\Activity;

class LogoutListener
{
    public function handle(Logout $event): void
    {
        $user = $event->user;

        if (!$user) {
            return;
        }

        $request = request();

        activity()
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'evento' => 'logout',
                'usuario_nombre' => $user->name,
                'usuario_email' => $user->email,
                'empresa_id' => $user->empresa_id,
                'sucursal_id' => $user->sucursal_id,
            ])
            ->log('Cierre de sesión');
    }
}
