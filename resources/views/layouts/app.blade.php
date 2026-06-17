<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Laratail Store') }}</title>

    <meta name="description" content="{{ $description ?? 'Laratail Store - Tu destino para encontrar el mejor estilo y calidad.' }}">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title ?? config('app.name', 'Laratail Store') }}">
    <meta property="og:description" content="{{ $description ?? 'Explora nuestro catálogo de productos.' }}">
    <meta property="og:site_name" content="Laratail Store">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    @fonts
    @fluxAppearance

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @livewireStyles
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

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    @livewireScripts
    @fluxScripts
</body>
</html>
