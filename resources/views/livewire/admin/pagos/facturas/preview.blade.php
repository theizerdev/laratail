<div>
    {{-- Action bar (hidden on print) --}}
    <div class="mb-6 flex items-center justify-between print:hidden">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Vista Previa</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $invoice->tipo_label }} {{ $invoice->numero }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.facturas') }}">
                <flux:button variant="outline"><iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon> Volver</flux:button>
            </a>
            <a href="{{ route('admin.facturas.pdf', $invoice->id) }}">
                <flux:button variant="primary" class="bg-emerald-600 hover:bg-emerald-700"><iconify-icon icon="heroicons:document-arrow-down" class="h-4 w-4"></iconify-icon> PDF</flux:button>
            </a>
            <flux:button onclick="window.print()" variant="primary"><iconify-icon icon="heroicons:printer" class="h-4 w-4"></iconify-icon> Imprimir</flux:button>
        </div>
    </div>

    {{-- Invoice document --}}
    <div class="mx-auto max-w-4xl rounded-2xl bg-white p-8 shadow-sm print:shadow-none">
        {{-- Header --}}
        <div class="mb-8 flex items-start justify-between border-b border-gray-200 pb-6">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">{{ strtoupper($invoice->tipo_label) }}</h2>
                <p class="mt-1 text-lg font-semibold text-cyan-600">{{ $invoice->numero }}</p>
                @if ($invoice->serie)
                    <p class="text-sm text-gray-500">Serie: {{ $invoice->serie }}</p>
                @endif
                @if ($invoice->numero_control)
                    <p class="text-sm text-gray-500">N° Control: {{ $invoice->numero_control }}</p>
                @endif
            </div>
            <div class="text-right">
                @if ($invoice->empresa)
                    <p class="text-lg font-bold text-gray-900">{{ $invoice->empresa->nombre ?? '' }}</p>
                @endif
                @if ($invoice->sucursal)
                    <p class="text-sm text-gray-500">{{ $invoice->sucursal->nombre ?? '' }}</p>
                @endif
                <p class="mt-2 text-sm text-gray-500">Fecha: {{ $invoice->fecha_emision?->format('d/m/Y') }}</p>
                @php $color = $invoice->estado_color; @endphp
                <span class="mt-1 inline-flex items-center gap-1.5 rounded-full bg-{{ $color }}-50 px-2.5 py-1 text-xs font-semibold text-{{ $color }}-700 ring-1 ring-inset ring-{{ $color }}-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-{{ $color }}-500"></span>
                    {{ ucfirst($invoice->estado) }}
                </span>
            </div>
        </div>

        {{-- Customer info --}}
        @if ($invoice->order?->customer)
            <div class="mb-6 rounded-xl bg-gray-50 p-4">
                <div class="mb-2 flex items-center gap-2">
                    <iconify-icon icon="heroicons:user-circle-solid" class="h-5 w-5 text-gray-500"></iconify-icon>
                    <h3 class="text-sm font-bold text-gray-700">Datos del Cliente</h3>
                </div>
                <p class="text-sm font-medium text-gray-900">{{ $invoice->order->customer->nombre ?? '-' }}</p>
                @if ($invoice->order->customer->email)
                    <p class="text-sm text-gray-500">{{ $invoice->order->customer->email }}</p>
                @endif
                @if ($invoice->order->customer->telefono)
                    <p class="text-sm text-gray-500">{{ $invoice->order->customer->telefono }}</p>
                @endif
                @if ($invoice->order->customer->rif)
                    <p class="text-sm text-gray-500">RIF: {{ $invoice->order->customer->rif }}</p>
                @endif
            </div>
        @endif

        {{-- Items table --}}
        <div class="mb-6 overflow-x-auto rounded-xl">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b-2 border-gray-300">
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-600">#</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-600">Producto</th>
                        <th class="px-3 py-2 text-center text-xs font-semibold uppercase text-gray-600">Cant.</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-gray-600">P. Unit.</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-gray-600">Desc.</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-gray-600">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $i => $item)
                        <tr class="border-b border-gray-200">
                            <td class="px-3 py-2 text-sm text-gray-500">{{ $i + 1 }}</td>
                            <td class="px-3 py-2 text-sm text-gray-900">
                                {{ $item->nombre_producto }}
                                @if ($item->sku)<br><span class="text-xs text-gray-500">SKU: {{ $item->sku }}</span>@endif
                            </td>
                            <td class="px-3 py-2 text-center text-sm text-gray-900">{{ $item->cantidad }}</td>
                            <td class="px-3 py-2 text-right text-sm text-gray-900">${{ number_format($item->precio_unitario, 2) }}</td>
                            <td class="px-3 py-2 text-right text-sm text-gray-900">${{ number_format($item->descuento, 2) }}</td>
                            <td class="px-3 py-2 text-right text-sm font-bold text-gray-900">${{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="mb-6 flex justify-end">
            <div class="w-64 space-y-2 rounded-xl bg-gray-50 p-4">
                <div class="flex justify-between text-sm"><span class="text-gray-500">Subtotal</span><span class="font-medium">${{ number_format($invoice->subtotal, 2) }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Impuesto (16%)</span><span class="font-medium">${{ number_format($invoice->impuesto, 2) }}</span></div>
                <div class="border-t border-gray-300 pt-3">
                    <div class="flex justify-between text-lg font-bold"><span class="text-gray-900">Total</span><span class="text-cyan-600">${{ number_format($invoice->total, 2) }}</span></div>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        @if ($invoice->notas)
            <div class="mb-6 rounded-xl bg-amber-50 p-4 ring-1 ring-inset ring-amber-200">
                <div class="mb-1 flex items-center gap-2">
                    <iconify-icon icon="heroicons:document-text-solid" class="h-4 w-4 text-amber-600"></iconify-icon>
                    <h3 class="text-sm font-bold text-amber-700">Notas</h3>
                </div>
                <p class="text-sm text-amber-700">{{ $invoice->notas }}</p>
            </div>
        @endif

        {{-- Footer --}}
        <div class="border-t border-gray-200 pt-4 text-center text-xs text-gray-500">
            <p>Documento generado el {{ now()->format('d/m/Y H:i') }}</p>
            <p class="mt-1">{{ $invoice->tipo_label }} N° {{ $invoice->numero }}</p>
        </div>
    </div>
</div>
