<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstudianteController extends Controller
{
    public function index(Request $request)
    {
        $query = Estudiante::with('representantes');
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%")
                  ->orWhere('codigo_acceso', 'like', "%{$search}%");
        }

        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'dni' => 'nullable|string|unique:estudiantes,dni',
            'grado' => 'nullable|string|max:255',
            'seccion' => 'nullable|string|max:255',
            'codigo_acceso' => 'nullable|string|unique:estudiantes,codigo_acceso',
            'fecha_nacimiento' => 'nullable|date',
            'edad' => 'nullable|integer',
            'genero' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:50',
            'representantes' => 'nullable|array',
            'representantes.*.id' => 'nullable|exists:representantes,id',
            'foto_base64' => 'nullable|string',
            'huella_template' => 'nullable|string',
            'representantes' => 'nullable|array',
            'representantes.*.id' => 'nullable|exists:representantes,id',
            'representantes.*.nombre' => 'required_without:representantes.*.id|string|max:255',
            'representantes.*.dni' => 'nullable|string',
            'representantes.*.telefono' => 'nullable|string',
            'representantes.*.telefono_secundario' => 'nullable|string',
            'representantes.*.email' => 'nullable|email',
            'representantes.*.relacion' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $fotoPath = null;
            if (!empty($validated['foto_base64'])) {
                $image_parts = explode(";base64,", $validated['foto_base64']);
                if (count($image_parts) == 2) {
                    $image_base64 = base64_decode($image_parts[1]);
                    $fotoName = 'estudiantes/' . uniqid() . '.png';
                    \Illuminate\Support\Facades\Storage::disk('public')->put($fotoName, $image_base64);
                    $fotoPath = $fotoName;
                }
            }

            $estudiante = Estudiante::create([
                'nombre' => $validated['nombre'],
                'apellido' => $validated['apellido'] ?? null,
                'dni' => $validated['dni'] ?? null,
                'grado' => $validated['grado'] ?? null,
                'seccion' => $validated['seccion'] ?? null,
                'codigo_acceso' => $validated['codigo_acceso'] ?? null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                'edad' => $validated['edad'] ?? null,
                'genero' => $validated['genero'] ?? null,
                'email' => $validated['email'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'foto_path' => $fotoPath,
                'huella_template' => $validated['huella_template'] ?? null,
                'empresa_id' => $request->user()->empresa_id,
                'sucursal_id' => $request->user()->sucursal_id,
            ]);

            if (!empty($validated['representantes'])) {
                $representantesData = [];
                foreach ($validated['representantes'] as $repData) {
                    $repId = $repData['id'] ?? null;
                    if (!$repId) {
                        // Create inline
                        $nuevoRep = \App\Models\Representante::create([
                            'nombre' => $repData['nombre'],
                            'dni' => $repData['dni'] ?? null,
                            'telefono' => $repData['telefono'] ?? null,
                            'telefono_secundario' => $repData['telefono_secundario'] ?? null,
                            'email' => $repData['email'] ?? null,
                            'empresa_id' => $request->user()->empresa_id,
                            'sucursal_id' => $request->user()->sucursal_id,
                        ]);
                        $repId = $nuevoRep->id;

                        // Send WhatsApp notification
                        if (!empty($repData['telefono'])) {
                            try {
                                $empresa = $request->user()->empresa()->with('pais')->first();
                                $codigoPais = $empresa?->pais?->codigo_telefono ?? '58';
                                
                                $telefonoFormateado = preg_replace('/[^0-9]/', '', $repData['telefono']);
                                if (!str_starts_with($telefonoFormateado, $codigoPais)) {
                                    if (str_starts_with($telefonoFormateado, '0')) {
                                        $telefonoFormateado = substr($telefonoFormateado, 1);
                                    }
                                    $telefonoFormateado = $codigoPais . $telefonoFormateado;
                                }

                                $waService = new \App\Services\WhatsAppService($empresa);
                                $mensaje = "¡Hola {$repData['nombre']}! Has sido registrado(a) como representante del estudiante {$estudiante->nombre} {$estudiante->apellido} en nuestro sistema de acceso escolar.";
                                $waService->sendMessage($telefonoFormateado, $mensaje, true);
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Error enviando WhatsApp al representante: ' . $e->getMessage());
                            }
                        }
                    }
                    $representantesData[$repId] = ['relacion' => $repData['relacion'] ?? 'Representante'];
                }
                $estudiante->representantes()->sync($representantesData);
            }

            DB::commit();
            return response()->json($estudiante->load('representantes'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el estudiante', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        $estudiante = Estudiante::with('representantes')->findOrFail($id);
        return response()->json($estudiante);
    }

    public function update(Request $request, string $id)
    {
        $estudiante = Estudiante::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'dni' => 'nullable|string|unique:estudiantes,dni,' . $estudiante->id,
            'grado' => 'nullable|string|max:255',
            'seccion' => 'nullable|string|max:255',
            'codigo_acceso' => 'nullable|string|unique:estudiantes,codigo_acceso,' . $estudiante->id,
            'fecha_nacimiento' => 'nullable|date',
            'edad' => 'nullable|integer',
            'genero' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:50',
            'foto_base64' => 'nullable|string',
            'huella_template' => 'nullable|string',
            'representantes' => 'nullable|array',
            'representantes.*.id' => 'nullable|exists:representantes,id',
            'representantes.*.nombre' => 'required_without:representantes.*.id|string|max:255',
            'representantes.*.dni' => 'nullable|string',
            'representantes.*.telefono' => 'nullable|string',
            'representantes.*.telefono_secundario' => 'nullable|string',
            'representantes.*.email' => 'nullable|email',
            'representantes.*.relacion' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $fotoPath = $estudiante->foto_path;
            if (!empty($validated['foto_base64'])) {
                if ($fotoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($fotoPath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($fotoPath);
                }
                $image_parts = explode(";base64,", $validated['foto_base64']);
                if (count($image_parts) == 2) {
                    $image_base64 = base64_decode($image_parts[1]);
                    $fotoName = 'estudiantes/' . uniqid() . '.png';
                    \Illuminate\Support\Facades\Storage::disk('public')->put($fotoName, $image_base64);
                    $fotoPath = $fotoName;
                }
            }

            $estudiante->update([
                'nombre' => $validated['nombre'] ?? $estudiante->nombre,
                'apellido' => $validated['apellido'] ?? $estudiante->apellido,
                'dni' => $validated['dni'] ?? $estudiante->dni,
                'grado' => $validated['grado'] ?? $estudiante->grado,
                'seccion' => $validated['seccion'] ?? $estudiante->seccion,
                'codigo_acceso' => $validated['codigo_acceso'] ?? $estudiante->codigo_acceso,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? $estudiante->fecha_nacimiento,
                'edad' => $validated['edad'] ?? $estudiante->edad,
                'genero' => $validated['genero'] ?? $estudiante->genero,
                'email' => $validated['email'] ?? $estudiante->email,
                'telefono' => $validated['telefono'] ?? $estudiante->telefono,
                'foto_path' => $fotoPath,
                'huella_template' => array_key_exists('huella_template', $validated) ? $validated['huella_template'] : $estudiante->huella_template,
            ]);

            if (isset($validated['representantes'])) {
                $representantesData = [];
                foreach ($validated['representantes'] as $repData) {
                    $repId = $repData['id'] ?? null;
                    if (!$repId) {
                        // Create inline
                        $nuevoRep = \App\Models\Representante::create([
                            'nombre' => $repData['nombre'],
                            'dni' => $repData['dni'] ?? null,
                            'telefono' => $repData['telefono'] ?? null,
                            'telefono_secundario' => $repData['telefono_secundario'] ?? null,
                            'email' => $repData['email'] ?? null,
                            'empresa_id' => $request->user()->empresa_id,
                            'sucursal_id' => $request->user()->sucursal_id,
                        ]);
                        $repId = $nuevoRep->id;

                        // Send WhatsApp notification
                        if (!empty($repData['telefono'])) {
                            try {
                                $empresa = $request->user()->empresa()->with('pais')->first();
                                $codigoPais = $empresa?->pais?->codigo_telefono ?? '58';
                                
                                $telefonoFormateado = preg_replace('/[^0-9]/', '', $repData['telefono']);
                                if (!str_starts_with($telefonoFormateado, $codigoPais)) {
                                    if (str_starts_with($telefonoFormateado, '0')) {
                                        $telefonoFormateado = substr($telefonoFormateado, 1);
                                    }
                                    $telefonoFormateado = $codigoPais . $telefonoFormateado;
                                }

                                $waService = new \App\Services\WhatsAppService($empresa);
                                $mensaje = "¡Hola {$repData['nombre']}! Has sido registrado(a) como representante del estudiante {$estudiante->nombre} {$estudiante->apellido} en nuestro sistema de acceso escolar.";
                                $waService->sendMessage($telefonoFormateado, $mensaje, true);
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Error enviando WhatsApp al representante: ' . $e->getMessage());
                            }
                        }
                    } else {
                        // Optional: Update existing rep?
                    }
                    $representantesData[$repId] = ['relacion' => $repData['relacion'] ?? 'Representante'];
                }
                $estudiante->representantes()->sync($representantesData);
            }

            DB::commit();
            return response()->json($estudiante->load('representantes'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar el estudiante', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        $estudiante = Estudiante::findOrFail($id);
        $estudiante->delete();
        return response()->json(['message' => 'Estudiante eliminado con éxito']);
    }
}
