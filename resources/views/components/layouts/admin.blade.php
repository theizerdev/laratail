<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin' }} - {{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @fonts
    @fluxAppearance
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    {{-- Iconify Icons --}}
    <script src="https://code.iconify.design/iconify-icon/2.3.0/iconify-icon.min.js"></script>
</head>
<body class="min-h-screen bg-white dark:bg-zinc-900 antialiased font-[Inter]">

    {{-- Sidebar Nativo de Flux --}}
    <flux:sidebar sticky collapsible class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-800">
        <flux:sidebar.header>
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500 text-sm font-bold text-white shadow-sm shadow-emerald-200">
                    F
                </div>
                <span class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">Flux</span>
            </div>
            <flux:sidebar.collapse />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            @include('components.layouts.partials.menu')
        </flux:sidebar.nav>

        <flux:spacer />

        {{-- Perfil del Usuario en la parte inferior --}}
        <flux:dropdown position="top" align="start">
            <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-zinc-100 dark:hover:bg-zinc-800">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-950 text-xs font-bold text-emerald-700 dark:text-emerald-300">
                    {{ Auth::user()->initials() }}
                </div>
                <div class="min-w-0 flex-1 text-left">
                    <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ Auth::user()->name }}</p>
                    <p class="truncate text-xs text-zinc-400 dark:text-zinc-500">{{ Auth::user()->email }}</p>
                </div>
                <iconify-icon icon="heroicons:chevron-up-down" class="h-4 w-4 text-zinc-400"></iconify-icon>
            </button>

            <flux:menu>
                <flux:menu.item icon="user" href="{{ route('admin.profile') }}" wire:navigate>Mi Perfil</flux:menu.item>
                <flux:menu.separator />
                <flux:menu.item
                    icon="arrow-right-end-on-rectangle"
                    href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                >
                    Cerrar sesión
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    {{-- Header superior unificado --}}
    <flux:header class="sticky top-0 z-40 border-b border-zinc-200/85 dark:border-zinc-800/85 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md shadow-sm">
        <flux:sidebar.toggle class="lg:hidden" />
        
        <div class="hidden lg:flex items-center gap-2">
            <span class="text-zinc-400 text-xs font-semibold uppercase tracking-wider">Admin</span>
            <iconify-icon icon="heroicons:chevron-right" class="h-3.5 w-3.5 text-zinc-300 dark:text-zinc-700"></iconify-icon>
            <span class="text-zinc-900 dark:text-white font-bold text-sm tracking-tight">{{ $title ?? 'Dashboard' }}</span>
        </div>
        
        <flux:spacer />
        
        {{-- Notificaciones y Mensajes --}}
        <div class="flex items-center gap-3">
            {{-- Dropdown de Notificaciones --}}
            <div x-data="{ open: false }" class="relative" @click.away="open = false">
                <button @click="open = !open" class="relative p-2 rounded-xl text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors flex items-center">
                    <iconify-icon icon="heroicons:bell" class="h-5 w-5"></iconify-icon>
                    <span class="absolute top-1.5 right-1.5 h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white dark:ring-zinc-900 animate-pulse"></span>
                </button>
                <div x-show="open" x-transition
                     class="absolute right-0 mt-2 w-80 bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-zinc-100 dark:border-zinc-800 py-3 z-50 overflow-hidden hidden"
                     :class="{ 'hidden': !open, 'block': open }">
                    <div class="px-4 pb-2.5 border-b border-zinc-100 dark:border-zinc-800 flex justify-between items-center">
                        <span class="font-bold text-sm text-zinc-900 dark:text-white">Notificaciones</span>
                        <button class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors">Marcar leídas</button>
                    </div>
                    <div class="max-h-72 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-850">
                        <div class="p-3.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors flex gap-3">
                            <div class="flex-shrink-0 h-8 w-8 rounded-lg bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center text-amber-500">
                                <iconify-icon icon="heroicons:exclamation-triangle" class="h-4.5 w-4.5"></iconify-icon>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Stock bajo detectado</p>
                                <p class="text-[11px] text-zinc-500 truncate">Camisa Oxford Classic (Solo 3 u.)</p>
                                <span class="text-[9px] text-zinc-400">Hace 10 min</span>
                            </div>
                        </div>
                        <div class="p-3.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors flex gap-3">
                            <div class="flex-shrink-0 h-8 w-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-emerald-500">
                                <iconify-icon icon="heroicons:shopping-cart" class="h-4.5 w-4.5"></iconify-icon>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Nuevo Pedido #1024</p>
                                <p class="text-[11px] text-zinc-500 truncate">Cliente: Test User - Total: $120.00</p>
                                <span class="text-[9px] text-zinc-400">Hace 1 hora</span>
                            </div>
                        </div>
                        <div class="p-3.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors flex gap-3">
                            <div class="flex-shrink-0 h-8 w-8 rounded-lg bg-blue-50 dark:bg-blue-950/40 flex items-center justify-center text-blue-500">
                                <iconify-icon icon="heroicons:server" class="h-4.5 w-4.5"></iconify-icon>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Backup completado</p>
                                <p class="text-[11px] text-zinc-500 truncate">Copia de seguridad semanal de DB</p>
                                <span class="text-[9px] text-zinc-400">Hace 4 horas</span>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 pt-2.5 border-t border-zinc-100 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/30 text-center">
                        <a href="#" class="text-xs text-zinc-500 hover:text-indigo-600 font-semibold transition-colors">Ver todas las notificaciones</a>
                    </div>
                </div>
            </div>

            {{-- Dropdown de Mensajes --}}
            <div x-data="{ open: false }" class="relative hidden sm:block" @click.away="open = false">
                <button @click="open = !open" class="relative p-2 rounded-xl text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors flex items-center">
                    <iconify-icon icon="heroicons:chat-bubble-left-right" class="h-5 w-5"></iconify-icon>
                    <span class="absolute top-1.5 right-1.5 h-2.5 w-2.5 rounded-full bg-indigo-500 ring-2 ring-white dark:ring-zinc-900"></span>
                </button>
                <div x-show="open" x-transition
                     class="absolute right-0 mt-2 w-80 bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-zinc-100 dark:border-zinc-800 py-3 z-50 overflow-hidden hidden"
                     :class="{ 'hidden': !open, 'block': open }">
                    <div class="px-4 pb-2.5 border-b border-zinc-100 dark:border-zinc-800 flex justify-between items-center">
                        <span class="font-bold text-sm text-zinc-900 dark:text-white">Mensajes Recientes</span>
                        <a href="#" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors">Ver chat</a>
                    </div>
                    <div class="max-h-72 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-850">
                        <div class="p-3.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors flex gap-3">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-xs font-bold text-indigo-600 dark:text-indigo-300">
                                TG
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Theizer Gonzalez</p>
                                <p class="text-[11px] text-zinc-500 truncate">Hola, ¿cuándo se despacha mi orden?</p>
                                <span class="text-[9px] text-zinc-400">Hace 5 min</span>
                            </div>
                        </div>
                        <div class="p-3.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors flex gap-3">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-xs font-bold text-emerald-600 dark:text-emerald-300">
                                TU
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Test User</p>
                                <p class="text-[11px] text-zinc-500 truncate">El pago por transferencia ya fue enviado.</p>
                                <span class="text-[9px] text-zinc-400">Hace 30 min</span>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 pt-2.5 border-t border-zinc-100 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/30 text-center">
                        <a href="#" class="text-xs text-zinc-500 hover:text-indigo-600 font-semibold transition-colors">Ver todos los mensajes</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-2 h-6 w-px bg-zinc-200 dark:bg-zinc-800 hidden sm:block"></div>

        {{-- Dropdown del perfil móvil/compacto --}}
        <flux:dropdown position="bottom" align="end">
            <button class="flex items-center gap-2 rounded-xl p-1 transition hover:bg-zinc-100 dark:hover:bg-zinc-800">
                <div class="relative">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-950 text-xs font-bold text-emerald-700 dark:text-emerald-300">
                        {{ Auth::user()->initials() }}
                    </div>
                    <span class="absolute bottom-0 right-0 h-2 w-2 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-zinc-900"></span>
                </div>
                <iconify-icon icon="heroicons:chevron-down" class="h-4 w-4 text-zinc-400"></iconify-icon>
            </button>
            <flux:menu>
                <flux:menu.heading>
                    <div class="px-1">
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-zinc-500">{{ Auth::user()->email }}</p>
                    </div>
                </flux:menu.heading>
                <flux:menu.separator />
                <flux:menu.item icon="user" href="{{ route('admin.profile') }}" wire:navigate>Mi Perfil</flux:menu.item>
                <flux:menu.separator />
                <flux:menu.item
                    icon="arrow-right-end-on-rectangle"
                    href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                >
                    Cerrar sesión
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{-- Contenido Principal --}}
    <flux:main>
        {{ $slot }}
    </flux:main>

    {{-- Formulario oculto de logout --}}
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    @livewireScripts
    @fluxScripts

    <!-- PWA Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            const registerSW = () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('PWA Service Worker registered successfully.'))
                    .catch(err => console.warn('PWA Service Worker registration failed:', err));
            };
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                registerSW();
            } else {
                window.addEventListener('load', registerSW);
            }
        }
    </script>
</body>
</html>
