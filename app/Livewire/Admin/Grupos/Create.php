<?php

namespace App\Livewire\Admin\Grupos;

use App\Models\Group;
use App\Traits\PermissionOrganizer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.admin')]
#[Title('Crear Grupo')]
class Create extends Component
{
    use PermissionOrganizer;

    public string $name = '';
    public string $description = '';
    public bool $is_active = true;
    public array $selectedRoles = [];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:groups,name',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'selectedRoles' => 'array',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $group = Group::create([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'is_active' => $this->is_active,
        ]);

        if (!empty($this->selectedRoles)) {
            $roles = Role::whereIn('id', $this->selectedRoles)->get();
            $group->syncRoles($roles);
        }

        session()->flash('success', 'Grupo creado correctamente.');

        $this->redirect(route('admin.grupos'), navigate: true);
    }

    public function render()
    {
        $roles = Role::orderBy('name')->get();
        $permissionsBySector = $this->getPermissionsBySector();

        return view('livewire.admin.grupos.create', [
            'roles' => $roles,
            'permissionsBySector' => $permissionsBySector,
        ]);
    }
}