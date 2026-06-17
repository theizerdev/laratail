<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Nueva Factura</h1>
            <p class="mt-1 text-sm text-gray-500">Emite una factura o proforma a partir de un pedido.</p>
        </div>
        <a href="{{ route('admin.facturas') }}">
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
        {{-- Main content --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Order selection --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center gap-2">
                    <iconify-icon icon="heroicons:shopping-cart-solid" class="h-5 w-5 text-cyan-600"></iconify-icon>
                    <h3 class="text-sm font-bold text-gray-900">Seleccionar Pedido</h3>
                </div>
                <div class="relative">
                    <flux:input wire:model.live.debounce.300ms="orderSearch" wire:focus="$set('showOrderDropdown', true)"
                        placeholder="Buscar por número de orden..."/>
                    @if ($showOrderDropdown)
                        <div class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                            @forelse (App\Models\Order::where('numero', 'like', '%' . $orderSearch . '%')->latest()->take(5)->get() as $ord)
                                <button type="button" wire:click="selectOrder({{ $ord->id }})"
                                    class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-100 transition-colors">
                                    <span class="font-medium">{{ $ord->numero }}</span> — ${{ number_format($ord->total, 2) }}
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
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center gap-2">
                    <iconify-icon icon="heroicons:rectangle-stack-solid" class="h-5 w-5 text-cyan-600"></iconify-icon>
                    <h3 class="text-sm font-bold text-gray-900">Items de la Factura</h3>
                </div>
                @if (empty($items))
                    <div class="flex flex-col items-center gap-2 py-8">
                        <iconify-icon icon="heroicons:rectangle-stack" class="h-10 w-10 text-gray-300"></iconify-icon>
                        <p class="text-sm text-gray-500">Selecciona un pedido para cargar los items.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Producto</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Cant.</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Precio</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Desc.</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($items as $item)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-3 py-2 text-sm text-gray-900">
                                            {{ $item['nombre_producto'] }}
                                            <br><span class="text-xs text-gray-500">{{ $item['sku'] }}</span>
                                        </td>
                                        <td class="px-3 py-2 text-center text-sm text-gray-900">{{ $item['cantidad'] }}</td>
                                        <td class="px-3 py-2 text-right text-sm text-gray-900">${{ number_format($item['precio_unitario'], 2) }}</td>
                                        <td class="px-3 py-2 text-right text-sm text-gray-900">${{ number_format($item['descuento'], 2) }}</td>
                                        <td class="px-3 py-2 text-right text-sm font-bold text-gray-900">${{ number_format($item['subtotal'], 2) }}</td>
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
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center gap-2">
                    <iconify-icon icon="heroicons:cog-6-tooth-solid" class="h-5 w-5 text-cyan-600"></iconify-icon>
                    <h3 class="text-sm font-bold text-gray-900">Configuración</h3>
                </div>
                <div class="space-y-4">
                    <div>
                        <flux:label>Tipo</flux:label>
                        <flux:select wire:model="tipo">
                            <flux:select.option value="factura">Factura</flux:select.option>
                            <flux:select.option value="proforma">Proforma</flux:select.option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:label>Serie</flux:label>
                        <flux:input wire:model="serie" placeholder="Opcional" />
                    </div>
                    <div>
                        <flux:label>N° Control</flux:label>
                        <flux:input wire:model="numero_control" placeholder="Opcional" />
                    </div>
                    <div>
                        <flux:label>Notas</flux:label>
                        <flux:textarea wire:model="notas" rows="3" placeholder="Notas adicionales..." />
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center gap-2">
                    <iconify-icon icon="heroicons:calculator-solid" class="h-5 w-5 text-cyan-600"></iconify-icon>
                    <h3 class="text-sm font-bold text-gray-900">Totales</h3>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="font-medium">${{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Impuesto (16%)</span>
                        <span class="font-medium">${{ number_format($impuesto, 2) }}</span>
                    </div>
                    <div class="border-t border-gray-200 pt-3">
                        <div class="flex justify-between text-base font-bold">
                            <span class="text-gray-900">Total</span>
                            <span class="text-cyan-600">${{ number_format($total, 2) }}</span>
                        </div>
                    </div>
                </div>
                <flux:button wire:click="save" variant="primary" class="mt-4 w-full justify-center">
                    Emitir Factura
                </flux:button>
            </div>
        </div>
    </div>
</div>
