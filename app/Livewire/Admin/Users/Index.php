<?php

namespace App\Livewire\Admin\Users;

use App\Models\Empresa;
use App\Models\Group;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.admin')]
#[Title('Usuarios')]
class Index extends Component
{
    public string $search = '';

    // Form fields
    public ?int $editingId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $telefono = '';
    public ?int $empresa_id = null;
    public ?int $sucursal_id = null;
    public ?int $group_id = null;
    public array $selectedRoles = [];

    public function updatedEmpresaId(): void
    {
        $this->sucursal_id = null;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . ($this->editingId ?? 'null'),
            'password' => $this->editingId ? 'nullable|string|min:8' : 'required|string|min:8',
            'telefono' => 'nullable|string|max:20',
            'empresa_id' => 'nullable|exists:empresas,id',
            'sucursal_id' => 'nullable|exists:sucursales,id',
            'group_id' => 'nullable|exists:groups,id',
            'selectedRoles' => 'array',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->dispatch('modal-show', name: 'user-form');
    }

    public function openEditModal(int $id): void
    {
        $user = User::with('roles')->findOrFail($id);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->telefono = $user->telefono ?? '';
        $this->empresa_id = $user->empresa_id;
        $this->sucursal_id = $user->sucursal_id;
        $this->group_id = $user->group_id;
        $this->selectedRoles = $user->roles->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        $this->dispatch('modal-show', name: 'user-form');
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'telefono' => $this->telefono ?: null,
            'empresa_id' => $this->empresa_id,
            'sucursal_id' => $this->sucursal_id,
            'group_id' => $this->group_id,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        $user = User::updateOrCreate(
            ['id' => $this->editingId],
            $data
        );

        // Sync roles
        $roles = Role::whereIn('id', $this->selectedRoles)->get();
        $user->syncRoles($roles);

        $this->dispatch('modal-close', name: 'user-form');
        $this->resetForm();

        session()->flash('success', $this->editingId
            ? 'Usuario actualizado correctamente.'
            : 'Usuario creado correctamente.'
        );
    }

    public function delete(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'No puedes eliminar tu propia cuenta.');

            return;
        }

        $user->delete();

        session()->flash('success', 'Usuario eliminado correctamente.');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->telefono = '';
        $this->empresa_id = null;
        $this->sucursal_id = null;
        $this->group_id = null;
        $this->selectedRoles = [];
        $this->resetValidation();
    }

    public function render()
    {
        $users = User::query()
            ->with(['group', 'roles', 'empresa', 'sucursal'])
            ->withCount('roles')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();

        $roles = Role::orderBy('name')->get();
        $groups = Group::where('is_active', true)->orderBy('name')->get();
        $empresas = Empresa::where('status', true)->orderBy('razon_social')->get();
        $sucursales = $this->empresa_id
            ? Sucursal::where('empresa_id', $this->empresa_id)->where('status', true)->orderBy('nombre')->get()
            : Sucursal::where('status', true)->orderBy('nombre')->get();

        $stats = [
            'total_users' => User::count(),
            'users_with_group' => User::whereNotNull('group_id')->count(),
            'total_roles' => Role::count(),
            'total_groups' => Group::count(),
        ];

        return view('livewire.admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'groups' => $groups,
            'empresas' => $empresas,
            'sucursales' => $sucursales,
            'stats' => $stats,
        ]);
    }
}