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
<body class="min-h-screen bg-gray-50 font-[Inter] ">
    {{-- Sidebar --}}
    <aside
        id="sidebar"
        class="fixed inset-y-0 left-0 z-40 flex w-[260px] flex-col  bg-blue-50 border-r border-gray-100 transition-transform duration-300 lg:translate-x-0 -translate-x-full"
    >
        {{-- Brand --}}
        <div class="flex h-[72px] items-center gap-3 px-6">
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500 text-sm font-bold text-white shadow-sm shadow-emerald-200">
                F
            </div>
            <span class="text-xl font-bold tracking-tight text-gray-900">Flux</span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-4 py-2">
            @include('components.layouts.partials.menu')
        </nav>

        {{-- User section at bottom --}}
        <div class="border-t border-gray-100 p-4">
            <flux:dropdown position="top" align="start">
                <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-gray-50">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">
                        {{ Auth::user()->initials() }}
                    </div>
                    <div class="min-w-0 flex-1 text-left">
                        <p class="truncate text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                        <p class="truncate text-xs text-gray-400">{{ Auth::user()->email }}</p>
                    </div>
                    <iconify-icon icon="heroicons:chevron-up-down" class="h-4 w-4 text-gray-400"></iconify-icon>
                </button>

                <flux:menu>
                    <flux:menu.item icon="cog" href="#">Settings</flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item
                        icon="arrow-right-end-on-rectangle"
                        href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    >
                        Log out
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </aside>

    {{-- Mobile overlay --}}
    <div
        id="sidebar-overlay"
        class="fixed inset-0 z-30 bg-gray-900/40 opacity-0 pointer-events-none transition-opacity duration-300 lg:hidden"
        onclick="toggleSidebar()"
    ></div>

    {{-- Main content area --}}
    <div class="lg:pl-[260px]">
        {{-- Top navbar --}}
        @include('components.layouts.partials.navbar', ['pageTitle' => $title ?? 'Dashboard'])

        {{-- Page content --}}
        <main class="p-6 lg:p-8">
            {{ $slot }}
        </main>
    </div>

    {{-- Hidden logout form --}}
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    @livewireScripts
    @fluxScripts

    {{-- Sidebar toggle script --}}
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const isOpen = !sidebar.classList.contains('-translate-x-full');

            if (isOpen) {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0', 'pointer-events-none');
                overlay.classList.remove('opacity-100');
            } else {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('opacity-0', 'pointer-events-none');
                overlay.classList.add('opacity-100');
            }
        }
    </script>
</body>
</html>
