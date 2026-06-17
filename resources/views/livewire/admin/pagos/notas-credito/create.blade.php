<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Nueva Nota de Crédito</h1>
            <p class="mt-1 text-sm text-gray-500">Emite una nota de crédito contra una factura existente.</p>
        </div>
        <a href="{{ route('admin.notas-credito') }}">
            <flux:button variant="outline"><iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon> Volver</flux:button>
        </a>
    </div>

    @if (session('error'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            <iconify-icon icon="heroicons:x-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Invoice selection --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center gap-2">
                    <iconify-icon icon="heroicons:document-text-solid" class="h-5 w-5 text-cyan-600"></iconify-icon>
                    <h3 class="text-sm font-bold text-gray-900">Seleccionar Factura</h3>
                </div>
                <div class="relative">
                    <flux:input wire:model.live.debounce.300ms="invoiceSearch" wire:focus="$set('showInvoiceDropdown', true)"
                        placeholder="Buscar por número de factura..." class="sm:max-w-xs" />
                    @if ($showInvoiceDropdown)
                        <div class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                            @forelse (App\Models\Invoice::where('estado', 'emitida')->where('numero', 'like', '%' . $invoiceSearch . '%')->latest()->take(5)->get() as $inv)
                                <button type="button" wire:click="selectInvoice({{ $inv->id }})"
                                    class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-100 transition-colors">
                                    <span class="font-medium">{{ $inv->numero }}</span> — ${{ number_format($inv->total, 2) }}
                                </button>
                            @empty
                                <p class="px-3 py-2 text-sm text-gray-500">No se encontraron facturas.</p>
                            @endforelse
                        </div>
                    @endif
                </div>
            </div>

            {{-- Items --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center gap-2">
                    <iconify-icon icon="heroicons:rectangle-stack-solid" class="h-5 w-5 text-cyan-600"></iconify-icon>
                    <h3 class="text-sm font-bold text-gray-900">Items de la Factura</h3>
                </div>
                @if (empty($items))
                    <div class="flex flex-col items-center gap-2 py-8">
                        <iconify-icon icon="heroicons:rectangle-stack" class="h-10 w-10 text-gray-300"></iconify-icon>
                        <p class="text-sm text-gray-500">Selecciona una factura para cargar los items.</p>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach ($items as $index => $item)
                            <div class="flex items-center gap-3 rounded-xl p-3 transition-colors {{ $item['selected'] ? 'bg-cyan-50 ring-1 ring-inset ring-cyan-200' : 'bg-gray-50 ring-1 ring-inset ring-gray-200' }}">
                                <input type="checkbox" wire:click="toggleItem({{ $index }})" {{ $item['selected'] ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-cyan-600 focus:ring-cyan-500" />
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $item['nombre_producto'] }}</p>
                                    <p class="text-xs text-gray-500">Max: {{ $item['cantidad_max'] }} x ${{ number_format($item['precio_unitario'], 2) }}</p>
                                </div>
                                @if ($item['selected'])
                                    <flux:input wire:model.live="items.{{ $index }}.cantidad" type="number" min="1" :max="$item['cantidad_max']" class="w-20" />
                                    <span class="w-24 text-right text-sm font-bold text-gray-900">
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
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center gap-2">
                    <iconify-icon icon="heroicons:chat-bubble-left-right-solid" class="h-5 w-5 text-cyan-600"></iconify-icon>
                    <h3 class="text-sm font-bold text-gray-900">Motivo</h3>
                </div>
                <flux:textarea wire:model="motivo" rows="4" placeholder="Describe el motivo de la nota de crédito..." />
            </div>

            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center gap-2">
                    <iconify-icon icon="heroicons:calculator-solid" class="h-5 w-5 text-cyan-600"></iconify-icon>
                    <h3 class="text-sm font-bold text-gray-900">Resumen</h3>
                </div>
                <div class="flex justify-between text-base font-bold">
                    <span class="text-gray-900">Monto a acreditar</span>
                    <span class="text-red-600">${{ number_format($totalMonto, 2) }}</span>
                </div>
                <flux:button wire:click="save" variant="primary" class="mt-4 w-full justify-center bg-red-600 hover:bg-red-700">
                    Emitir Nota de Crédito
                </flux:button>
            </div>
        </div>
    </div>
</div>
