<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all()->groupBy('module');
        return response()->json($permissions);
    }
}
