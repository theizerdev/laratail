<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="{{ $meta_robots ?? 'index, follow' }}">

    <title>{{ $title ?? config('app.name', 'Abastos Los Trinis') }}</title>

    <meta name="description" content="{{ $description ?? 'Abastos Los Trinis - Tu supermercado en línea de confianza. Compra víveres, charcutería, bebidas y más al mejor precio.' }}">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $og_type ?? 'website' }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    <meta property="og:title" content="{{ $title ?? config('app.name', 'Abastos Los Trinis') }}">
    <meta property="og:description" content="{{ $description ?? 'Abastos Los Trinis - Tu supermercado en línea de confianza. Compra víveres, charcutería, bebidas y más al mejor precio.' }}">
    <meta property="og:image" content="{{ $og_image ?? asset('images/logo.png') }}">
    <meta property="og:site_name" content="Abastos Los Trinis">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $canonical ?? url()->current() }}">
    <meta name="twitter:title" content="{{ $title ?? config('app.name', 'Abastos Los Trinis') }}">
    <meta name="twitter:description" content="{{ $description ?? 'Abastos Los Trinis - Tu supermercado en línea de confianza. Compra víveres, charcutería, bebidas y más al mejor precio.' }}">
    <meta name="twitter:image" content="{{ $og_image ?? asset('images/logo.png') }}">

    <link rel="icon" href="/images/logo.png" sizes="any">
    <link rel="icon" href="/images/logo.png" type="image/png">
    <link class="apple-touch-icon" href="/images/logo.png">

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">

    {{-- JSON-LD Structured Data Stack --}}
    @stack('structured-data')

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



    <!-- Search Overlay -->
    <livewire:store.partials.search-overlay />

    <!-- WhatsApp Chat Widget -->
    <livewire:store.partials.whatsapp-chat />

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- PWA Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            const registerSW = () => {
                navigator.serviceWorker.register('/serviceworker.js')
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