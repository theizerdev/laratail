<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Editar Orden: <span class="text-cyan-600">{{ $order->numero }}</span></h2>
            <p class="mt-1 text-sm text-gray-500">Modifica la orden de compra o gestiona su estado.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.ordenes-compra') }}" wire:navigate>
                <flux:button variant="outline">
                    <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon>
                    Volver
                </flux:button>
            </a>
            @if ($order->pendiente_recepcion)
                <a href="{{ route('admin.ordenes-compra.recibir', $order->id) }}" wire:navigate>
                    <flux:button variant="primary" class="!bg-emerald-600 hover:!bg-emerald-700">
                        <iconify-icon icon="heroicons:arrow-down-tray" class="h-4 w-4"></iconify-icon>
                        Recibir
                    </flux:button>
                </a>
            @endif
        </div>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('success') }}
        </div>
    @endif

    {{-- Status bar --}}
    @php $ec = $order->estado_color; @endphp
    <div class="mb-6 rounded-2xl bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-{{ $ec }}-50 px-3 py-1 text-xs font-semibold text-{{ $ec }}-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-{{ $ec }}-500"></span>
                    {{ $order->estado_label }}
                </span>
                @if ($order->total_pedido > 0)
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-20 rounded-full bg-gray-100">
                            <div class="h-2 rounded-full bg-{{ $ec }}-500" style="width: {{ $order->porcentaje_recepcion }}%"></div>
                        </div>
                        <span class="text-xs font-medium text-gray-500">{{ $order->porcentaje_recepcion }}% ({{ $order->total_recibido }}/{{ $order->total_pedido }})</span>
                    </div>
                @endif
            </div>
            <div class="flex gap-2">
                @if ($estado === 'borrador')
                    <flux:button wire:click="cambiarEstado('enviada')" variant="primary" size="sm" class="!bg-blue-600 hover:!bg-blue-700">
                        <iconify-icon icon="heroicons:paper-airplane" class="h-4 w-4"></iconify-icon>
                        Enviar
                    </flux:button>
                @elseif ($estado === 'enviada')
                    <flux:button wire:click="cambiarEstado('aprobada')" variant="primary" size="sm" class="!bg-indigo-600 hover:!bg-indigo-700">
                        <iconify-icon icon="heroicons:check" class="h-4 w-4"></iconify-icon>
                        Aprobar
                    </flux:button>
                    <flux:button wire:click="cambiarEstado('cancelada')" variant="outline" size="sm" class="!text-red-600 !border-red-200 hover:!bg-red-50">
                        Cancelar
                    </flux:button>
                @elseif ($estado === 'aprobada')
                    <flux:button wire:click="cambiarEstado('cancelada')" variant="outline" size="sm" class="!text-red-600 !border-red-200 hover:!bg-red-50">
                        Cancelar
                    </flux:button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Order info --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-900 uppercase tracking-wider">Información de la Orden</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:select wire:model="supplier_id" label="Proveedor">
                        @foreach ($suppliers as $s)
                            <flux:select.option value="{{ $s->id }}">{{ $s->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input type="date" wire:model="fecha" label="Fecha" />
                    <flux:input type="date" wire:model="fecha_entrega_esperada" label="Fecha Entrega Esperada" />
                </div>
            </div>

            {{-- Products --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-900 uppercase tracking-wider">Productos</h3>
                <div class="relative mb-4">
                    <flux:input wire:model.live.debounce.300ms="productSearch" wire:focus="$set('showProductDropdown', true)"
                        placeholder="Agregar producto..." icon="magnifying-glass" />
                    @if ($showProductDropdown && count($searchResults) > 0)
                        <div class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                            @foreach ($searchResults as $p)
                                <button type="button" wire:click="addItem({{ $p->id }})"
                                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-gray-50 transition-colors">
                                    <span class="font-medium text-gray-900">{{ $p->nombre }}</span>
                                    <span class="text-gray-400">({{ $p->sku }})</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="overflow-x-auto rounded-xl border border-gray-100">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Producto</th>
                                <th class="w-24 px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Pedida</th>
                                <th class="w-24 px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Recibida</th>
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
                                    <td class="px-3 py-2 text-center">
                                        <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{{ $item['cantidad_recibida'] ?? 0 }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <input type="number" wire:change="updateItemCosto({{ $index }}, $event.target.value)"
                                            value="{{ $item['costo_unitario'] }}" min="0" step="0.01"
                                            class="w-28 rounded-lg border-gray-200 text-right text-sm focus:border-cyan-500 focus:ring-cyan-500">
                                    </td>
                                    <td class="px-3 py-2 text-right text-sm font-semibold text-gray-900">${{ number_format($item['subtotal'], 2) }}</td>
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
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <flux:textarea wire:model="notas" label="Notas" rows="3" />
            </div>
        </div>

        <div>
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
                </div>
                <div class="mt-6">
                    <flux:button wire:click="save" variant="primary" class="w-full justify-center">
                        Guardar Cambios
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
</div>
