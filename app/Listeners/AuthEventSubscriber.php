<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;
use App\Models\SessionHistory;
use Illuminate\Support\Facades\Request;

class AuthEventSubscriber
{
    /**
     * Handle user login events.
     */
    public function handleUserLogin(Login $event): void
    {
        SessionHistory::create([
            'user_id' => $event->user->id,
            'empresa_id' => $event->user->empresa_id,
            'sucursal_id' => $event->user->sucursal_id,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'login_at' => now(),
            'session_id' => Request::session()->getId(),
        ]);
    }

    /**
     * Handle user logout events.
     */
    public function handleUserLogout(Logout $event): void
    {
        if ($event->user) {
            $sessionHistory = SessionHistory::where('user_id', $event->user->id)
                ->where('session_id', Request::session()->getId())
                ->whereNull('logout_at')
                ->latest()
                ->first();

            if ($sessionHistory) {
                $sessionHistory->update([
                    'logout_at' => now(),
                ]);
            }
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
            Logout::class,
            [AuthEventSubscriber::class, 'handleUserLogout']
        );
    }
}
