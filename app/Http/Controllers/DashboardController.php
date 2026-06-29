<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estudiante;
use App\Models\RegistroAcceso;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DashboardController extends Controller
{
    public function index()
    {
        // Total estudiantes
        $totalEstudiantes = Estudiante::count();
        
        // Accesos de hoy
        $hoy = Carbon::today();
        
        $accesosHoy = RegistroAcceso::whereDate('fecha_hora', $hoy)->count();
        
        $entradasHoy = RegistroAcceso::whereDate('fecha_hora', $hoy)
            ->where('tipo', 'entrada')
            ->count();
            
        $salidasHoy = RegistroAcceso::whereDate('fecha_hora', $hoy)
            ->where('tipo', 'salida')
            ->count();

        // Gráfica semanal (últimos 7 días)
        $fechas = [];
        $entradasData = [];
        $salidasData = [];

        $period = CarbonPeriod::create(Carbon::today()->subDays(6), Carbon::today());
        
        // Agrupar los accesos por fecha y tipo
        $registrosSemana = RegistroAcceso::where('fecha_hora', '>=', Carbon::today()->subDays(6))
            ->selectRaw('DATE(fecha_hora) as date, tipo, COUNT(*) as count')
            ->groupBy('date', 'tipo')
            ->get();

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $fechas[] = $date->isoFormat('ddd D'); // ej. "lun 22"
            
            $entradas = $registrosSemana->where('date', $dateString)->where('tipo', 'entrada')->first();
            $entradasData[] = $entradas ? $entradas->count : 0;
            
            $salidas = $registrosSemana->where('date', $dateString)->where('tipo', 'salida')->first();
            $salidasData[] = $salidas ? $salidas->count : 0;
        }

        // Feed de Accesos Recientes (últimos 8)
        $recentActivity = RegistroAcceso::with('estudiante')
            ->latest('fecha_hora')
            ->take(8)
            ->get()
            ->map(function ($acceso) {
                return [
                    'id' => $acceso->id,
                    'estudiante_nombre' => $acceso->estudiante ? $acceso->estudiante->nombre . ' ' . $acceso->estudiante->apellido : 'Desconocido',
                    'grado' => $acceso->estudiante ? $acceso->estudiante->grado : '-',
                    'foto_url' => $acceso->estudiante ? $acceso->estudiante->foto_url : null,
                    'tipo' => $acceso->tipo, // entrada o salida
                    'metodo' => $acceso->metodo,
                    'hora' => Carbon::parse($acceso->fecha_hora)->format('h:i A'),
                    'fecha' => Carbon::parse($acceso->fecha_hora)->format('d/m/Y')
                ];
            });

        return response()->json([
            'metrics' => [
                'total_estudiantes' => $totalEstudiantes,
                'accesos_hoy' => $accesosHoy,
                'entradas_hoy' => $entradasHoy,
                'salidas_hoy' => $salidasHoy,
            ],
            'chart_data' => [
                'labels' => $fechas,
                'entradas' => $entradasData,
                'salidas' => $salidasData,
            ],
            'recent_activity' => $recentActivity
        ]);
    }
}
