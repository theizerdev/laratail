<div>
    {{-- Action bar (hidden on print) --}}
    <div class="mb-6 flex items-center justify-between print:hidden">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Vista Previa</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $invoice->tipo_label }} {{ $invoice->numero }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.facturas') }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
                 <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon> Volver
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-1 rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700">
                <x-heroicons:printer class="h-4 w-4" /> Imprimir
            </button>
        </div>
    </div>

    {{-- Invoice document --}}
    <div class="mx-auto max-w-4xl rounded-lg border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-700 dark:bg-gray-800 print:border-0 print:shadow-none">
        {{-- Header --}}
        <div class="mb-8 flex items-start justify-between border-b border-gray-200 pb-6 dark:border-gray-700">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ strtoupper($invoice->tipo_label) }}</h2>
                <p class="mt-1 text-lg font-semibold text-cyan-600 dark:text-cyan-400">{{ $invoice->numero }}</p>
                @if ($invoice->serie)
                    <p class="text-sm text-gray-500">Serie: {{ $invoice->serie }}</p>
                @endif
                @if ($invoice->numero_control)
                    <p class="text-sm text-gray-500">N° Control: {{ $invoice->numero_control }}</p>
                @endif
            </div>
            <div class="text-right">
                @if ($invoice->empresa)
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $invoice->empresa->nombre ?? '' }}</p>
                @endif
                @if ($invoice->sucursal)
                    <p class="text-sm text-gray-500">{{ $invoice->sucursal->nombre ?? '' }}</p>
                @endif
                <p class="mt-2 text-sm text-gray-500">Fecha: {{ $invoice->fecha_emision?->format('d/m/Y') }}</p>
                <span class="mt-1 inline-flex rounded-full bg-{{ $invoice->estado_color }}-100 px-2 py-0.5 text-xs font-medium text-{{ $invoice->estado_color }}-700">
                    {{ ucfirst($invoice->estado) }}
                </span>
            </div>
        </div>

        {{-- Customer info --}}
        @if ($invoice->order?->customer)
            <div class="mb-6 rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                <h3 class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Datos del Cliente</h3>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $invoice->order->customer->nombre ?? '-' }}</p>
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
        <div class="mb-6 overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b-2 border-gray-300 dark:border-gray-600">
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">#</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Producto</th>
                        <th class="px-3 py-2 text-center text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Cant.</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">P. Unit.</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Desc.</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $i => $item)
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="px-3 py-2 text-sm text-gray-500">{{ $i + 1 }}</td>
                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-white">
                                {{ $item->nombre_producto }}
                                @if ($item->sku)<br><span class="text-xs text-gray-500">SKU: {{ $item->sku }}</span>@endif
                            </td>
                            <td class="px-3 py-2 text-center text-sm text-gray-900 dark:text-white">{{ $item->cantidad }}</td>
                            <td class="px-3 py-2 text-right text-sm text-gray-900 dark:text-white">${{ number_format($item->precio_unitario, 2) }}</td>
                            <td class="px-3 py-2 text-right text-sm text-gray-900 dark:text-white">${{ number_format($item->descuento, 2) }}</td>
                            <td class="px-3 py-2 text-right text-sm font-medium text-gray-900 dark:text-white">${{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="mb-6 flex justify-end">
            <div class="w-64 space-y-2 rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                <div class="flex justify-between text-sm"><span class="text-gray-500">Subtotal:</span><span class="dark:text-white">${{ number_format($invoice->subtotal, 2) }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Impuesto (16%):</span><span class="dark:text-white">${{ number_format($invoice->impuesto, 2) }}</span></div>
                <div class="border-t border-gray-300 pt-2 dark:border-gray-600">
                    <div class="flex justify-between text-lg font-bold"><span class="text-gray-900 dark:text-white">Total:</span><span class="text-cyan-600">${{ number_format($invoice->total, 2) }}</span></div>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        @if ($invoice->notas)
            <div class="mb-6 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                <h3 class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">Notas</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $invoice->notas }}</p>
            </div>
        @endif

        {{-- Footer --}}
        <div class="border-t border-gray-200 pt-4 text-center text-xs text-gray-500 dark:border-gray-700">
            <p>Documento generado el {{ now()->format('d/m/Y H:i') }}</p>
            <p class="mt-1">{{ $invoice->tipo_label }} N° {{ $invoice->numero }}</p>
        </div>
    </div>
</div>
