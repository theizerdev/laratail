<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Módulo: Seguridad
        $seguridadPermissions = [
            ['name' => 'users.view', 'display_name' => 'Ver Usuarios', 'module' => 'Seguridad'],
            ['name' => 'users.create', 'display_name' => 'Crear Usuarios', 'module' => 'Seguridad'],
            ['name' => 'users.edit', 'display_name' => 'Editar Usuarios', 'module' => 'Seguridad'],
            ['name' => 'users.delete', 'display_name' => 'Eliminar Usuarios', 'module' => 'Seguridad'],

            ['name' => 'roles.view', 'display_name' => 'Ver Roles', 'module' => 'Seguridad'],
            ['name' => 'roles.create', 'display_name' => 'Crear Roles', 'module' => 'Seguridad'],
            ['name' => 'roles.edit', 'display_name' => 'Editar Roles', 'module' => 'Seguridad'],
            ['name' => 'roles.delete', 'display_name' => 'Eliminar Roles', 'module' => 'Seguridad'],

            ['name' => 'permissions.view', 'display_name' => 'Ver Permisos', 'module' => 'Seguridad'],

            ['name' => 'countries.view', 'display_name' => 'Ver Países', 'module' => 'Seguridad'],
            ['name' => 'countries.create', 'display_name' => 'Crear Países', 'module' => 'Seguridad'],
            ['name' => 'countries.edit', 'display_name' => 'Editar Países', 'module' => 'Seguridad'],
            ['name' => 'countries.delete', 'display_name' => 'Eliminar Países', 'module' => 'Seguridad'],
        ];

        foreach ($seguridadPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }

        // Módulo: Configuración
        $configuracionPermissions = [
            ['name' => 'empresas.view', 'display_name' => 'Ver Empresas', 'module' => 'Configuración'],
            ['name' => 'empresas.create', 'display_name' => 'Crear Empresas', 'module' => 'Configuración'],
            ['name' => 'empresas.edit', 'display_name' => 'Editar Empresas', 'module' => 'Configuración'],
            ['name' => 'empresas.delete', 'display_name' => 'Eliminar Empresas', 'module' => 'Configuración'],
            ['name' => 'sucursales.view', 'display_name' => 'Ver Sucursales', 'module' => 'Configuración'],
            ['name' => 'sucursales.create', 'display_name' => 'Crear Sucursales', 'module' => 'Configuración'],
            ['name' => 'sucursales.edit', 'display_name' => 'Editar Sucursales', 'module' => 'Configuración'],
            ['name' => 'sucursales.delete', 'display_name' => 'Eliminar Sucursales', 'module' => 'Configuración'],
        ];

        foreach ($configuracionPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }

        // Módulo: Integraciones
        $integracionesPermissions = [
            ['name' => 'integraciones.view', 'display_name' => 'Ver Integraciones', 'module' => 'Integraciones'],
            ['name' => 'integraciones.whatsapp.view', 'display_name' => 'Ver WhatsApp CRM', 'module' => 'Integraciones'],
        ];

        foreach ($integracionesPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }

        // Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'display_name' => 'Super Administrador']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'display_name' => 'Administrador']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'display_name' => 'Usuario']);

        // Asignar permisos al Admin (todos los de seguridad, por ejemplo)
        $admin->givePermissionTo(Permission::all());

        // El Super Admin no necesita asignación de permisos si usamos Gate::before
        // Pero para simplificar, le podemos dar todos también
        $superAdmin->givePermissionTo(Permission::all());

        // Crear un usuario Super Admin de prueba si no existe
        $superAdminUser = User::firstOrCreate([
            'email' => 'superadmin@example.com',
        ], [
            'name' => 'Super Admin',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);
        
        $superAdminUser->assignRole($superAdmin);
    }
}
