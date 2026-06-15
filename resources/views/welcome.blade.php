<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Flux Demo - {{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

        @fonts
        @fluxAppearance

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-zinc-50 min-h-screen flex items-center justify-center p-6">
        <div class="max-w-2xl w-full space-y-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-zinc-900">Tailwind + Livewire Flux</h1>
                <p class="mt-2 text-zinc-600">Todo esta funcionando correctamente.</p>
            </div>

            <flux:card>
                <flux:heading size="lg">Componentes Flux</flux:heading>
                <flux:subheading>Aqui tienes algunos ejemplos de componentes.</flux:subheading>

                <div class="mt-6 space-y-4">
                    <flux:input label="Nombre" placeholder="Escribe tu nombre..." />

                    <flux:select label="Pais">
                        <option>España</option>
                        <option>Mexico</option>
                        <option>Argentina</option>
                        <option>Colombia</option>
                    </flux:select>

                    <flux:checkbox label="Acepto los terminos y condiciones" />

                    <div class="flex gap-3">
                        <flux:button variant="primary">Guardar</flux:button>
                        <flux:button variant="ghost">Cancelar</flux:button>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <flux:heading size="lg">Badges y Avatares</flux:heading>
                <div class="mt-4 flex flex-wrap gap-2">
                    <flux:badge color="green">Activo</flux:badge>
                    <flux:badge color="red">Inactivo</flux:badge>
                    <flux:badge color="blue">Pendiente</flux:badge>
                    <flux:badge color="amber">En revision</flux:badge>
                </div>
            </flux:card>
        </div>
    </body>
    @livewireScripts
    @fluxScripts
</html>
