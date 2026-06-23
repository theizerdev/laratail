<div x-data="{ tab: @entangle('activeTab') }" class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-1.5 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-950 dark:text-white">Configuración del Perfil</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Actualiza tus datos personales, cambia tu contraseña y configura la seguridad de tu cuenta.</p>
        </div>
    </div>

    {{-- Main Grid Layout --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        
        {{-- Left Sidebar: Avatar & Tab Links --}}
        <div class="lg:col-span-4 space-y-6">
            {{-- User card --}}
            <div class="rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-xs">
                <div class="flex flex-col items-center text-center">
                    {{-- Large Initials Avatar --}}
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-50 dark:bg-indigo-950/40 text-xl font-bold text-indigo-600 dark:text-indigo-400 shadow-sm border border-indigo-100 dark:border-indigo-900/50">
                        {{ auth()->user()->initials() }}
                    </div>
                    <h2 class="mt-4 text-lg font-bold text-zinc-900 dark:text-white">{{ auth()->user()->name }}</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ auth()->user()->email }}</p>
                    
                    {{-- Role Badge --}}
                    <div class="mt-3 flex items-center gap-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:text-emerald-400 border border-emerald-100/80 dark:border-emerald-900/40">
                        <iconify-icon icon="heroicons:shield-check" class="h-3.5 w-3.5"></iconify-icon>
                        {{ auth()->user()->roles->pluck('name')->implode(', ') ?: 'Usuario' }}
                    </div>
                </div>

                {{-- Navigation Options --}}
                <div class="mt-8 space-y-1">
                    <button 
                        @click="tab = 'info'" 
                        :class="tab === 'info' 
                            ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-semibold' 
                            : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 hover:text-zinc-900 dark:hover:text-zinc-200'"
                        class="flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition-all duration-200"
                    >
                        <iconify-icon icon="heroicons:user" class="h-5 w-5"></iconify-icon>
                        <span>Información Personal</span>
                    </button>

                    <button 
                        @click="tab = 'security'" 
                        :class="tab === 'security' 
                            ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-semibold' 
                            : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 hover:text-zinc-900 dark:hover:text-zinc-200'"
                        class="flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition-all duration-200"
                    >
                        <iconify-icon icon="heroicons:key" class="h-5 w-5"></iconify-icon>
                        <span>Cambiar Contraseña</span>
                    </button>

                    <button 
                        @click="tab = '2fa'" 
                        :class="tab === '2fa' 
                            ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-semibold' 
                            : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 hover:text-zinc-900 dark:hover:text-zinc-200'"
                        class="flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition-all duration-200"
                    >
                        <iconify-icon icon="heroicons:shield-exclamation" class="h-5 w-5"></iconify-icon>
                        <span>Autenticación de 2 Factores</span>
                        @if(auth()->user()->hasTwoFactorEnabled())
                            <span class="ml-auto h-2 w-2 rounded-full bg-emerald-500 ring-4 ring-emerald-500/20"></span>
                        @endif
                    </button>
                </div>
            </div>
        </div>

        {{-- Right Pane: Active Form Card --}}
        <div class="lg:col-span-8">
            <div class="rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-xs">
                
                {{-- Tab 1: Personal Info --}}
                <div x-show="tab === 'info'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" class="space-y-6">
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-white">Información Personal</h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Actualiza los datos básicos de tu cuenta.</p>
                    </div>

                    @if (session()->has('success_profile'))
                        <div class="flex items-center gap-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/50 px-4 py-3 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                            <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5 text-emerald-500"></iconify-icon>
                            <span>{{ session('success_profile') }}</span>
                        </div>
                    @endif

                    <form wire:submit.prevent="updateProfile" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <flux:input 
                                wire:model="name" 
                                label="Nombre Completo" 
                                placeholder="Ingresa tu nombre"
                            />
                            
                            <flux:input 
                                wire:model="email" 
                                type="email" 
                                label="Correo Electrónico" 
                                placeholder="correo@ejemplo.com"
                            />
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <flux:input 
                                wire:model="telefono" 
                                type="tel" 
                                label="Teléfono (Opcional)" 
                                placeholder="+584120000000"
                            />
                        </div>

                        <div class="flex justify-end pt-4 border-t border-zinc-100 dark:border-zinc-850">
                            <flux:button type="submit" variant="primary" class="!bg-indigo-600 hover:!bg-indigo-700">
                                Guardar Cambios
                            </flux:button>
                        </div>
                    </form>
                </div>

                {{-- Tab 2: Change Password --}}
                <div x-show="tab === 'security'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" class="space-y-6" style="display: none;">
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-white">Cambiar Contraseña</h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Asegúrate de que tu cuenta esté utilizando una contraseña segura y aleatoria.</p>
                    </div>

                    @if (session()->has('success_password'))
                        <div class="flex items-center gap-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/50 px-4 py-3 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                            <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5 text-emerald-500"></iconify-icon>
                            <span>{{ session('success_password') }}</span>
                        </div>
                    @endif

                    <form wire:submit.prevent="updatePassword" class="space-y-4">
                        <flux:input 
                            wire:model="current_password" 
                            type="password" 
                            label="Contraseña Actual" 
                            placeholder="••••••••"
                        />

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <flux:input 
                                wire:model="new_password" 
                                type="password" 
                                label="Nueva Contraseña" 
                                placeholder="Mínimo 8 caracteres"
                            />
                            
                            <flux:input 
                                wire:model="new_password_confirmation" 
                                type="password" 
                                label="Confirmar Nueva Contraseña" 
                                placeholder="Repite la contraseña"
                            />
                        </div>

                        <div class="flex justify-end pt-4 border-t border-zinc-100 dark:border-zinc-850">
                            <flux:button type="submit" variant="primary" class="!bg-indigo-600 hover:!bg-indigo-700">
                                Actualizar Contraseña
                            </flux:button>
                        </div>
                    </form>
                </div>

                {{-- Tab 3: Two-Factor Authentication (2FA) --}}
                <div x-show="tab === '2fa'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" class="space-y-6" style="display: none;">
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-white">Autenticación de Dos Pasos (2FA)</h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Agrega seguridad adicional a tu cuenta mediante autenticación TOTP.</p>
                    </div>

                    @if (session()->has('success_2fa'))
                        <div class="flex items-center gap-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/50 px-4 py-3 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                            <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5 text-emerald-500"></iconify-icon>
                            <span>{{ session('success_2fa') }}</span>
                        </div>
                    @endif

                    @if(auth()->user()->hasTwoFactorEnabled())
                        {{-- 2FA ACTIVE STATE --}}
                        <div class="rounded-xl border border-emerald-100 dark:border-emerald-900/30 bg-emerald-50/40 dark:bg-emerald-950/10 p-5 flex items-start gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400">
                                <iconify-icon icon="heroicons:shield-check-solid" class="h-6 w-6"></iconify-icon>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-400">La autenticación de dos pasos está activa</h4>
                                <p class="text-xs text-zinc-550 dark:text-zinc-400">Tu cuenta está protegida con un segundo factor de autenticación. Deberás ingresar tu código dinámico cada vez que inicies sesión.</p>
                            </div>
                        </div>

                        {{-- Show recovery codes --}}
                        <div class="space-y-3 pt-4 border-t border-zinc-100 dark:border-zinc-850">
                            <div>
                                <h4 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">Códigos de Recuperación</h4>
                                <p class="text-xs text-zinc-500">Guarda estos códigos de recuperación en un lugar seguro. Te permitirán acceder a tu cuenta si pierdes acceso a tu dispositivo autenticador.</p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2 max-w-sm rounded-xl bg-zinc-50 dark:bg-zinc-950 p-4 font-mono text-xs border border-zinc-200/50 dark:border-zinc-800/80">
                                @foreach($recoveryCodes as $code)
                                    <div class="text-zinc-700 dark:text-zinc-300 font-semibold select-all">{{ $code }}</div>
                                @endforeach
                            </div>

                            <div class="flex gap-2">
                                <button onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs font-semibold text-zinc-750 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                                    <iconify-icon icon="heroicons:printer" class="h-4 w-4"></iconify-icon>
                                    Imprimir
                                </button>
                            </div>
                        </div>

                        {{-- Disable button --}}
                        <div class="pt-6 border-t border-zinc-100 dark:border-zinc-850">
                            @if($isDisabling2FA)
                                <form wire:submit.prevent="disableTwoFactor" class="space-y-4 max-w-sm">
                                    <flux:input 
                                        wire:model="confirmPasswordFor2FA" 
                                        type="password" 
                                        label="Confirma tu contraseña para desactivar 2FA" 
                                        placeholder="Ingresa tu contraseña"
                                    />
                                    <div class="flex items-center gap-2">
                                        <flux:button type="submit" variant="danger">
                                            Desactivar 2FA
                                        </flux:button>
                                        <flux:button type="button" wire:click="$set('isDisabling2FA', false)">
                                            Cancelar
                                        </flux:button>
                                    </div>
                                </form>
                            @else
                                <flux:button type="button" wire:click="startDisableTwoFactor" variant="danger">
                                    Desactivar Autenticación de Dos Pasos
                                </flux:button>
                            @endif
                        </div>

                    @else
                        {{-- 2FA INACTIVE STATE --}}
                        @if($isConfiguring2FA)
                            {{-- Configuration Flow (QR & Verification code) --}}
                            <div class="space-y-6 x-transition">
                                <div class="rounded-xl bg-indigo-50/40 dark:bg-indigo-950/10 border border-indigo-100 dark:border-indigo-900/30 p-4">
                                    <h4 class="text-sm font-semibold text-indigo-900 dark:text-indigo-400">Configurando Autenticación de Dos Pasos</h4>
                                    <p class="text-xs text-zinc-550 dark:text-zinc-400 mt-1">Sigue los pasos a continuación para activar la autenticación multifactor en tu cuenta.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                    {{-- QR Code --}}
                                    <div class="md:col-span-4 flex flex-col items-center justify-center p-4 bg-white dark:bg-zinc-950 rounded-2xl border border-zinc-200/60 dark:border-zinc-800/80">
                                        <img src="{{ $tempQrCodeUrl }}" alt="Código QR de Autenticación" class="w-44 h-44 rounded-xl border border-zinc-100 dark:border-zinc-900">
                                        <span class="text-[10px] text-zinc-400 mt-2 font-mono break-all select-all">Secreto: {{ $tempSecret }}</span>
                                    </div>

                                    {{-- Setup steps --}}
                                    <div class="md:col-span-8 space-y-4">
                                        <div class="flex gap-3">
                                            <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800 text-xs font-bold text-zinc-700 dark:text-zinc-300">1</div>
                                            <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                                <strong class="text-zinc-800 dark:text-zinc-200">Escanea el código QR</strong> en tu aplicación de autenticación (como Google Authenticator, Microsoft Authenticator o Authy). Si no puedes escanearlo, ingresa la clave secreta provista manualmente.
                                            </div>
                                        </div>
                                        
                                        <div class="flex gap-3">
                                            <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800 text-xs font-bold text-zinc-700 dark:text-zinc-300">2</div>
                                            <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                                <strong class="text-zinc-800 dark:text-zinc-200">Guarda tus códigos de recuperación</strong>. Tendrás acceso a ellos una vez confirmes el código de seguridad. Consérvalos en un lugar seguro.
                                            </div>
                                        </div>

                                        <div class="flex gap-3 pt-2">
                                            <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800 text-xs font-bold text-zinc-700 dark:text-zinc-300">3</div>
                                            <div class="flex-1 space-y-3">
                                                <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                                    <strong class="text-zinc-800 dark:text-zinc-200">Confirma el código generado</strong> de tu aplicación para activar la sincronización.
                                                </div>
                                                
                                                <form wire:submit.prevent="confirmTwoFactor" class="max-w-xs space-y-3">
                                                    <flux:input 
                                                        wire:model="twoFactorCode" 
                                                        placeholder="000000" 
                                                        maxlength="6"
                                                        class="text-center tracking-widest font-mono text-base"
                                                    />
                                                    
                                                    <div class="flex items-center gap-2">
                                                        <flux:button type="submit" variant="primary" class="!bg-indigo-600 hover:!bg-indigo-700">
                                                            Confirmar y Activar
                                                        </flux:button>
                                                        <flux:button type="button" wire:click="cancelTwoFactorSetup">
                                                            Cancelar
                                                        </flux:button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Intro Card --}}
                            <div class="space-y-4">
                                <div class="rounded-xl border border-zinc-200/80 dark:border-zinc-800 bg-zinc-50/40 dark:bg-zinc-950/20 p-5 flex items-start gap-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                                        <iconify-icon icon="heroicons:shield-solid" class="h-6 w-6"></iconify-icon>
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">La autenticación de dos pasos está desactivada</h4>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Activa esta característica para proteger tu cuenta de accesos no autorizados. Al habilitar 2FA, se te solicitará ingresar una contraseña de un solo uso generada por tu dispositivo móvil en cada inicio de sesión.</p>
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <flux:button type="button" wire:click="initTwoFactor" variant="primary" class="!bg-indigo-600 hover:!bg-indigo-700">
                                        Habilitar Autenticación de Dos Pasos
                                    </flux:button>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

            </div>
        </div>

    </div>
</div>
