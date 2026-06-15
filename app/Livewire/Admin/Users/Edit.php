<?php

namespace App\Livewire\Admin\Users;

use App\Models\Empresa;
use App\Models\Group;
use App\Models\Sucursal;
use App\Models\User;
use App\Traits\PermissionOrganizer;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.admin')]
#[Title('Editar Usuario')]
class Edit extends Component
{
    use PermissionOrganizer;

    public User $user;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $telefono = '';
    public ?int $empresa_id = null;
    public ?int $sucursal_id = null;
    public ?int $group_id = null;
    public array $selectedRoles = [];

    public function mount(int $id): void
    {
        $this->user = User::with('roles')->findOrFail($id);
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->telefono = $this->user->telefono ?? '';
        $this->empresa_id = $this->user->empresa_id;
        $this->sucursal_id = $this->user->sucursal_id;
        $this->group_id = $this->user->group_id;
        $this->selectedRoles = $this->user->roles->pluck('id')->map(fn ($id) => (string) $id)->toArray();
    }

    public function updatedEmpresaId(): void
    {
        $this->sucursal_id = null;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $this->user->id,
            'password' => 'nullable|string|min:8',
            'telefono' => 'nullable|string|max:20',
            'empresa_id' => 'nullable|exists:empresas,id',
            'sucursal_id' => 'nullable|exists:sucursales,id',
            'group_id' => 'nullable|exists:groups,id',
            'selectedRoles' => 'array',
        ];
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

        $this->user->update($data);

        $roles = Role::whereIn('id', $this->selectedRoles)->get();
        $this->user->syncRoles($roles);

        session()->flash('success', 'Usuario actualizado correctamente.');

        $this->redirect(route('admin.users'), navigate: true);
    }

    public function render()
    {
        $roles = Role::orderBy('name')->get();
        $groups = Group::where('is_active', true)->orderBy('name')->get();
        $empresas = Empresa::where('status', true)->orderBy('razon_social')->get();
        $sucursales = $this->empresa_id
            ? Sucursal::where('empresa_id', $this->empresa_id)->where('status', true)->orderBy('nombre')->get()
            : collect();
        $permissionsBySector = $this->getPermissionsBySector();

        return view('livewire.admin.users.edit', [
            'roles' => $roles,
            'groups' => $groups,
            'empresas' => $empresas,
            'sucursales' => $sucursales,
            'permissionsBySector' => $permissionsBySector,
        ]);
    }
}