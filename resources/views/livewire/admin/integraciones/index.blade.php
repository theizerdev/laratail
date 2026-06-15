<div>
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Integraciones</h2>
        <p class="mt-1 text-sm text-gray-500">Conecta servicios externos para ampliar las capacidades del sistema.</p>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            <iconify-icon icon="heroicons:exclamation-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('error') }}
        </div>
    @endif

    {{-- Integration Cards --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">

        {{-- WhatsApp CRM --}}
        @can('whatsapp.view')
            <a href="{{ route('admin.integraciones.whatsapp') }}" wire:navigate class="group block">
                <div class="rounded-2xl bg-white p-6 shadow-sm transition hover:shadow-md">
                    <div class="mb-4 flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-100">
                            <iconify-icon icon="mdi:whatsapp" class="h-7 w-7 text-green-600"></iconify-icon>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">
                            Disponible
                        </span>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-green-700 transition">
                        WhatsApp CRM
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Conecta WhatsApp Business para enviar mensajes masivos, gestionar contactos y automatizar comunicaciones.
                    </p>
                    <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4">
                        <div class="flex items-center gap-2">
                            @if($empresasConWhatsapp > 0)
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                <span class="text-xs text-gray-500">{{ $empresasConWhatsapp }} empresa(s) configurada(s)</span>
                            @else
                                <span class="h-2 w-2 rounded-full bg-gray-300"></span>
                                <span class="text-xs text-gray-400">Sin configurar</span>
                            @endif
                        </div>
                        <iconify-icon icon="heroicons:arrow-right" class="h-4 w-4 text-gray-400 group-hover:text-green-600 transition"></iconify-icon>
                    </div>
                </div>
            </a>
        @endcan

        {{-- Placeholder: Email Marketing --}}
        <div class="block">
            <div class="rounded-2xl bg-gray-50 p-6 border-2 border-dashed border-gray-200 opacity-60">
                <div class="mb-4 flex items-start justify-between">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100">
                        <iconify-icon icon="heroicons:envelope" class="h-7 w-7 text-gray-400"></iconify-icon>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">
                        Proximamente
                    </span>
                </div>
                <h3 class="text-base font-semibold text-gray-500">Email Marketing</h3>
                <p class="mt-1 text-sm text-gray-400">
                    Envio de campanas de email, automatizacion y seguimiento de apertura.
                </p>
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <span class="text-xs text-gray-400">Proximamente disponible</span>
                </div>
            </div>
        </div>

        {{-- Placeholder: SMS --}}
        <div class="block">
            <div class="rounded-2xl bg-gray-50 p-6 border-2 border-dashed border-gray-200 opacity-60">
                <div class="mb-4 flex items-start justify-between">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100">
                        <iconify-icon icon="heroicons:chat-bubble-left" class="h-7 w-7 text-gray-400"></iconify-icon>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">
                        Proximamente
                    </span>
                </div>
                <h3 class="text-base font-semibold text-gray-500">SMS Masivos</h3>
                <p class="mt-1 text-sm text-gray-400">
                    Envio de mensajes SMS a contactos y clientes con seguimiento de entrega.
                </p>
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <span class="text-xs text-gray-400">Proximamente disponible</span>
                </div>
            </div>
        </div>

    </div>
</div>
