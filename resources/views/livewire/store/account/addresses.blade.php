<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Pais;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    public bool $showForm = false;
    public ?int $editingId = null;

    #[Validate('required|min:2|max:100')]
    public string $nombre_destinatario = '';
    #[Validate('nullable|max:30')]
    public string $telefono_destinatario = '';
    #[Validate('required|min:5|max:255')]
    public string $direccion = '';
    #[Validate('required|min:2|max:100')]
    public string $ciudad = '';
    #[Validate('nullable|max:100')]
    public string $estado_region = '';
    #[Validate('nullable|max:20')]
    public string $codigo_postal = '';
    #[Validate('required|integer|exists:paises,id')]
    public int $pais_id = 0;
    #[Validate('nullable|max:50')]
    public string $alias = '';
    #[Validate('nullable|max:20')]
    public string $tipo = 'casa';
    public bool $predeterminada = false;

    public ?string $successMessage = null;

    public function with(): array
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $addresses = $customer
            ? $customer->addresses()->orderBy('predeterminada', 'desc')->orderByDesc('created_at')->get()
            : collect();

        return [
            'addresses' => $addresses,
            'paises' => Pais::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editingId = null;
    }

    public function edit(int $id): void
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $address = $customer->addresses()->findOrFail($id);

        $this->editingId = $id;
        $this->nombre_destinatario = $address->nombre_destinatario;
        $this->telefono_destinatario = $address->telefono_destinatario ?? '';
        $this->direccion = $address->direccion;
        $this->ciudad = $address->ciudad;
        $this->estado_region = $address->estado_region ?? '';
        $this->codigo_postal = $address->codigo_postal ?? '';
        $this->pais_id = $address->pais_id;
        $this->alias = $address->alias ?? '';
        $this->tipo = $address->tipo ?? 'casa';
        $this->predeterminada = $address->predeterminada;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $customer = Customer::where('user_id', Auth::id())->first();
        if (!$customer) {
            $this->addError('form', 'No se encontró tu perfil de cliente.');
            return;
        }

        $data = [
            'customer_id' => $customer->id,
            'tipo' => $this->tipo,
            'alias' => $this->alias ?: null,
            'nombre_destinatario' => $this->nombre_destinatario,
            'telefono_destinatario' => $this->telefono_destinatario ?: null,
            'direccion' => $this->direccion,
            'ciudad' => $this->ciudad,
            'estado_region' => $this->estado_region ?: null,
            'codigo_postal' => $this->codigo_postal ?: null,
            'pais_id' => $this->pais_id,
            'predeterminada' => $this->predeterminada,
        ];

        // If setting as default, unset others
        if ($this->predeterminada) {
            $customer->addresses()->update(['predeterminada' => false]);
        }

        if ($this->editingId) {
            $address = $customer->addresses()->findOrFail($this->editingId);
            $address->update($data);
            $this->successMessage = 'Dirección actualizada correctamente.';
        } else {
            $customer->addresses()->create($data);
            $this->successMessage = 'Dirección agregada correctamente.';
        }

        $this->showForm = false;
        $this->resetForm();
        $this->js('setTimeout(() => { $wire.successMessage = null }, 4000)');
    }

    public function delete(int $id): void
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $customer->addresses()->where('id', $id)->delete();
        $this->successMessage = 'Dirección eliminada.';
        $this->js('setTimeout(() => { $wire.successMessage = null }, 4000)');
    }

    public function setDefault(int $id): void
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $customer->addresses()->update(['predeterminada' => false]);
        $customer->addresses()->where('id', $id)->update(['predeterminada' => true]);
        $this->successMessage = 'Dirección predeterminada actualizada.';
        $this->js('setTimeout(() => { $wire.successMessage = null }, 4000)');
    }

    protected function resetForm(): void
    {
        $this->reset([
            'nombre_destinatario', 'telefono_destinatario', 'direccion', 'ciudad',
            'estado_region', 'codigo_postal', 'pais_id', 'alias', 'tipo', 'predeterminada',
            'editingId',
        ]);
    }
};
?>

