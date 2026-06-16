<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Nueva Nota de Crédito</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Emite una nota de crédito contra una factura existente.</p>
        </div>
        <a href="{{ route('admin.notas-credito') }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
             <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon> Volver
        </a>
    </div>

    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-400">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Invoice selection --}}
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Seleccionar Factura</h3>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="invoiceSearch" wire:focus="$set('showInvoiceDropdown', true)"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Buscar por número de factura..." />
                    @if ($showInvoiceDropdown)
                        <div class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700">
                            @forelse (App\Models\Invoice::where('estado', 'emitida')->where('numero', 'like', '%' . $invoiceSearch . '%')->latest()->take(5)->get() as $inv)
                                <button type="button" wire:click="selectInvoice({{ $inv->id }})"
                                    class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-white">
                                    {{ $inv->numero }} — ${{ number_format($inv->total, 2) }}
                                </button>
                            @empty
                                <p class="px-3 py-2 text-sm text-gray-500">No se encontraron facturas.</p>
                            @endforelse
                        </div>
                    @endif
                </div>
            </div>

            {{-- Items --}}
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Items de la Factura</h3>
                @if (empty($items))
                    <p class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">Selecciona una factura para cargar los items.</p>
                @else
                    <div class="space-y-2">
                        @foreach ($items as $index => $item)
                            <div class="flex items-center gap-3 rounded-lg border p-3 {{ $item['selected'] ? 'border-cyan-300 bg-cyan-50 dark:border-cyan-700 dark:bg-cyan-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                                <input type="checkbox" wire:click="toggleItem({{ $index }})" {{ $item['selected'] ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-cyan-600" />
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item['nombre_producto'] }}</p>
                                    <p class="text-xs text-gray-500">Max: {{ $item['cantidad_max'] }} x ${{ number_format($item['precio_unitario'], 2) }}</p>
                                </div>
                                @if ($item['selected'])
                                    <input type="number" wire:model.live="items.{{ $index }}.cantidad" min="1" max="{{ $item['cantidad_max'] }}"
                                        class="w-20 rounded border border-gray-300 px-2 py-1 text-sm text-center dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                    <span class="w-24 text-right text-sm font-medium text-gray-900 dark:text-white">
                                        ${{ number_format($item['cantidad'] * $item['precio_unitario'], 2) }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Motivo</h3>
                <textarea wire:model="motivo" rows="4"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="Describe el motivo de la nota de crédito..."></textarea>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Resumen</h3>
                <div class="flex justify-between text-base font-bold">
                    <span class="text-gray-900 dark:text-white">Monto a acreditar:</span>
                    <span class="text-red-600">${{ number_format($totalMonto, 2) }}</span>
                </div>
                <button wire:click="save" class="mt-4 w-full rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    Emitir Nota de Crédito
                </button>
            </div>
        </div>
    </div>
</div>
