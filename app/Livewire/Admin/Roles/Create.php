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
#[Title('Crear Rol')]
class Create extends Component
{
    use PermissionOrganizer;
    public string $name = '';
    public array $selectedPermissions = [];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name',
            'selectedPermissions' => 'array',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $role = Role::create([
            'name' => $this->name,
            'guard_name' => 'web',
        ]);

        // Sync permissions
        $assignedPermissions = [];
        if (!empty($this->selectedPermissions)) {
            $permissions = Permission::whereIn('id', $this->selectedPermissions)->get();
            $role->syncPermissions($permissions);
            $assignedPermissions = $permissions->pluck('name')->toArray();
        }

        // Log activity in Spanish
        activity()
            ->performedOn($role)
            ->causedBy(auth()->user())
            ->withProperties([
                'evento' => 'created',
                'tabla' => 'roles',
                'registro_id' => $role->id,
                'identificador_registro' => $role->name,
                'usuario_nombre' => auth()->user()->name,
                'usuario_email' => auth()->user()->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'permisos_asignados' => $assignedPermissions,
                'total_permisos' => count($assignedPermissions),
            ])
            ->log("Se creó un nuevo registro de rol - {$role->name}");

        session()->flash('success', 'Rol creado correctamente.');

        $this->redirect(route('admin.roles'), navigate: true);
    }

    public function selectAllPermissions(): void
    {
        $this->selectedPermissions = Permission::pluck('id')->map(fn ($id) => (string) $id)->toArray();
    }

    public function clearPermissions(): void
    {
        $this->selectedPermissions = [];
    }

    public function render()
    {
        $permissionsBySector = $this->getPermissionsBySector();
        $sectors = $this->getSectors();
        $modules = $this->getModules();

        return view('livewire.admin.roles.create', [
            'permissionsBySector' => $permissionsBySector,
            'sectors' => $sectors,
            'modules' => $modules,
        ]);
    }
}