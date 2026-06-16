<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Nueva Factura</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Emite una factura o proforma a partir de un pedido.</p>
        </div>
        <a href="{{ route('admin.facturas') }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
             <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon> Volver
        </a>
    </div>

    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-400">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main content --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Order selection --}}
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Seleccionar Pedido</h3>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="orderSearch" wire:focus="$set('showOrderDropdown', true)"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Buscar por número de orden..." />
                    @if ($showOrderDropdown)
                        <div class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700">
                            @forelse (App\Models\Order::where('numero', 'like', '%' . $orderSearch . '%')->latest()->take(5)->get() as $ord)
                                <button type="button" wire:click="selectOrder({{ $ord->id }})"
                                    class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-white">
                                    {{ $ord->numero }} — ${{ number_format($ord->total, 2) }}
                                    <span class="text-xs text-gray-500">({{ $ord->customer?->nombre ?? 'S/C' }})</span>
                                </button>
                            @empty
                                <p class="px-3 py-2 text-sm text-gray-500">No se encontraron órdenes.</p>
                            @endforelse
                        </div>
                    @endif
                </div>
            </div>

            {{-- Items --}}
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Items de la Factura</h3>
                @if (empty($items))
                    <p class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">Selecciona un pedido para cargar los items.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Producto</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium uppercase text-gray-500">Cant.</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">Precio</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">Desc.</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($items as $item)
                                    <tr>
                                        <td class="px-3 py-2 text-sm dark:text-white">{{ $item['nombre_producto'] }}<br><span class="text-xs text-gray-500">{{ $item['sku'] }}</span></td>
                                        <td class="px-3 py-2 text-center text-sm dark:text-white">{{ $item['cantidad'] }}</td>
                                        <td class="px-3 py-2 text-right text-sm dark:text-white">${{ number_format($item['precio_unitario'], 2) }}</td>
                                        <td class="px-3 py-2 text-right text-sm dark:text-white">${{ number_format($item['descuento'], 2) }}</td>
                                        <td class="px-3 py-2 text-right text-sm font-medium dark:text-white">${{ number_format($item['subtotal'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Configuración</h3>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                        <select wire:model="tipo" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="factura">Factura</option>
                            <option value="proforma">Proforma</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Serie</label>
                        <input type="text" wire:model="serie" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Opcional" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">N° Control</label>
                        <input type="text" wire:model="numero_control" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Opcional" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Notas</label>
                        <textarea wire:model="notas" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Notas adicionales..."></textarea>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Totales</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Subtotal:</span><span class="dark:text-white">${{ number_format($subtotal, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Impuesto (16%):</span><span class="dark:text-white">${{ number_format($impuesto, 2) }}</span></div>
                    <div class="border-t border-gray-200 pt-2 dark:border-gray-700">
                        <div class="flex justify-between text-base font-bold"><span class="text-gray-900 dark:text-white">Total:</span><span class="text-cyan-600">${{ number_format($total, 2) }}</span></div>
                    </div>
                </div>
                <button wire:click="save" class="mt-4 w-full rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700">
                    Emitir Factura
                </button>
            </div>
        </div>
    </div>
</div>
