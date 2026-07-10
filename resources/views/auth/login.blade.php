<x-layouts.auth>
    @section('title', 'Iniciar Sesión')

    {{-- Heading --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Iniciar Sesión</h1>
        <p class="mt-2 text-sm text-zinc-500">
            Inicia sesión en tu cuenta para continuar.
    </div>

  

    {{-- Divider --}}
    <div class="my-6 flex items-center gap-4">
        <div class="h-px flex-1 bg-zinc-200"></div>
        <span class="text-xs font-medium text-zinc-400 uppercase">or</span>
        <div class="h-px flex-1 bg-zinc-200"></div>
    </div>

    {{-- Login Form --}}
    <div x-data="{
        loading: false,
        errors: {},
        submit() {
            this.loading = true;
            this.errors = {};
            let formData = new FormData(this.$refs.form);

            fetch('{{ route('login') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 422) {
                        return response.json().then(data => {
                            this.errors = data.errors;
                        });
                    }
                    throw new Error('Network response was not ok.');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.redirect_url) {
                    window.location.href = data.redirect_url;
                }
            })
            .catch(error => {
                console.error('There has been a problem with your fetch operation:', error);
                this.errors = { general: ['Ocurrió un error inesperado. Por favor, inténtalo de nuevo.'] };
            })
            .finally(() => {
                this.loading = false;
            });
        }
    }">
        <form x-ref="form" @submit.prevent="submit()" class="space-y-5">
            @csrf

            {{-- General Error Message --}}
            <template x-if="errors.general">
                <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <span x-text="errors.general[0]"></span>
                </div>
            </template>

            {{-- Email or Username --}}
            <div>
                <flux:input
                    name="login"
                    label="Correo electrónico o Nombre de Usuario"
                    type="text"
                    placeholder="email@example.com o usuario"
                    autofocus
                    :value="old('login')"
                />
                <template x-if="errors.login">
                    <p class="mt-1 text-sm text-red-600" x-text="errors.login[0]"></p>
                </template>
            </div>

            {{-- Password --}}
            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <flux:label for="password">Contraseña</flux:label>
                    <a
                        href="{{ route('password.request') }}"
                        class="text-sm text-zinc-500 hover:text-zinc-700 transition"
                    >
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
                <flux:input
                    name="password"
                    id="password"
                    type="password"
                    placeholder="Tu contraseña"
                />
                <template x-if="errors.password">
                    <p class="mt-1 text-sm text-red-600" x-text="errors.password[0]"></p>
                </template>
            </div>

            {{-- Remember me --}}
            <flux:checkbox name="remember" id="remember" label="Recordar sesión por 30 días" />

            {{-- Submit --}}
            <flux:button type="submit" variant="primary" class="w-full" ::disabled="loading">
                <span x-show="!loading">Iniciar Sesión</span>
                <span x-show="loading" class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                   
                </span>
            </flux:button>
        </form>
    </div>
</x-layouts.auth>