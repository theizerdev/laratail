<div>
    @if($show2FA)
        {{-- Heading --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Código de Seguridad</h1>
            <p class="text-xs text-zinc-500 mt-1">Ingresa el código de 6 dígitos de tu aplicación autenticadora o un código de recuperación.</p>
        </div>

        {{-- 2FA Verification Form --}}
        <form wire:submit.prevent="verify2FA" class="space-y-5">
            {{-- 2FA Code --}}
            <flux:input
                wire:model="twoFactorCode"
                label="Código de Autenticación"
                placeholder="000000 o código de recuperación"
                autofocus
                class="text-center tracking-widest font-mono text-base"
            />

            {{-- Submit --}}
            <flux:button type="submit" variant="primary" class="w-full !bg-indigo-600 hover:!bg-indigo-700">
                Verificar Código
            </flux:button>

            {{-- Cancel / Back link --}}
            <button
                type="button"
                wire:click="cancel2FA"
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 shadow-xs transition hover:bg-zinc-50"
            >
                <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon>
                Volver al inicio
            </button>
        </form>
    @else
        {{-- Heading --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Welcome back</h1>
        </div>

        {{-- Social Login Buttons --}}
        <div class="space-y-3">
            <button
                type="button"
                class="flex w-full items-center justify-center gap-3 rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 shadow-xs transition hover:bg-zinc-50"
            >
                {{-- Google Icon --}}
                <iconify-icon icon="logos:google-icon" class="h-5 w-5"></iconify-icon>
                Continue with Google
            </button>

            <button
                type="button"
                class="flex w-full items-center justify-center gap-3 rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 shadow-xs transition hover:bg-zinc-50"
            >
                {{-- GitHub Icon --}}
                <iconify-icon icon="mdi:github" class="h-5 w-5"></iconify-icon>
                Continue with GitHub
            </button>
        </div>

        {{-- Divider --}}
        <div class="my-6 flex items-center gap-4">
            <div class="h-px flex-1 bg-zinc-200"></div>
            <span class="text-xs font-medium text-zinc-400 uppercase">or</span>
            <div class="h-px flex-1 bg-zinc-200"></div>
        </div>

        {{-- Login Form --}}
        <form wire:submit="login" class="space-y-5">
            {{-- Email --}}
            <flux:input
                wire:model="email"
                label="Email"
                type="email"
                placeholder="email@example.com"
            />

            {{-- Password --}}
            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <flux:label>Password</flux:label>
                    <a
                        href="{{ route('password.request') }}"
                        class="text-sm text-zinc-500 hover:text-zinc-700 transition"
                        wire:navigate
                    >
                        Forgot password?
                    </a>
                </div>
                <flux:input
                    wire:model="password"
                    type="password"
                    placeholder="Your password"
                />
            </div>

            {{-- Remember me --}}
            <flux:checkbox wire:model="remember" label="Remember me for 30 days" />

            {{-- Submit --}}
            <flux:button type="submit" variant="primary" class="w-full">
                Log in
            </flux:button>

            {{-- Sign up link --}}
            <p class="text-center text-sm text-zinc-500">
                First time around here?
                <a
                    href="#"
                    class="font-medium text-zinc-900 underline underline-offset-2 hover:text-zinc-700 transition"
                >
                    Sign up for free
                </a>
            </p>
        </form>
    @endif
</div>
