<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="/images/logo.png" sizes="any">
    <link rel="icon" href="/images/logo.png" type="image/pngl">

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

    @livewireStyles

    {{-- Iconify Icons --}}
    <script src="https://code.iconify.design/iconify-icon/2.3.0/iconify-icon.min.js"></script>
</head>
<body class="min-h-screen bg-white">
    <div class="flex min-h-screen">
        {{-- Left Panel: Form Area --}}
        <div class="flex w-full flex-col items-center justify-center px-6 py-12 lg:w-1/2 lg:px-16 xl:px-24">
            <div class="w-full max-w-sm">
                {{-- Logo --}}
                <div class="mb-8 flex justify-center">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 80px; width: auto;">
                    </a>
                </div>

                {{-- Content --}}
                {{ $slot }}
            </div>
        </div>

        {{-- Right Panel: Hero / Testimonial --}}
        <div class="relative hidden w-1/2 lg:block">
            {{-- Aurora background --}}
            <div class="absolute inset-0 bg-gradient-to-br from-zinc-900 via-emerald-950 to-zinc-900">
                {{-- Aurora effect layers --}}
                <div class="absolute inset-0 opacity-60"
                     style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, transparent 50%),
                                          linear-gradient(225deg, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                                          linear-gradient(180deg, rgba(16, 185, 129, 0.2) 0%, transparent 60%);">
                </div>
                {{-- Subtle aurora streaks --}}
                <div class="absolute top-0 left-1/4 h-full w-px bg-gradient-to-b from-transparent via-emerald-400/20 to-transparent"></div>
                <div class="absolute top-0 left-1/3 h-full w-px bg-gradient-to-b from-transparent via-emerald-400/10 to-transparent"></div>
                <div class="absolute top-0 right-1/4 h-full w-px bg-gradient-to-b from-transparent via-cyan-400/15 to-transparent"></div>
                <div class="absolute top-0 right-1/3 h-full w-px bg-gradient-to-b from-transparent via-emerald-400/10 to-transparent"></div>
            </div>
            

            {{-- Testimonial content --}}
            <div class="relative z-10 flex h-full flex-col items-center justify-center px-12 text-center">
                
             
               

                {{-- Quote --}}
                <blockquote class="mb-8 max-w-md text-xl leading-relaxed font-medium text-white/90">
                    "Somos una empresa mayorista, encargada para el reabastecimiento de comercios.
                    
                    No somos solo un proveedor; nos convertimos en el socio estratégico de su rentabilidad. Nuestra operación se fundamenta en tres pilares esenciales:

                    Precios competitivos: Optimizamos los costos de origen para ofrecer tarifas por volumen que maximizan el margen de ganancia de nuestros clientes.
                    
                    Disponibilidad constante: Mantenemos un control estricto de inventario para asegurar un stock permanente de los productos de mayor rotación.
                    
                    Logística integral: Contamos con una red de distribución ágil que garantiza la entrega segura de los pedidos en los tiempos pactados.
                </blockquote>

                {{-- Author --}}
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 overflow-hidden rounded-full bg-zinc-700 ring-2 ring-white/20 flex items-center justify-center">
                        <iconify-icon icon="heroicons:user-solid" class="h-6 w-6 text-zinc-400"></iconify-icon>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-semibold text-white">Abastos Los Trinis</p>
                        <p class="text-xs text-white/60">Los abastos para ti</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @livewireScripts

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