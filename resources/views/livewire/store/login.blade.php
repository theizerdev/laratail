<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] #[Title('Iniciar Sesión - Laratail Store')] class extends Component {
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', 'Las credenciales proporcionadas no son válidas.');
            return;
        }

        session()->regenerate();

        $intended = session('url.intended', '/mi-cuenta');
        $this->redirect($intended, navigate: true);
    }
};
?>

<div class="min-h-[60vh] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-zinc-900">Iniciar Sesión</h1>
            <p class="mt-2 text-zinc-500">Bienvenido de vuelta. Accede a tu cuenta.</p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-8">
            <form wire:submit="login" class="space-y-5">
                <flux:input
                    wire:model="email"
                    label="Correo electrónico"
                    type="email"
                    placeholder="tu@email.com"
                    autofocus
                />

                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <flux:label>Contraseña</flux:label>
                        <a href="#" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                    <flux:input
                        wire:model="password"
                        type="password"
                        placeholder="••••••••"
                    />
                </div>

                <flux:checkbox wire:model="remember" label="Recordarme" />

                <flux:button type="submit" variant="primary" class="w-full">
                    Iniciar Sesión
                </flux:button>
            </form>
        </div>

        <!-- Register Link -->
        <p class="mt-6 text-center text-sm text-zinc-500">
            ¿No tienes cuenta?
            <a href="/registro" wire:navigate class="text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">Regístrate aquí</a>
        </p>
    </div>
</div>
