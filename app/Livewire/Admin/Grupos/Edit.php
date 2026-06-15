<?php

namespace App\Livewire\Admin\Grupos;

use App\Models\Group;
use App\Traits\PermissionOrganizer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.admin')]
#[Title('Editar Grupo')]
class Edit extends Component
{
    use PermissionOrganizer;

    public Group $group;
    public string $name = '';
    public string $description = '';
    public bool $is_active = true;
    public array $selectedRoles = [];

    public function mount(int $id): void
    {
        $this->group = Group::with('roles')->findOrFail($id);
        $this->name = $this->group->name;
        $this->description = $this->group->description ?? '';
        $this->is_active = $this->group->is_active;
        $this->selectedRoles = $this->group->roles->pluck('id')->map(fn ($id) => (string) $id)->toArray();
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:groups,name,' . $this->group->id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'selectedRoles' => 'array',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->group->update([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'is_active' => $this->is_active,
        ]);

        $roles = Role::whereIn('id', $this->selectedRoles)->get();
        $this->group->syncRoles($roles);

        session()->flash('success', 'Grupo actualizado correctamente.');

        $this->redirect(route('admin.grupos'), navigate: true);
    }

    public function render()
    {
        $roles = Role::orderBy('name')->get();
        $permissionsBySector = $this->getPermissionsBySector();

        return view('livewire.admin.grupos.edit', [
            'roles' => $roles,
            'permissionsBySector' => $permissionsBySector,
        ]);
    }
}