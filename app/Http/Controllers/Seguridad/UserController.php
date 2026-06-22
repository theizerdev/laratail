<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'empresa', 'sucursal']);
        
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->paginate(15);
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'roles' => 'array',
            'roles.*' => 'string|exists:roles,name',
            'status' => 'nullable|string|max:50',
            'telefono' => 'nullable|string|max:20',
            'empresa_id' => 'nullable|exists:empresas,id',
            'sucursal_id' => 'nullable|exists:sucursales,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'status' => $request->status ?? 'activo',
            'telefono' => $request->telefono,
            'empresa_id' => $request->empresa_id,
            'sucursal_id' => $request->sucursal_id,
        ]);

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return response()->json($user->load(['roles', 'empresa', 'sucursal']), 201);
    }

    public function show(string $id)
    {
        $user = User::with(['roles', 'empresa', 'sucursal'])->findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'roles' => 'array',
            'roles.*' => 'string|exists:roles,name',
            'status' => 'nullable|string|max:50',
            'telefono' => 'nullable|string|max:20',
            'empresa_id' => 'nullable|exists:empresas,id',
            'sucursal_id' => 'nullable|exists:sucursales,id',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->has('status')) $user->status = $request->status;
        if ($request->has('telefono')) $user->telefono = $request->telefono;
        if ($request->has('empresa_id')) $user->empresa_id = $request->empresa_id;
        if ($request->has('sucursal_id')) $user->sucursal_id = $request->sucursal_id;
        
        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->save();

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return response()->json($user->load(['roles', 'empresa', 'sucursal']));
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Cannot delete yourself'], 403);
        }

        $user->delete();
        
        return response()->json(null, 204);
    }
}
