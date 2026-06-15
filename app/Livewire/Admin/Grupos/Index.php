<?php

namespace App\Livewire\Admin\Grupos;

use App\Models\Group;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.admin')]
#[Title('Grupos')]
class Index extends Component
{
    public string $search = '';
    public string $filter = 'all'; // all, active, inactive

    // Form fields
    public ?int $editingId = null;
    public string $name = '';
    public string $description = '';
    public bool $is_active = true;
    public array $selectedRoles = [];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:groups,name,' . ($this->editingId ?? 'null'),
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'selectedRoles' => 'array',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->dispatch('modal-show', name: 'group-form');
    }

    public function openEditModal(int $id): void
    {
        $group = Group::findOrFail($id);

        $this->editingId = $group->id;
        $this->name = $group->name;
        $this->description = $group->description ?? '';
        $this->is_active = $group->is_active;
        $this->selectedRoles = $group->roles->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        $this->dispatch('modal-show', name: 'group-form');
    }

    public function save(): void
    {
        $this->validate();

        $group = Group::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'description' => $this->description ?: null,
                'is_active' => $this->is_active,
            ]
        );

        // Sync roles
        $roles = Role::whereIn('id', $this->selectedRoles)->get();
        $group->syncRoles($roles);

        $this->dispatch('modal-close', name: 'group-form');
        $this->resetForm();

        session()->flash('success', $this->editingId
            ? 'Grupo actualizado correctamente.'
            : 'Grupo creado correctamente.'
        );
    }

    public function delete(int $id): void
    {
        $group = Group::findOrFail($id);

        // Prevent deleting groups with users
        if ($group->users()->count() > 0) {
            session()->flash('error', 'No se puede eliminar un grupo con usuarios asignados.');

            return;
        }

        $group->syncRoles([]);
        $group->delete();

        session()->flash('success', 'Grupo eliminado correctamente.');
    }

    public function toggleStatus(int $id): void
    {
        $group = Group::findOrFail($id);
        $group->update(['is_active' => ! $group->is_active]);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->is_active = true;
        $this->selectedRoles = [];
        $this->resetValidation();
    }

    public function render()
    {
        $groups = Group::query()
            ->with(['roles', 'users' => fn ($q) => $q->limit(5)])
            ->withCount('users')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            })
            ->when($this->filter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->filter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->get();

        $roles = Role::orderBy('name')->get();

        // Stats
        $stats = [
            'total_groups' => Group::count(),
            'active_groups' => Group::where('is_active', true)->count(),
            'total_users' => \App\Models\User::whereNotNull('group_id')->count(),
            'total_roles' => Role::count(),
        ];

        return view('livewire.admin.grupos.index', [
            'groups' => $groups,
            'roles' => $roles,
            'stats' => $stats,
        ]);
    }
}
