<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin' }} - {{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

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
                <flux:menu.item icon="cog" href="#">Configuración</flux:menu.item>
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
    <flux:header class="border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" />
        
        <flux:heading class="hidden lg:block">{{ $title ?? 'Dashboard' }}</flux:heading>
        
        <flux:spacer />
        
        {{-- Notificaciones e Mensajes --}}
        <flux:button variant="subtle" icon="bell" class="relative">
            <span class="absolute top-1 right-1 h-2 w-2 rounded-full bg-red-500"></span>
        </flux:button>
        <flux:button variant="subtle" icon="chat-bubble-left-right" class="hidden sm:inline-flex" />

        <div class="mx-2 h-6 w-px bg-zinc-200 dark:bg-zinc-800 hidden sm:block"></div>

        {{-- Dropdown del perfil móvil/compacto --}}
        <flux:dropdown position="bottom" align="end">
            <button class="flex items-center gap-2 rounded-xl p-1 transition hover:bg-zinc-100 dark:hover:bg-zinc-800">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-950 text-xs font-bold text-emerald-700 dark:text-emerald-300">
                    {{ Auth::user()->initials() }}
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
                <flux:menu.item icon="cog" href="#">Configuración</flux:menu.item>
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
</body>
</html>
