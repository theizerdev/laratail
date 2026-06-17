<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Nueva Orden de Compra</h2>
            <p class="mt-1 text-sm text-gray-500">Crea una orden de compra para un proveedor.</p>
        </div>
        <a href="{{ route('admin.ordenes-compra') }}" wire:navigate>
            <flux:button variant="outline">
                <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon>
                Volver
            </flux:button>
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main content --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Order details --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-900 uppercase tracking-wider">Información de la Orden</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:select wire:model="supplier_id" label="Proveedor *" placeholder="Seleccionar proveedor...">
                        @foreach ($suppliers as $s)
                            <flux:select.option value="{{ $s->id }}">{{ $s->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input type="date" wire:model="fecha" label="Fecha *" />
                    <flux:input type="date" wire:model="fecha_entrega_esperada" label="Fecha Entrega Esperada" />
                </div>
            </div>

            {{-- Products --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-900 uppercase tracking-wider">Productos</h3>

                {{-- Product search --}}
                <div class="relative mb-4">
                    <flux:input wire:model.live.debounce.300ms="productSearch" wire:focus="$set('showProductDropdown', true)"
                        placeholder="Buscar producto por nombre o SKU..." icon="magnifying-glass" />
                    @if ($showProductDropdown && count($searchResults) > 0)
                        <div class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                            @foreach ($searchResults as $p)
                                <button type="button" wire:click="addItem({{ $p->id }})"
                                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-gray-50 transition-colors">
                                    <span class="font-medium text-gray-900">{{ $p->nombre }}</span>
                                    <span class="text-gray-400">({{ $p->sku }})</span>
                                    <span class="ml-auto text-xs text-gray-500">Stock: {{ $p->stock }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Items table --}}
                @if (count($items) > 0)
                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Producto</th>
                                    <th class="w-24 px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Cantidad</th>
                                    <th class="w-32 px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Costo Unit.</th>
                                    <th class="w-32 px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Subtotal</th>
                                    <th class="w-10 px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($items as $index => $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2">
                                            <div class="text-sm font-medium text-gray-900">{{ $item['nombre_producto'] }}</div>
                                            <div class="text-xs text-gray-400">{{ $item['sku'] }}</div>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <input type="number" wire:change="updateItemCantidad({{ $index }}, $event.target.value)"
                                                value="{{ $item['cantidad_pedida'] }}" min="1"
                                                class="w-20 rounded-lg border-gray-200 text-center text-sm focus:border-cyan-500 focus:ring-cyan-500">
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <input type="number" wire:change="updateItemCosto({{ $index }}, $event.target.value)"
                                                value="{{ $item['costo_unitario'] }}" min="0" step="0.01"
                                                class="w-28 rounded-lg border-gray-200 text-right text-sm focus:border-cyan-500 focus:ring-cyan-500">
                                        </td>
                                        <td class="px-3 py-2 text-right text-sm font-semibold text-gray-900">
                                            ${{ number_format($item['subtotal'], 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button wire:click="removeItem({{ $index }})" class="text-gray-400 hover:text-red-500 transition-colors">
                                                <iconify-icon icon="heroicons:trash" class="h-4 w-4"></iconify-icon>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="rounded-xl border-2 border-dashed border-gray-200 p-8 text-center">
                        <div class="flex h-12 w-12 mx-auto items-center justify-center rounded-full bg-gray-100">
                            <iconify-icon icon="heroicons:shopping-cart" class="h-6 w-6 text-gray-400"></iconify-icon>
                        </div>
                        <p class="mt-2 text-sm font-medium text-gray-900">Sin productos</p>
                        <p class="text-xs text-gray-500">Busca y agrega productos a la orden de compra</p>
                    </div>
                @endif
            </div>

            {{-- Notes --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <flux:textarea wire:model="notas" label="Notas" placeholder="Notas adicionales..." rows="3" />
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div class="sticky top-6 rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-900 uppercase tracking-wider">Resumen</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="font-medium text-gray-900">${{ number_format($this->getSubtotal(), 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Impuesto ({{ $impuesto_rate }}%)</span>
                        <span class="font-medium text-gray-900">${{ number_format($this->getImpuesto(), 2) }}</span>
                    </div>
                    <div class="border-t border-gray-100 pt-3">
                        <div class="flex justify-between">
                            <span class="text-base font-bold text-gray-900">Total</span>
                            <span class="text-lg font-bold text-cyan-600">${{ number_format($this->getTotal(), 2) }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-400">
                        <span>Productos</span>
                        <span>{{ count($items) }} ítem(s)</span>
                    </div>
                </div>

                <div class="mt-6 space-y-2">
                    <flux:button wire:click="save('borrador')" variant="outline" class="w-full justify-center">
                        Guardar como Borrador
                    </flux:button>
                    <flux:button wire:click="save('enviada')" variant="primary"
                        disabled="{{ count($items) === 0 || !$supplier_id }}" class="w-full justify-center">
                        Crear y Enviar
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
</div>
