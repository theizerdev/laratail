<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Orden: {{ $order->numero }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Modifica la orden de compra o gestiona su estado.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.ordenes-compra') }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
                 <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon> Volver
            </a>
            @if ($order->pendiente_recepcion)
                <a href="{{ route('admin.ordenes-compra.recibir', $order->id) }}" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    <iconify-icon icon="heroicons:arrow-down-tray" class="h-4 w-4" /> Recibir 
                </a>
            @endif
        </div>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">{{ session('success') }}</div>
    @endif

    {{-- Status bar --}}
    @php $ec = $order->estado_color; @endphp
    <div class="mb-6 rounded-lg border border-{{ $ec }}-200 bg-{{ $ec }}-50 p-4 dark:border-{{ $ec }}-800 dark:bg-{{ $ec }}-900/20">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center rounded-full bg-{{ $ec }}-100 px-3 py-1 text-sm font-semibold text-{{ $ec }}-800 dark:bg-{{ $ec }}-900/30 dark:text-{{ $ec }}-400">
                    {{ $order->estado_label }}
                </span>
                @if ($order->total_pedido > 0)
                    <span class="text-sm text-gray-500 dark:text-gray-400">Recepción: {{ $order->porcentaje_recepcion }}% ({{ $order->total_recibido }}/{{ $order->total_pedido }})</span>
                @endif
            </div>
            <div class="flex gap-2">
                @if ($estado === 'borrador')
                    <button wire:click="cambiarEstado('enviada')" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700">Enviar</button>
                @elseif ($estado === 'enviada')
                    <button wire:click="cambiarEstado('aprobada')" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">Aprobar</button>
                    <button wire:click="cambiarEstado('cancelada')" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Cancelar</button>
                @elseif ($estado === 'aprobada')
                    <button wire:click="cambiarEstado('cancelada')" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Cancelar</button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Order info --}}
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Información de la Orden</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Proveedor</label>
                        <select wire:model="supplier_id" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha</label>
                        <input type="date" wire:model="fecha" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha Entrega Esperada</label>
                        <input type="date" wire:model="fecha_entrega_esperada" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            </div>

            {{-- Products --}}
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Productos</h2>
                <div class="relative mb-4">
                    <input type="text" wire:model.live.debounce.300ms="productSearch" wire:focus="$set('showProductDropdown', true)"
                        placeholder="Agregar producto..."
                        class="w-full rounded-lg border-gray-300 pl-10 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                     <iconify-icon icon="heroicons:magnifying-glass" class="absolute left-3 top-2.5 h-5 w-5 text-gray-400"></iconify-icon>
                    @if ($showProductDropdown && count($searchResults) > 0)
                        <div class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700">
                            @foreach ($searchResults as $p)
                                <button type="button" wire:click="addItem({{ $p->id }})" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $p->nombre }}</span>
                                    <span class="text-gray-400">({{ $p->sku }})</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Producto</th>
                                <th class="w-24 px-3 py-2 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Pedida</th>
                                <th class="w-24 px-3 py-2 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Recibida</th>
                                <th class="w-32 px-3 py-2 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Costo Unit.</th>
                                <th class="w-32 px-3 py-2 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Subtotal</th>
                                <th class="w-10 px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($items as $index => $item)
                                <tr>
                                    <td class="px-3 py-2">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $item['nombre_producto'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $item['sku'] }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <input type="number" wire:change="updateItemCantidad({{ $index }}, $event.target.value)"
                                            value="{{ $item['cantidad_pedida'] }}" min="1"
                                            class="w-20 rounded border-gray-300 text-center text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    </td>
                                    <td class="px-3 py-2 text-center text-sm text-gray-600 dark:text-gray-300">{{ $item['cantidad_recibida'] ?? 0 }}</td>
                                    <td class="px-3 py-2">
                                        <input type="number" wire:change="updateItemCosto({{ $index }}, $event.target.value)"
                                            value="{{ $item['costo_unitario'] }}" min="0" step="0.01"
                                            class="w-28 rounded border-gray-300 text-right text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    </td>
                                    <td class="px-3 py-2 text-right text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($item['subtotal'], 2) }}</td>
                                    <td class="px-3 py-2 text-center">
                                        <button wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700">
                                            <iconify-icon icon="heroicons:trash" class="h-4 w-4"></iconify-icon>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Notas</label>
                <textarea wire:model="notas" rows="3" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
            </div>
        </div>

        <div>
            <div class="sticky top-6 rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Resumen</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Subtotal</span><span class="text-gray-900 dark:text-white">${{ number_format($this->getSubtotal(), 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Impuesto ({{ $impuesto_rate }}%)</span><span class="text-gray-900 dark:text-white">${{ number_format($this->getImpuesto(), 2) }}</span></div>
                    <div class="border-t border-gray-200 pt-2 dark:border-gray-700">
                        <div class="flex justify-between text-base font-bold"><span class="text-gray-900 dark:text-white">Total</span><span class="text-cyan-600 dark:text-cyan-400">${{ number_format($this->getTotal(), 2) }}</span></div>
                    </div>
                </div>
                <div class="mt-6">
                    <button wire:click="save" class="w-full rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700">
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
