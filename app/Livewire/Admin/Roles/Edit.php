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
#[Title('Editar Rol')]
class Edit extends Component
{
    use PermissionOrganizer;
    public Role $role;
    public string $name = '';
    public array $selectedPermissions = [];

    public function mount(int $id): void
    {
        $this->role = Role::with('permissions')->findOrFail($id);
        $this->name = $this->role->name;
        $this->selectedPermissions = $this->role->permissions->pluck('id')->map(fn ($id) => (string) $id)->toArray();
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . $this->role->id,
            'selectedPermissions' => 'array',
        ];
    }

    public function save(): void
    {
        $this->validate();

        // Capture old values for activity log
        $oldName = $this->role->name;
        $oldPermissions = $this->role->permissions->pluck('name')->sort()->values()->toArray();

        $this->role->update([
            'name' => $this->name,
        ]);

        // Sync permissions
        $permissions = Permission::whereIn('id', $this->selectedPermissions)->get();
        $this->role->syncPermissions($permissions);
        $newPermissions = $permissions->pluck('name')->sort()->values()->toArray();

        // Build change details in Spanish
        $cambios = [];
        if ($oldName !== $this->name) {
            $cambios[] = "Nombre: de '{$oldName}' a '{$this->name}'";
        }
        $agregados = array_diff($newPermissions, $oldPermissions);
        $eliminados = array_diff($oldPermissions, $newPermissions);
        if (!empty($agregados)) {
            $cambios[] = 'Permisos agregados: ' . implode(', ', $agregados);
        }
        if (!empty($eliminados)) {
            $cambios[] = 'Permisos eliminados: ' . implode(', ', $eliminados);
        }

        $descripcion = "Se actualizó la información de rol - {$this->name}";
        if (!empty($cambios)) {
            $detalle = implode(' | ', $cambios);
            if (strlen($detalle) > 200) {
                $detalle = substr($detalle, 0, 197) . '...';
            }
            $descripcion .= " - Cambios: {$detalle}";
        }

        // Log activity in Spanish
        activity()
            ->performedOn($this->role)
            ->causedBy(auth()->user())
            ->withProperties([
                'evento' => 'updated',
                'tabla' => 'roles',
                'registro_id' => $this->role->id,
                'identificador_registro' => $this->role->name,
                'usuario_nombre' => auth()->user()->name,
                'usuario_email' => auth()->user()->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old' => ['name' => $oldName, 'permissions' => $oldPermissions],
                'attributes' => ['name' => $this->name, 'permissions' => $newPermissions],
            ])
            ->log($descripcion);

        session()->flash('success', 'Rol actualizado correctamente.');

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

        return view('livewire.admin.roles.edit', [
            'permissionsBySector' => $permissionsBySector,
            'sectors' => $sectors,
            'modules' => $modules,
        ]);
    }
}