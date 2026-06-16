<div>
    {{-- Action bar (hidden on print) --}}
    <div class="mb-6 flex items-center justify-between print:hidden">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Guía de Despacho</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Documento de guía para el envío {{ $shipment->numero }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.envios.edit', $shipment->id) }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
                <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon> Volver
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-1 rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700">
                <x-heroicons:printer class="h-4 w-4" /> Imprimir
            </button>
        </div>
    </div>

    {{-- Printable document --}}
    <div class="mx-auto max-w-4xl rounded-lg border border-gray-200 bg-white p-8 shadow-sm print:border-0 print:shadow-none print:p-0">
        {{-- Header --}}
        <div class="mb-8 flex items-start justify-between border-b pb-6">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">GUÍA DE DESPACHO</h2>
                <p class="mt-1 text-lg font-semibold text-cyan-600">{{ $shipment->numero }}</p>
            </div>
            <div class="text-right">
                @php $ec = $shipment->estado_color; @endphp
                <span class="inline-flex items-center rounded-full bg-{{ $ec }}-100 px-3 py-1 text-sm font-semibold text-{{ $ec }}-800">
                    {{ $shipment->estado_label }}
                </span>
                <p class="mt-2 text-sm text-gray-500">Fecha: {{ $shipment->created_at->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="mb-8 grid grid-cols-2 gap-8">
            {{-- Origin info --}}
            <div>
                <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-500">Remitente</h3>
                <div class="rounded-lg bg-gray-50 p-4">
                    <p class="font-semibold text-gray-900">{{ auth()->user()?->empresa?->nombre ?? config('app.name') }}</p>
                    <p class="text-sm text-gray-600">{{ auth()->user()?->sucursal?->direccion ?? '' }}</p>
                    <p class="text-sm text-gray-600">{{ auth()->user()?->sucursal?->telefono ?? '' }}</p>
                </div>
            </div>

            {{-- Destination info --}}
            <div>
                <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-500">Destinatario</h3>
                <div class="rounded-lg bg-gray-50 p-4">
                    <p class="font-semibold text-gray-900">{{ $shipment->order?->customer?->nombre ?? $shipment->order?->customer?->email ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-600">{{ $shipment->direccion_destino ?? $shipment->order?->direccion_envio ?? '' }}</p>
                    <p class="text-sm text-gray-600">
                        {{ $shipment->ciudad_destino ?? $shipment->order?->ciudad_envio ?? '' }}
                        {{ $shipment->estado_destino ?? $shipment->order?->estado_envio ?? '' }}
                        {{ $shipment->codigo_postal_destino ?? $shipment->order?->codigo_postal_envio ?? '' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Shipping details --}}
        <div class="mb-8">
            <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-500">Información del Envío</h3>
            <div class="grid grid-cols-4 gap-4">
                <div class="rounded-lg border p-3">
                    <p class="text-xs text-gray-500">Transportadora</p>
                    <p class="font-semibold text-gray-900">{{ $shipment->carrier_name ?? 'N/A' }}</p>
                </div>
                <div class="rounded-lg border p-3">
                    <p class="text-xs text-gray-500">Nro. Tracking</p>
                    <p class="font-mono font-semibold text-gray-900">{{ $shipment->tracking_number ?? 'N/A' }}</p>
                </div>
                <div class="rounded-lg border p-3">
                    <p class="text-xs text-gray-500">Peso</p>
                    <p class="font-semibold text-gray-900">{{ $shipment->peso ? number_format($shipment->peso, 3) . ' kg' : 'N/A' }}</p>
                </div>
                <div class="rounded-lg border p-3">
                    <p class="text-xs text-gray-500">Costo Envío</p>
                    <p class="font-semibold text-gray-900">{{ $shipment->costo_envio ? '$' . number_format($shipment->costo_envio, 2) : 'N/A' }}</p>
                </div>
            </div>
        </div>

        {{-- Dates --}}
        <div class="mb-8 grid grid-cols-3 gap-4">
            <div class="rounded-lg border p-3">
                <p class="text-xs text-gray-500">Fecha Envío</p>
                <p class="font-semibold text-gray-900">{{ $shipment->fecha_envio?->format('d/m/Y') ?? 'Pendiente' }}</p>
            </div>
            <div class="rounded-lg border p-3">
                <p class="text-xs text-gray-500">Entrega Esperada</p>
                <p class="font-semibold text-gray-900">{{ $shipment->fecha_entrega_esperada?->format('d/m/Y') ?? 'N/A' }}</p>
            </div>
            <div class="rounded-lg border p-3">
                <p class="text-xs text-gray-500">Fecha Entrega</p>
                <p class="font-semibold text-gray-900">{{ $shipment->fecha_entrega?->format('d/m/Y') ?? 'Pendiente' }}</p>
            </div>
        </div>

        {{-- Order items --}}
        @if ($shipment->order?->items?->count())
            <div class="mb-8">
                <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-500">
                    Contenido del Paquete — Orden: {{ $shipment->order->numero }}
                </h3>
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border-b px-4 py-2 text-left text-xs font-semibold uppercase text-gray-600">Producto</th>
                            <th class="border-b px-4 py-2 text-center text-xs font-semibold uppercase text-gray-600">SKU</th>
                            <th class="border-b px-4 py-2 text-center text-xs font-semibold uppercase text-gray-600">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($shipment->order->items as $item)
                            <tr>
                                <td class="border-b px-4 py-2 text-sm text-gray-900">{{ $item->product?->nombre ?? $item->nombre_producto }}</td>
                                <td class="border-b px-4 py-2 text-center text-sm text-gray-600">{{ $item->product?->sku ?? $item->sku ?? '-' }}</td>
                                <td class="border-b px-4 py-2 text-center text-sm font-semibold text-gray-900">{{ $item->cantidad }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Notes --}}
        @if ($shipment->notas)
            <div class="mb-8">
                <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-500">Observaciones</h3>
                <p class="rounded-lg bg-gray-50 p-4 text-sm text-gray-700">{{ $shipment->notas }}</p>
            </div>
        @endif

        {{-- Signatures --}}
        <div class="mt-12 grid grid-cols-2 gap-12">
            <div class="text-center">
                <div class="mb-2 border-t-2 border-gray-400 pt-2">
                    <p class="text-sm font-semibold text-gray-700">Despachado por</p>
                    <p class="text-xs text-gray-500">Fecha y Hora</p>
                </div>
            </div>
            <div class="text-center">
                <div class="mb-2 border-t-2 border-gray-400 pt-2">
                    <p class="text-sm font-semibold text-gray-700">Recibido por</p>
                    <p class="text-xs text-gray-500">Fecha y Hora</p>
                </div>
            </div>
        </div>
    </div>
</div>
