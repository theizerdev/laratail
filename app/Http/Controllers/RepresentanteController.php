<?php

namespace App\Http\Controllers;

use App\Models\Representante;
use Illuminate\Http\Request;

class RepresentanteController extends Controller
{
    public function index(Request $request)
    {
        $query = Representante::query();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%");
        }

        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'dni' => 'nullable|string|unique:representantes,dni',
            'telefono' => 'nullable|string|max:255',
            'telefono_secundario' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $representante = Representante::create(array_merge($validated, [
            'empresa_id' => $request->user()->empresa_id,
            'sucursal_id' => $request->user()->sucursal_id,
        ]));

        return response()->json($representante, 201);
    }

    public function show(string $id)
    {
        $representante = Representante::with('estudiantes')->findOrFail($id);
        return response()->json($representante);
    }

    public function update(Request $request, string $id)
    {
        $representante = Representante::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'dni' => 'nullable|string|unique:representantes,dni,' . $representante->id,
            'telefono' => 'nullable|string|max:255',
            'telefono_secundario' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $representante->update($validated);

        return response()->json($representante);
    }

    public function destroy(string $id)
    {
        $representante = Representante::findOrFail($id);
        $representante->delete();
        return response()->json(['message' => 'Representante eliminado con éxito']);
    }
}
