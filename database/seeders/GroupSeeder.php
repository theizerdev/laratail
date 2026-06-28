<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class GroupSeeder extends Seeder
{
    /**
     * Seed the default groups and assign roles.
     */
    public function run(): void
    {
        $groups = [
            [
                'name' => 'Administradores',
                'description' => 'Grupo de administradores del sistema',
                'role' => 'admin',
            ],
            [
                'name' => 'Editores',
                'description' => 'Grupo de editores de contenido',
                'role' => 'editor',
            ],
            [
                'name' => 'Visualizadores',
                'description' => 'Grupo con acceso de solo lectura',
                'role' => 'viewer',
            ],
            [
                'name' => 'Clientes',
                'description' => 'Grupo para los clientes registrados en la tienda',
                'role' => 'cliente',
            ],
        ];

        foreach ($groups as $groupData) {
            $group = Group::firstOrCreate(
                ['name' => $groupData['name']],
                [
                    'description' => $groupData['description'],
                    'is_active' => true,
                ]
            );

            $role = Role::where('name', $groupData['role'])->first();

            if ($role) {
                $group->syncRoles([$role]);
            }
        }
    }
}
