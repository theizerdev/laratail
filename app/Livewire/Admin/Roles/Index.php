<?php

namespace App\Livewire\Admin\Roles;

use App\Models\User;
use App\Traits\PermissionOrganizer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.admin')]
#[Title('Roles')]
class Index extends Component
{
    use PermissionOrganizer;
    public string $search = '';

    public function delete(int $id): void
    {
        $role = Role::findOrFail($id);

        // Prevent deleting super-admin
        if ($role->name === 'super-admin') {
            session()->flash('error', 'No se puede eliminar el rol super-admin.');

            return;
        }

        // Check if users have this role directly
        $usersWithRole = User::role($role)->count();

        if ($usersWithRole > 0) {
            session()->flash('error', "No se puede eliminar un rol asignado a {$usersWithRole} usuario(s).");

            return;
        }

        // Capture data for activity log before deletion
        $roleName = $role->name;
        $roleId = $role->id;
        $deletedPermissions = $role->permissions->pluck('name')->toArray();

        $role->syncPermissions([]);
        $role->delete();

        // Log activity in Spanish
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'evento' => 'deleted',
                'tabla' => 'roles',
                'registro_id' => $roleId,
                'identificador_registro' => $roleName,
                'usuario_nombre' => auth()->user()->name,
                'usuario_email' => auth()->user()->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'permisos_eliminados' => $deletedPermissions,
            ])
            ->log("Se eliminó el registro de rol - {$roleName}");

        session()->flash('success', 'Rol eliminado correctamente.');
    }

    public function render()
    {
        $roles = Role::query()
            ->with('permissions')
            ->withCount('permissions')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();

        $permissionsBySector = $this->getPermissionsBySector();
        $sectors = $this->getSectors();
        $modules = $this->getModules();

        // Count users per role
        $usersPerRole = [];
        foreach ($roles as $role) {
            $usersPerRole[$role->id] = User::role($role)->count();
        }

        // Stats
        $stats = [
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'total_sectors' => $sectors->count(),
            'total_modules' => $modules->count(),
            'total_users' => User::count(),
        ];

        return view('livewire.admin.roles.index', [
            'roles' => $roles,
            'permissionsBySector' => $permissionsBySector,
            'usersPerRole' => $usersPerRole,
            'stats' => $stats,
            'sectors' => $sectors,
            'modules' => $modules,
        ]);
    }
}
