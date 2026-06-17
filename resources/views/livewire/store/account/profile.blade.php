<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

new #[Layout('layouts.app')] class extends Component {
    #[Validate('required|min:2|max:100')]
    public string $nombre = '';

    #[Validate('required|min:2|max:100')]
    public string $apellido = '';

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('nullable|min:6|max:30')]
    public string $telefono = '';

    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public ?string $successMessage = null;

    public function mount(): void
    {
        $user = Auth::user();
        $customer = Customer::where('user_id', $user->id)->first();

        if ($customer) {
            $this->nombre = $customer->nombre;
            $this->apellido = $customer->apellido ?? '';
            $this->email = $customer->email;
            $this->telefono = $customer->telefono ?? '';
        } else {
            $this->nombre = $user->name;
            $this->email = $user->email;
            $this->telefono = $user->telefono ?? '';
        }
    }

    public function updateProfile(): void
    {
        $this->validate([
            'nombre' => 'required|min:2|max:100',
            'apellido' => 'required|min:2|max:100',
            'email' => 'required|email',
            'telefono' => 'nullable|min:6|max:30',
        ]);

        $user = Auth::user();
        $user->name = $this->nombre . ' ' . $this->apellido;
        $user->email = $this->email;
        $user->telefono = $this->telefono ?: null;
        $user->save();

        $customer = Customer::where('user_id', $user->id)->first();
        if ($customer) {
            $customer->nombre = $this->nombre;
            $customer->apellido = $this->apellido;
            $customer->email = $this->email;
            $customer->telefono = $this->telefono ?: null;
            $customer->save();
        }

        $this->successMessage = 'Perfil actualizado correctamente.';
        $this->js('setTimeout(() => { $wire.successMessage = null }, 4000)');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|same:new_password_confirmation',
        ]);

        $user = Auth::user();

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'La contraseña actual no es correcta.');
            return;
        }

        $user->password = Hash::make($this->new_password);
        $user->save();

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->successMessage = 'Contraseña actualizada correctamente.';
        $this->js('setTimeout(() => { $wire.successMessage = null }, 4000)');
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
                <span class="text-zinc-900 font-medium">Mi Cuenta</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar -->
            @include('livewire.store.account.partials.sidebar')

            <!-- Main Content -->
            <main class="flex-1 min-w-0">
                @if($successMessage)
                    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
                        {{ $successMessage }}
                    </div>
                @endif

                <!-- Personal Info -->
                <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 sm:p-8 mb-6">
                    <h2 class="text-xl font-bold text-zinc-900 mb-6">Información Personal</h2>
                    <form wire:submit="updateProfile" class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1">Nombre</label>
                                <input type="text" wire:model="nombre" class="w-full rounded-xl border-zinc-300 focus:ring-indigo-500 focus:border-indigo-500">
                                @error('nombre') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1">Apellido</label>
                                <input type="text" wire:model="apellido" class="w-full rounded-xl border-zinc-300 focus:ring-indigo-500 focus:border-indigo-500">
                                @error('apellido') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Correo electrónico</label>
                            <input type="email" wire:model="email" class="w-full rounded-xl border-zinc-300 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Teléfono</label>
                            <input type="tel" wire:model="telefono" class="w-full rounded-xl border-zinc-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="+1 234 567 8900">
                            @error('telefono') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-indigo-700 transition-colors">
                            Guardar Cambios
                        </button>
                    </form>
                </div>

                <!-- Password Change -->
                <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 sm:p-8">
                    <h2 class="text-xl font-bold text-zinc-900 mb-6">Cambiar Contraseña</h2>
                    <form wire:submit="updatePassword" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Contraseña actual</label>
                            <input type="password" wire:model="current_password" class="w-full rounded-xl border-zinc-300 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('current_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Nueva contraseña</label>
                            <input type="password" wire:model="new_password" class="w-full rounded-xl border-zinc-300 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('new_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Confirmar nueva contraseña</label>
                            <input type="password" wire:model="new_password_confirmation" class="w-full rounded-xl border-zinc-300 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <button type="submit" class="bg-zinc-900 text-white px-8 py-3 rounded-xl font-semibold hover:bg-black transition-colors">
                            Actualizar Contraseña
                        </button>
                    </form>
                </div>
            </main>
        </div>
    </div>
</div>
