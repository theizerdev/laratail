<?php

namespace App\Livewire\Admin\Users;

use App\Models\Empresa;
use App\Models\Group;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\UsernameGeneratorService;
use App\Traits\PermissionOrganizer;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.admin')]
#[Title('Crear Usuario')]
class Create extends Component
{
    use PermissionOrganizer;

    public string $name = '';
    public ?string $username = '';
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

    public function updatedName(): void
    {
        // Only generate username if it's empty or was auto-generated before
        if (empty($this->username) || $this->username === $this->generateCurrentUsername()) {
            $usernameGenerator = app(UsernameGeneratorService::class);
            $this->username = $usernameGenerator->generate($this->name);
        }
    }
    
    private function generateCurrentUsername(): string
    {
        return app(UsernameGeneratorService::class)->generate($this->name);
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
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

        $user = User::create([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'telefono' => $this->telefono ?: null,
            'empresa_id' => $this->empresa_id,
            'sucursal_id' => $this->sucursal_id,
            'group_id' => $this->group_id,
        ]);

        if (!empty($this->selectedRoles)) {
            $roles = Role::whereIn('id', $this->selectedRoles)->get();
            $user->syncRoles($roles);
        }

        session()->flash('success', 'Usuario creado correctamente.');

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

        return view('livewire.admin.users.create', [
            'roles' => $roles,
            'groups' => $groups,
            'empresas' => $empresas,
            'sucursales' => $sucursales,
            'permissionsBySector' => $permissionsBySector,
        ]);
    }
}