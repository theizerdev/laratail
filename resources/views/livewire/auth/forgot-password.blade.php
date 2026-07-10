<div>
    {{-- ── Back to login ──────────────────────────────────────────────────────── --}}
    <div class="mb-8">
        <a
            href="{{ route('login') }}"
            class="inline-flex items-center gap-1.5 text-sm text-zinc-500 hover:text-zinc-700 transition"
            wire:navigate
        >
            <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon>
            Volver al login
        </a>
    </div>

    {{-- ── Stepper visual ──────────────────────────────────────────────────────── --}}
    <div class="mb-8 flex items-center gap-0">
        @foreach ([['phone','1','Teléfono'], ['verify','2','Verificar'], ['reset','3','Contraseña']] as [$s, $n, $label])
            @php
                $steps = ['phone', 'verify', 'reset'];
                $currentIdx = array_search($step, $steps);
                $thisIdx = array_search($s, $steps);
                $isDone = $currentIdx > $thisIdx;
                $isActive = $step === $s;
            @endphp
            <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
                <div class="flex flex-col items-center">
                    <div @class([
                        'flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition-all duration-300',
                        'bg-indigo-600 text-white shadow-md shadow-indigo-200' => $isActive,
                        'bg-emerald-500 text-white' => $isDone,
                        'bg-zinc-100 text-zinc-400' => !$isActive && !$isDone,
                    ])>
                        @if($isDone)
                            <iconify-icon icon="heroicons:check" class="h-4 w-4"></iconify-icon>
                        @else
                            {{ $n }}
                        @endif
                    </div>
                    <span @class([
                        'mt-1 text-[10px] font-medium whitespace-nowrap',
                        'text-indigo-600' => $isActive,
                        'text-emerald-600' => $isDone,
                        'text-zinc-400' => !$isActive && !$isDone,
                    ])>{{ $label }}</span>
                </div>
                @if(!$loop->last)
                    <div @class([
                        'flex-1 h-px mx-2 mb-4 transition-all duration-500',
                        'bg-emerald-400' => $isDone,
                        'bg-zinc-200' => !$isDone,
                    ])></div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- ── Mensajes globales ─────────────────────────────────────────────────── --}}
    @if ($errorMessage)
        <div class="mb-5 flex items-start gap-2 rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-sm text-red-700">
            <iconify-icon icon="heroicons:exclamation-circle" class="mt-0.5 h-4 w-4 shrink-0"></iconify-icon>
            {{ $errorMessage }}
        </div>
    @endif

    @if ($successMessage && $step !== 'reset')
        <div class="mb-5 flex items-start gap-2 rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-700">
            <iconify-icon icon="heroicons:check-circle" class="mt-0.5 h-4 w-4 shrink-0"></iconify-icon>
            {{ $successMessage }}
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════════
         PASO 1: Número de teléfono
    ══════════════════════════════════════════════════════════════════════════ --}}
    @if($step === 'phone')
        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">¿Olvidaste tu contraseña?</h1>
            <p class="mt-2 text-sm text-zinc-500">
                Ingresa tu número de WhatsApp y te enviaremos un código de verificación para restablecer tu contraseña.
            </p>
        </div>

        <form wire:submit.prevent="sendOtp" class="space-y-5">
            {{-- Selector de país + número --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-zinc-700">Número de WhatsApp</label>
                <div class="flex gap-2">
                    {{-- Select de país --}}
                    <div class="relative w-36 shrink-0">
                        <select
                            wire:model.live="countryIso"
                            class="w-full appearance-none rounded-lg border border-zinc-200 bg-white px-3 py-2 pr-8 text-sm text-zinc-700 shadow-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                            @foreach($countries as $country)
                                <option value="{{ $country['codigo_iso2'] }}">
                                    {{ $country['codigo_telefonico'] }} {{ $country['codigo_iso2'] }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-400">
                            <iconify-icon icon="heroicons:chevron-down" class="h-4 w-4"></iconify-icon>
                        </div>
                    </div>

                    {{-- Número telefónico --}}
                    <div class="relative flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                            <iconify-icon icon="heroicons:phone" class="h-4 w-4 text-zinc-400"></iconify-icon>
                        </div>
                        <input
                            wire:model="phone"
                            type="tel"
                            inputmode="numeric"
                            placeholder="4241234567"
                            class="w-full rounded-lg border border-zinc-200 bg-white py-2 pl-9 pr-3 text-sm text-zinc-700 shadow-xs placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                    </div>
                </div>

                @error('phone')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror

                <p class="mt-1.5 text-xs text-zinc-400">
                    País seleccionado: <span class="font-medium text-zinc-600">{{ $countryName }}</span>
                    · Prefijo: <span class="font-medium text-zinc-600">{{ $countryCode }}</span>
                </p>
            </div>

            <flux:button
                type="submit"
                variant="primary"
                class="w-full !bg-indigo-600 hover:!bg-indigo-700"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>
                    <iconify-icon icon="heroicons:paper-airplane" class="h-4 w-4 me-1.5"></iconify-icon>
                    Enviar código OTP
                </span>
                <span wire:loading class="flex items-center justify-center gap-2">
                    <iconify-icon icon="line-md:loading-twotone-loop" class="h-4 w-4 animate-spin"></iconify-icon>
                    Enviando...
                </span>
            </flux:button>
        </form>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════════
         PASO 2: Verificar OTP
    ══════════════════════════════════════════════════════════════════════════ --}}
    @if($step === 'verify')
        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Verifica tu código</h1>
            <p class="mt-2 text-sm text-zinc-500">
                Hemos enviado un código de 6 dígitos a tu WhatsApp
                @if($phone)
                    al número <span class="font-semibold text-zinc-700">{{ $countryCode }} {{ $phone }}</span>.
                @else
                    registrado.
                @endif
            </p>
        </div>

        <form wire:submit.prevent="verifyOtp" class="space-y-5">
            {{-- Input OTP único de 6 dígitos --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-zinc-700">Código OTP</label>
                <input
                    wire:model="otp"
                    type="text"
                    inputmode="numeric"
                    maxlength="6"
                    placeholder="• • • • • •"
                    autofocus
                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-center text-2xl font-bold tracking-[0.6em] text-zinc-900 shadow-xs placeholder-zinc-300 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition"
                />
                @error('otp')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Info de seguridad --}}
            <div class="flex items-center gap-2 rounded-lg bg-amber-50 border border-amber-100 px-3 py-2.5 text-xs text-amber-700">
                <iconify-icon icon="heroicons:clock" class="h-3.5 w-3.5 shrink-0"></iconify-icon>
                El código expira en <strong class="ml-1">10 minutos</strong>.
            </div>

            {{-- Botones --}}
            <flux:button
                type="submit"
                variant="primary"
                class="w-full !bg-indigo-600 hover:!bg-indigo-700"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>
                    <iconify-icon icon="heroicons:shield-check" class="h-4 w-4 me-1.5"></iconify-icon>
                    Verificar código
                </span>
                <span wire:loading class="flex items-center justify-center gap-2">
                    <iconify-icon icon="line-md:loading-twotone-loop" class="h-4 w-4 animate-spin"></iconify-icon>
                    Verificando...
                </span>
            </flux:button>

            {{-- Reenviar código --}}
            <button
                type="button"
                wire:click="resendOtp"
                class="flex w-full items-center justify-center gap-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition"
            >
                <iconify-icon icon="heroicons:arrow-path" class="h-4 w-4"></iconify-icon>
                No recibí el código — Reintentar
            </button>
        </form>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════════
         PASO 3: Nueva contraseña
    ══════════════════════════════════════════════════════════════════════════ --}}
    @if($step === 'reset')
        <div class="mb-6">
            <div class="mb-4 flex items-center gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3">
                <iconify-icon icon="heroicons:shield-check-solid" class="h-5 w-5 text-emerald-600 shrink-0"></iconify-icon>
                <p class="text-sm font-medium text-emerald-700">Identidad verificada correctamente</p>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Nueva contraseña</h1>
            <p class="mt-2 text-sm text-zinc-500">
                Elige una contraseña segura de al menos 8 caracteres con letras y números.
            </p>
        </div>

        <form wire:submit.prevent="resetPassword" class="space-y-5">
            {{-- Nueva contraseña --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-zinc-700">Nueva contraseña</label>
                <div class="relative" x-data="{ show: false }">
                    <input
                        wire:model="password"
                        :type="show ? 'text' : 'password'"
                        placeholder="Mínimo 8 caracteres"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 pr-10 text-sm text-zinc-700 shadow-xs placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                    <button
                        type="button"
                        @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 transition"
                    >
                        <iconify-icon :icon="show ? 'heroicons:eye-slash' : 'heroicons:eye'" class="h-4 w-4"></iconify-icon>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirmar contraseña --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-zinc-700">Confirmar contraseña</label>
                <div class="relative" x-data="{ show: false }">
                    <input
                        wire:model="password_confirmation"
                        :type="show ? 'text' : 'password'"
                        placeholder="Repite tu nueva contraseña"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 pr-10 text-sm text-zinc-700 shadow-xs placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                    <button
                        type="button"
                        @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 transition"
                    >
                        <iconify-icon :icon="show ? 'heroicons:eye-slash' : 'heroicons:eye'" class="h-4 w-4"></iconify-icon>
                    </button>
                </div>
            </div>

            {{-- Indicador de fortaleza --}}
            <div x-data="{
                get strength() {
                    const p = $wire.password;
                    if (!p) return 0;
                    let s = 0;
                    if (p.length >= 8) s++;
                    if (/[A-Z]/.test(p)) s++;
                    if (/[0-9]/.test(p)) s++;
                    if (/[^A-Za-z0-9]/.test(p)) s++;
                    return s;
                },
                get label() {
                    return ['', 'Débil', 'Regular', 'Buena', 'Fuerte'][this.strength] || '';
                },
                get color() {
                    return ['', 'bg-red-400', 'bg-amber-400', 'bg-blue-400', 'bg-emerald-500'][this.strength] || '';
                }
            }">
                <div class="flex gap-1.5" x-show="$wire.password.length > 0">
                    <template x-for="i in 4">
                        <div
                            :class="i <= strength ? color : 'bg-zinc-100'"
                            class="h-1.5 flex-1 rounded-full transition-all duration-300"
                        ></div>
                    </template>
                </div>
                <p class="mt-1 text-xs" :class="{'text-red-500': strength === 1, 'text-amber-500': strength === 2, 'text-blue-500': strength === 3, 'text-emerald-600': strength === 4}" x-show="$wire.password.length > 0" x-text="label"></p>
            </div>

            <flux:button
                type="submit"
                variant="primary"
                class="w-full !bg-indigo-600 hover:!bg-indigo-700"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>
                    <iconify-icon icon="heroicons:lock-closed" class="h-4 w-4 me-1.5"></iconify-icon>
                    Cambiar contraseña
                </span>
                <span wire:loading class="flex items-center justify-center gap-2">
                    <iconify-icon icon="line-md:loading-twotone-loop" class="h-4 w-4 animate-spin"></iconify-icon>
                    Guardando...
                </span>
            </flux:button>
        </form>
    @endif
</div>