<div>
    <!-- Breadcrumb -->
    <div class="bg-white border-b border-zinc-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center gap-2 text-sm">
                <a href="/" wire:navigate class="text-zinc-500 hover:text-zinc-900 transition-colors">Inicio</a>
                <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <a href="/mi-cuenta" wire:navigate class="text-zinc-500 hover:text-zinc-900 transition-colors">Mi Cuenta</a>
                <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="text-zinc-900 font-medium">Direcciones</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            @include('livewire.store.account.partials.sidebar')

            <main class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-zinc-900">Mis Direcciones</h1>
                    @if(!$showForm)
                        <button wire:click="openCreate" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl font-semibold text-sm hover:bg-indigo-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Nueva Dirección
                        </button>
                    @endif
                </div>

                @if($successMessage)
                    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
                        {{ $successMessage }}
                    </div>
                @endif

                <!-- Address Form -->
                @if($showForm)
                <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 sm:p-8 mb-6">
                    <h2 class="text-lg font-bold text-zinc-900 mb-6">{{ $editingId ? 'Editar Dirección' : 'Nueva Dirección' }}</h2>
                    <form wire:submit="save" class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1">Nombre del destinatario *</label>
                                <input type="text" wire:model="nombre_destinatario" class="w-full rounded-xl border-zinc-300 focus:ring-indigo-500 focus:border-indigo-500">
                                @error('nombre_destinatario') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1">Teléfono</label>
                                <input type="tel" wire:model="telefono_destinatario" class="w-full rounded-xl border-zinc-300 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Dirección *</label>
                            <input type="text" wire:model="direccion" class="w-full rounded-xl border-zinc-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Calle, número, apt...">
                            @error('direccion') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1">Ciudad *</label>
                                <input type="text" wire:model="ciudad" class="w-full rounded-xl border-zinc-300 focus:ring-indigo-500 focus:border-indigo-500">
                                @error('ciudad') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1">Estado / Región</label>
                                <input type="text" wire:model="estado_region" class="w-full rounded-xl border-zinc-300 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1">Código Postal</label>
                                <input type="text" wire:model="codigo_postal" class="w-full rounded-xl border-zinc-300 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1">País *</label>
                                <select wire:model="pais_id" class="w-full rounded-xl border-zinc-300 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="0">Seleccionar...</option>
                                    @foreach($paises as $pais)
                                        <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('pais_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1">Tipo</label>
                                <select wire:model="tipo" class="w-full rounded-xl border-zinc-300 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="casa">Casa</option>
                                    <option value="oficina">Oficina</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1">Alias (opcional)</label>
                                <input type="text" wire:model="alias" class="w-full rounded-xl border-zinc-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Mi casa, Trabajo...">
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="predeterminada" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-zinc-600">Establecer como dirección predeterminada</span>
                        </label>
                        <div class="flex gap-3">
                            <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-indigo-700 transition-colors">
                                {{ $editingId ? 'Actualizar' : 'Guardar' }}
                            </button>
                            <button type="button" wire:click="$set('showForm', false)" class="px-6 py-3 border border-zinc-300 rounded-xl font-medium text-zinc-700 hover:bg-zinc-50 transition-colors">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
                @endif

                <!-- Address List -->
                @if($addresses->isEmpty() && !$showForm)
                    <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-zinc-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <h3 class="text-lg font-semibold text-zinc-700 mb-2">Sin direcciones guardadas</h3>
                        <p class="text-zinc-500 mb-6">Agrega una dirección para agilizar tus compras.</p>
                        <button wire:click="openCreate" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors">
                            Agregar Dirección
                        </button>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($addresses as $addr)
                        <div class="bg-white rounded-2xl border shadow-sm p-5 {{ $addr->predeterminada ? 'border-indigo-200 ring-1 ring-indigo-100' : 'border-zinc-100' }}">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <p class="font-semibold text-zinc-900">{{ $addr->nombre_destinatario }}</p>
                                    @if($addr->alias)
                                        <span class="text-xs text-zinc-500">{{ ucfirst($addr->alias) }}</span>
                                    @else
                                        <span class="text-xs text-zinc-500">{{ ucfirst($addr->tipo ?? 'casa') }}</span>
                                    @endif
                                </div>
                                @if($addr->predeterminada)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">Predeterminada</span>
                                @endif
                            </div>
                            <p class="text-sm text-zinc-600 mb-4">{{ $addr->direccion_completa }}</p>
                            @if($addr->telefono_destinatario)
                                <p class="text-xs text-zinc-500 mb-3">Tel: {{ $addr->telefono_destinatario }}</p>
                            @endif
                            <div class="flex items-center gap-2 pt-3 border-t border-zinc-100">
                                <button wire:click="edit({{ $addr->id }})" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors">Editar</button>
                                @if(!$addr->predeterminada)
                                    <span class="text-zinc-300">·</span>
                                    <button wire:click="setDefault({{ $addr->id }})" class="text-xs text-zinc-500 hover:text-zinc-700 font-medium transition-colors">Predeterminar</button>
                                    <span class="text-zinc-300">·</span>
                                    <button wire:click="delete({{ $addr->id }})" wire:confirm="¿Eliminar esta dirección?" class="text-xs text-red-500 hover:text-red-700 font-medium transition-colors">Eliminar</button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </main>
        </div>
    </div>
</div>
