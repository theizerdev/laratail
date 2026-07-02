<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4f46e5">
    <title>{{ config('app.name', 'Laratail Store') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    @fonts
    @fluxAppearance

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @livewireStyles

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

    @stack('head-scripts')
</head>
<body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Navbar Partial -->
    <livewire:store.partials.navbar />

    <!-- Main Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer Partial -->
    <livewire:store.partials.footer />

    <!-- Social Proof Notifications -->
    <livewire:store.partials.social-proof />

    <!-- WhatsApp Chat Widget -->
    <livewire:store.partials.whatsapp-chat />

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

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

    @livewireScripts
    @fluxScripts
</body>
</html>

