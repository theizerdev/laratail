<?php
 
namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\Pais;
use Illuminate\Http\Request;

class PaisController extends Controller
{
    public function index()
    {
        $paises = Pais::orderBy('nombre', 'asc')->get();
        return response()->json($paises);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo_iso2' => 'required|string|size:2|unique:pais',
            'codigo_iso3' => 'required|string|size:3|unique:pais',
            'codigo_telefonico' => 'nullable|string|max:10',
            'moneda_principal' => 'nullable|string|max:10',
            'idioma_principal' => 'nullable|string|max:10',
            'continente' => 'nullable|string|max:50',
            'zona_horaria' => 'nullable|string|max:50',
            'formato_fecha' => 'nullable|string|max:20',
            'formato_moneda' => 'nullable|string|max:20',
            'impuesto_predeterminado' => 'nullable|numeric|between:0,999.99',
            'separador_miles' => 'nullable|string|max:1',
            'separador_decimales' => 'nullable|string|max:1',
            'decimales_moneda' => 'nullable|integer|min:0|max:5',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'activo' => 'boolean',
        ]);

        $pais = Pais::create($validated);

        return response()->json($pais, 201);
    }

    public function show(string $id)
    {
        $pais = Pais::findOrFail($id);
        return response()->json($pais);
    }

    public function update(Request $request, string $id)
    {
        $pais = Pais::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo_iso2' => 'required|string|size:2|unique:pais,codigo_iso2,' . $pais->id,
            'codigo_iso3' => 'required|string|size:3|unique:pais,codigo_iso3,' . $pais->id,
            'codigo_telefonico' => 'nullable|string|max:10',
            'moneda_principal' => 'nullable|string|max:10',
            'idioma_principal' => 'nullable|string|max:10',
            'continente' => 'nullable|string|max:50',
            'zona_horaria' => 'nullable|string|max:50',
            'formato_fecha' => 'nullable|string|max:20',
            'formato_moneda' => 'nullable|string|max:20',
            'impuesto_predeterminado' => 'nullable|numeric|between:0,999.99',
            'separador_miles' => 'nullable|string|max:1',
            'separador_decimales' => 'nullable|string|max:1',
            'decimales_moneda' => 'nullable|integer|min:0|max:5',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'activo' => 'boolean',
        ]);

        $pais->update($validated);

        return response()->json($pais);
    }

    public function destroy(string $id)
    {
        $pais = Pais::findOrFail($id);
        $pais->delete();

        return response()->json(null, 204);
    }
}
