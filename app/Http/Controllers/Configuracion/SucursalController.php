<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\Sucursal;
use App\Models\Empresa;
use Illuminate\Http\Request;

class SucursalController extends Controller
{
    public function index(Request $request)
    {
        $query = Sucursal::with('empresa');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%")
                  ->orWhere('direccion', 'like', "%{$search}%");
            });
        }

        $sucursales = $query->paginate(15);
        return response()->json($sucursales);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'status' => 'boolean',
        ]);

        $sucursal = Sucursal::create($validated);

        return response()->json($sucursal, 201);
    }

    public function show($id)
    {
        $sucursal = Sucursal::with('empresa')->findOrFail($id);
        return response()->json($sucursal);
    }

    public function update(Request $request, $id)
    {
        $sucursal = Sucursal::findOrFail($id);

        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'status' => 'boolean',
        ]);

        $sucursal->update($validated);

        return response()->json($sucursal);
    }

    public function destroy($id)
    {
        $sucursal = Sucursal::findOrFail($id);
        $sucursal->delete();

        return response()->json(['message' => 'Sucursal eliminada correctamente.']);
    }

    public function switchStatus($id)
    {
        $sucursal = Sucursal::findOrFail($id);
        $sucursal->status = !$sucursal->status;
        $sucursal->save();

        return response()->json([
            'message' => 'Estado actualizado',
            'status' => $sucursal->status
        ]);
    }

    // Método auxiliar para obtener empresas para el dropdown del Modal
    public function empresas()
    {
        $empresas = Empresa::select('id', 'razon_social', 'latitud', 'longitud')->where('status', true)->get();
        return response()->json($empresas);
    }
}
