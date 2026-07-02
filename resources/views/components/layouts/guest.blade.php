<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Laratail Store') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    @fonts
    @fluxAppearance

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @livewireStyles

    <!-- Iconify Icons -->
    <script src="https://code.iconify.design/iconify-icon/2.3.0/iconify-icon.min.js"></script>

    <!-- Dark mode base -->
    <style>
        .dark body { background-color: #09090b; color: #fafafa; }
        .dark .bg-white { background-color: #18181b !important; }
        .dark .bg-zinc-50 { background-color: #09090b !important; }
        .dark .border-zinc-100, .dark .border-zinc-200 { border-color: #27272a !important; }
        .dark .text-zinc-900 { color: #fafafa !important; }
        .dark .text-zinc-700 { color: #d4d4d8 !important; }
        .dark .text-zinc-600 { color: #a1a1aa !important; }
        .dark .text-zinc-500 { color: #71717a !important; }
        .dark .bg-zinc-100 { background-color: #27272a !important; }
    </style>
</head>
<body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased selection:bg-indigo-500 selection:text-white">
    <div class="relative min-h-screen flex flex-col justify-between">
        <!-- Optional Top Header/Bar -->
        <header class="border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 py-4 px-6 shadow-sm">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <a href="/" class="flex items-center gap-2">
                    <span class="text-xl font-bold tracking-tight text-indigo-600 dark:text-indigo-400">
                        {{ config('app.name', 'Laratail') }}
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 font-medium">
                        Portal Empleado
                    </span>
                </a>
                
                <div class="flex items-center gap-3" x-data="{ canInstall: false }" @beforeinstallprompt.window="window.deferredPrompt = $event; canInstall = true">
                    <!-- PWA Install Button -->
                    <button 
                        x-show="canInstall" 
                        @click="window.deferredPrompt.prompt(); window.deferredPrompt.userChoice.then(choice => { if (choice.outcome === 'accepted') { canInstall = false; } })" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-colors" 
                        style="display: none;"
                    >
                        <iconify-icon icon="heroicons:arrow-down-tray" class="h-4.5 w-4.5"></iconify-icon>
                        Instalar App
                    </button>

                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                        <iconify-icon x-show="!darkMode" icon="heroicons:moon" class="h-5 w-5"></iconify-icon>
                        <iconify-icon x-show="darkMode" icon="heroicons:sun" class="h-5 w-5" style="display: none;"></iconify-icon>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main slot -->
        <main class="flex-grow">
            {{ $slot }}
        </main>

        <!-- Minimal Footer -->
        <footer class="py-6 border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
            <div class="max-w-7xl mx-auto px-6 text-center text-xs text-zinc-500 dark:text-zinc-400">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
            </div>
        </footer>
    </div>

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
