<?php

namespace App\Http\Controllers;

use App\Models\RegistroAcceso;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class RegistroAccesoController extends Controller
{
    public function index(Request $request)
    {
        $query = RegistroAcceso::with('estudiante');

        if ($request->has('fecha')) {
            $query->whereDate('fecha_hora', $request->fecha);
        } else {
            $query->whereDate('fecha_hora', Carbon::today());
        }

        $accesos = $query->orderBy('fecha_hora', 'desc')->paginate(50);

        $accesos->getCollection()->transform(function ($acceso) {
            if ($acceso->tipo === 'salida') {
                $entrada = RegistroAcceso::where('estudiante_id', $acceso->estudiante_id)
                    ->where('tipo', 'entrada')
                    ->whereDate('fecha_hora', Carbon::parse($acceso->fecha_hora)->toDateString())
                    ->where('fecha_hora', '<', $acceso->fecha_hora)
                    ->orderBy('fecha_hora', 'desc')
                    ->first();
                
                if ($entrada) {
                    $acceso->estadia_minutos = Carbon::parse($entrada->fecha_hora)->diffInMinutes(Carbon::parse($acceso->fecha_hora));
                } else {
                    $acceso->estadia_minutos = null;
                }
            } else {
                $acceso->estadia_minutos = null;
            }
            return $acceso;
        });

        return response()->json($accesos);
    }

    public function escanear(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string',
            'metodo' => 'nullable|string'
        ]);

        $codigo = $request->codigo;
        $metodo = $request->metodo ?? 'codigo';

        // Buscar estudiante por código de acceso, DNI o ID (como fallback)
        $estudiante = Estudiante::with(['representantes', 'empresa.pais'])
            ->where('codigo_acceso', $codigo)
            ->orWhere('dni', $codigo)
            ->first();

        // Fallback: Si el código tiene formato STU-{id}
        if (!$estudiante && str_starts_with($codigo, 'STU-')) {
            $id = str_replace('STU-', '', $codigo);
            $estudiante = Estudiante::with(['representantes', 'empresa.pais'])->find($id);
        }

        if (!$estudiante) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado.'
            ], 404);
        }

        // Determinar Entrada o Salida basado en el último registro de hoy
        $ultimoRegistroHoy = RegistroAcceso::where('estudiante_id', $estudiante->id)
            ->whereDate('fecha_hora', Carbon::today())
            ->orderBy('fecha_hora', 'desc')
            ->first();

        $tipo = 'entrada';
        if ($ultimoRegistroHoy && $ultimoRegistroHoy->tipo === 'entrada') {
            $tipo = 'salida';
        }

        // Crear el registro
        $registro = RegistroAcceso::create([
            'estudiante_id' => $estudiante->id,
            'tipo' => $tipo,
            'fecha_hora' => Carbon::now(),
            'metodo' => $metodo,
            'estado' => 'A tiempo', // Aquí se puede agregar lógica de horario
            'empresa_id' => $estudiante->empresa_id,
            'sucursal_id' => $estudiante->sucursal_id,
        ]);

        // Notificar por WhatsApp al representante
        $this->notificarRepresentantes($estudiante, $registro);

        return response()->json([
            'success' => true,
            'message' => 'Acceso registrado correctamente.',
            'tipo' => $tipo,
            'estudiante' => [
                'nombre' => $estudiante->nombre,
                'apellido' => $estudiante->apellido,
                'grado' => $estudiante->grado,
                'seccion' => $estudiante->seccion,
                'foto_url' => $estudiante->foto_url
            ]
        ]);
    }

    private function notificarRepresentantes($estudiante, $registro)
    {
        if (!$estudiante->representantes || $estudiante->representantes->isEmpty()) {
            return;
        }

        try {
            $empresa = $estudiante->empresa;
            $codigoPais = $empresa?->pais?->codigo_telefono ?? '58';
            $waService = new WhatsAppService($empresa);

            $horaStr = Carbon::parse($registro->fecha_hora)->format('h:i A');
            $tipoStr = $registro->tipo === 'entrada' ? 'Ingreso' : 'Salida';

            $mensaje = "🔔 *Notificación de Acceso*\n\n"
                     . "El estudiante *{$estudiante->nombre} {$estudiante->apellido}* ha registrado su *{$tipoStr}* a la institución a las {$horaStr}.\n\n"
                     . "_Sistema de Control de Acceso Escolar_";

            foreach ($estudiante->representantes as $rep) {
                if (!empty($rep->telefono)) {
                    $telefonoFormateado = preg_replace('/[^0-9]/', '', $rep->telefono);
                    if (!str_starts_with($telefonoFormateado, $codigoPais)) {
                        if (str_starts_with($telefonoFormateado, '0')) {
                            $telefonoFormateado = substr($telefonoFormateado, 1);
                        }
                        $telefonoFormateado = $codigoPais . $telefonoFormateado;
                    }
                    $waService->sendMessage($telefonoFormateado, $mensaje, false);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error enviando WhatsApp de acceso: ' . $e->getMessage());
        }
    }
}
