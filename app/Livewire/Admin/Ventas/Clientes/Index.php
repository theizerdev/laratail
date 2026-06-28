<?php

namespace App\Livewire\Admin\Ventas\Clientes;

use App\Models\Customer;
use App\Models\Pais;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Clientes')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filter = 'all';
    public string $fuente = '';

    // Modal state
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    // Form fields
    public string $nombre = '';
    public string $apellido = '';
    public string $email = '';
    public string $telefono = '';
    public string $tipo_documento = '';
    public string $documento = '';
    public string $direccion = '';
    public string $ciudad = '';
    public string $estado_region = '';
    public string $codigo_postal = '';
    public ?int $pais_id = null;
    public string $empresa_nombre = '';
    public string $whatsapp = '';
    public string $fuente_form = '';
    public string $notas = '';
    public ?string $fecha_nacimiento = null;
    public bool $activo = true;

    // Auto-create user
    public bool $crear_usuario = false;
    public bool $enviar_whatsapp = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $customer = Customer::findOrFail($id);
        $this->editingId = $customer->id;
        $this->nombre = $customer->nombre;
        $this->apellido = $customer->apellido ?? '';
        $this->email = $customer->email ?? '';
        $this->telefono = $customer->telefono ?? '';
        $this->tipo_documento = $customer->tipo_documento ?? '';
        $this->documento = $customer->documento ?? '';
        $this->direccion = $customer->direccion ?? '';
        $this->ciudad = $customer->ciudad ?? '';
        $this->estado_region = $customer->estado_region ?? '';
        $this->codigo_postal = $customer->codigo_postal ?? '';
        $this->pais_id = $customer->pais_id;
        $this->empresa_nombre = $customer->empresa_nombre ?? '';
        $this->whatsapp = $customer->whatsapp ?? '';
        $this->fuente_form = $customer->fuente ?? '';
        $this->notas = $customer->notas ?? '';
        $this->fecha_nacimiento = $customer->fecha_nacimiento?->format('Y-m-d');
        $this->activo = $customer->activo;
        $this->crear_usuario = false;
        $this->enviar_whatsapp = false;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->nombre = '';
        $this->apellido = '';
        $this->email = '';
        $this->telefono = '';
        $this->tipo_documento = '';
        $this->documento = '';
        $this->direccion = '';
        $this->ciudad = '';
        $this->estado_region = '';
        $this->codigo_postal = '';
        $this->pais_id = null;
        $this->empresa_nombre = '';
        $this->whatsapp = '';
        $this->fuente_form = '';
        $this->notas = '';
        $this->fecha_nacimiento = null;
        $this->activo = true;
        $this->crear_usuario = false;
        $this->enviar_whatsapp = false;
        $this->editingId = null;
    }

    public function save(): void
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'tipo_documento' => 'nullable|string|max:20',
            'documento' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:500',
            'ciudad' => 'nullable|string|max:100',
            'estado_region' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:20',
            'pais_id' => 'nullable|exists:pais,id',
            'empresa_nombre' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'fuente_form' => 'nullable|string|max:50',
            'notas' => 'nullable|string|max:2000',
            'fecha_nacimiento' => 'nullable|date',
            'activo' => 'boolean',
        ];

        // Email unique validation
        if ($this->editingId) {
            $rules['email'] = 'nullable|email|max:255|unique:customers,email,' . $this->editingId;
        } else {
            $rules['email'] = 'nullable|email|max:255|unique:customers,email';
        }

        // If creating user, email is required
        if ($this->crear_usuario && !$this->editingId) {
            $rules['email'] = 'required|email|max:255|unique:users,email|unique:customers,email';
        }

        $this->validate($rules);

        $data = [
            'nombre' => $this->nombre,
            'apellido' => $this->apellido ?: null,
            'email' => $this->email ?: null,
            'telefono' => $this->telefono ?: null,
            'tipo_documento' => $this->tipo_documento ?: null,
            'documento' => $this->documento ?: null,
            'direccion' => $this->direccion ?: null,
            'ciudad' => $this->ciudad ?: null,
            'estado_region' => $this->estado_region ?: null,
            'codigo_postal' => $this->codigo_postal ?: null,
            'pais_id' => $this->pais_id,
            'empresa_nombre' => $this->empresa_nombre ?: null,
            'whatsapp' => $this->whatsapp ?: null,
            'fuente' => $this->fuente_form ?: null,
            'notas' => $this->notas ?: null,
            'fecha_nacimiento' => $this->fecha_nacimiento ?: null,
            'activo' => $this->activo,
        ];

        DB::beginTransaction();

        try {
            if ($this->editingId) {
                $customer = Customer::findOrFail($this->editingId);
                $customer->update($data);
                session()->flash('success', 'Cliente actualizado correctamente.');
            } else {
                // Create user if requested
                if ($this->crear_usuario && $this->email) {
                    $password = Str::random(10);

                    $group = \App\Models\Group::where('name', 'Clientes')->first();
                    $groupId = $group ? $group->id : null;

                    $user = User::create([
                        'name' => trim($this->nombre . ' ' . $this->apellido),
                        'email' => $this->email,
                        'password' => Hash::make($password),
                        'telefono' => $this->telefono ?: null,
                        'empresa_id' => auth()->user()->empresa_id,
                        'sucursal_id' => auth()->user()->sucursal_id,
                        'group_id' => $groupId,
                    ]);

                    // Assign client role if exists
                    $user->assignRole('cliente');

                    $data['user_id'] = $user->id;
                }

                $customer = Customer::create($data);

                // Send WhatsApp message with credentials
                if ($this->crear_usuario && $this->enviar_whatsapp && $this->whatsapp && isset($password)) {
                    $this->enviarCredencialesWhatsApp($customer, $this->email, $password);
                }

                session()->flash('success', 'Cliente creado correctamente.' .
                    ($this->crear_usuario ? ' Usuario creado y credenciales enviadas.' : ''));
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar cliente: ' . $e->getMessage());
            session()->flash('error', 'Error al guardar el cliente: ' . $e->getMessage());
        }

        $this->closeModal();
    }

    /**
     * Enviar credenciales por WhatsApp
     */
    protected function enviarCredencialesWhatsApp(Customer $customer, string $email, string $password): void
    {
        try {
            $whatsappService = new WhatsAppService();

            if (!$whatsappService->isConfigured()) {
                Log::warning('WhatsApp no está configurado para enviar credenciales');
                return;
            }

            $appName = config('app.name', 'LaraTail');
            $loginUrl = url('/login');

            $message = "¡Bienvenido a *{$appName}*! 🎉\n\n";
            $message .= "Tu cuenta ha sido creada:\n";
            $message .= "📧 *Email:* {$email}\n";
            $message .= "🔑 *Contraseña:* {$password}\n\n";
            $message .= "Puedes acceder en: {$loginUrl}\n\n";
            $message .= "⚠️ Te recomendamos cambiar tu contraseña después de iniciar sesión.";

            $phone = preg_replace('/[^0-9]/', '', $customer->whatsapp);
            $whatsappService->sendMessage($phone, $message);

            Log::info('Credenciales enviadas por WhatsApp', [
                'customer_id' => $customer->id,
                'whatsapp' => $phone,
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando credenciales por WhatsApp: ' . $e->getMessage());
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $customer = Customer::findOrFail($this->deletingId);
        $customer->delete();

        $this->showDeleteModal = false;
        $this->deletingId = null;
        session()->flash('success', 'Cliente eliminado correctamente.');
    }

    public function toggleStatus(int $id): void
    {
        $customer = Customer::findOrFail($id);
        $customer->update(['activo' => !$customer->activo]);
    }

    public function render()
    {
        $query = Customer::with(['user', 'pais'])
            ->withCount('orders')
            ->orderBy('nombre');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                    ->orWhere('apellido', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('telefono', 'like', "%{$this->search}%")
                    ->orWhere('documento', 'like', "%{$this->search}%");
            });
        }

        if ($this->filter === 'active') {
            $query->where('activo', true);
        } elseif ($this->filter === 'inactive') {
            $query->where('activo', false);
        } elseif ($this->filter === 'with_user') {
            $query->whereNotNull('user_id');
        } elseif ($this->filter === 'without_user') {
            $query->whereNull('user_id');
        }

        if ($this->fuente) {
            $query->where('fuente', $this->fuente);
        }

        $customers = $query->paginate(15);

        $stats = [
            'total' => Customer::count(),
            'active' => Customer::where('activo', true)->count(),
            'inactive' => Customer::where('activo', false)->count(),
            'with_user' => Customer::whereNotNull('user_id')->count(),
            'total_orders' => \App\Models\Order::where('tipo', 'venta')->count(),
        ];

        $paises = Pais::orderBy('nombre')->get();

        return view('livewire.admin.ventas.clientes.index', [
            'customers' => $customers,
            'stats' => $stats,
            'paises' => $paises,
        ]);
    }
}
