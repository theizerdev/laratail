<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        // Gracias a Multitenantable Global Scope, estas consultas ya vienen filtradas 
        // para la Empresa y Sucursal correspondiente del usuario autenticado.
        
        $usersCount = User::count();
        
        // Role no tiene Multitenantable por defecto a menos que lo hayamos añadido, 
        // pero podemos asumir que se limita por guardia o que cuenta los globales.
        $rolesCount = Role::count();
        
        // Accesos de hoy
        $loginsToday = Activity::where('log_name', 'acceso_sistema')
            ->where('description', 'Inicio de sesión exitoso')
            ->whereDate('created_at', today())
            ->count();
            
        // Intentos fallidos hoy
        $failedLoginsToday = Activity::where('log_name', 'acceso_sistema')
            ->where('description', 'Intento de inicio de sesión fallido')
            ->whereDate('created_at', today())
            ->count();

        // Últimos 5 accesos al sistema
        $recentActivity = Activity::with('causer')
            ->where('log_name', 'acceso_sistema')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'causer_name' => $activity->causer ? $activity->causer->name : ($activity->properties['email_attempted'] ?? 'Desconocido'),
                    'ip_address' => $activity->properties['ip_address'] ?? null,
                    'status' => $activity->properties['status'] ?? null,
                    'created_at' => $activity->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'metrics' => [
                'users' => $usersCount,
                'roles' => $rolesCount,
                'logins_today' => $loginsToday,
                'failed_logins_today' => $failedLoginsToday,
            ],
            'recent_activity' => $recentActivity
        ]);
    }
}
