<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Pais;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        $query = Empresa::with('pais');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('razon_social', 'like', "%{$search}%")
                  ->orWhere('documento', 'like', "%{$search}%")
                  ->orWhere('representante_legal', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->orderBy('id', 'desc')->paginate($request->get('per_page', 10))
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'razon_social' => 'required|string|max:255',
            'documento' => 'required|string|max:255|unique:empresas',
            'direccion' => 'nullable|string',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'representante_legal' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'pais_id' => 'nullable|exists:pais,id',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'status' => 'boolean'
        ]);

        $data = $request->except('logo');

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('empresas/logos', 'public');
        }

        $empresa = Empresa::create($data);

        return response()->json([
            'message' => 'Empresa creada exitosamente',
            'data' => $empresa
        ], 201);
    }

    public function show($id)
    {
        $empresa = Empresa::findOrFail($id);
        return response()->json(['data' => $empresa]);
    }

    public function update(Request $request, $id)
    {
        $empresa = Empresa::findOrFail($id);

        $request->validate([
            'razon_social' => 'required|string|max:255',
            'documento' => 'required|string|max:255|unique:empresas,documento,' . $empresa->id,
            'direccion' => 'nullable|string',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'representante_legal' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'pais_id' => 'nullable|exists:pais,id',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'status' => 'boolean'
        ]);

        $data = $request->except('logo');

        if ($request->hasFile('logo')) {
            // Delete old logo if it exists
            if ($empresa->logo && Storage::disk('public')->exists($empresa->logo)) {
                Storage::disk('public')->delete($empresa->logo);
            }
            $data['logo'] = $request->file('logo')->store('empresas/logos', 'public');
        }

        $empresa->update($data);

        return response()->json([
            'message' => 'Empresa actualizada exitosamente',
            'data' => $empresa
        ]);
    }

    public function destroy($id)
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->delete();

        return response()->json([
            'message' => 'Empresa eliminada exitosamente'
        ]);
    }

    public function switchStatus(Request $request, $id)
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->status = !$empresa->status;
        $empresa->save();

        return response()->json([
            'message' => 'Estado de la empresa actualizado',
            'status' => $empresa->status
        ]);
    }

    public function paises()
    {
        $paises = Pais::where('activo', true)
            ->select('id', 'nombre', 'codigo_iso2')
            ->orderBy('nombre', 'asc')
            ->get();

        return response()->json($paises);
    }
}
