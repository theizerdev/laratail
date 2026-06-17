<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Recibir Mercancía</h2>
            <p class="mt-1 text-sm text-gray-500">
                Orden: <span class="font-semibold text-cyan-600">{{ $order->numero }}</span> — Proveedor: {{ $order->supplier?->nombre }}
            </p>
        </div>
        <a href="{{ route('admin.ordenes-compra.edit', $order->id) }}" wire:navigate>
            <flux:button variant="outline">
                <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon>
                Volver
            </flux:button>
        </a>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            <iconify-icon icon="heroicons:x-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Items to receive --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Productos a Recibir</h3>
                    <div class="flex gap-2">
                        <flux:button wire:click="recibirTodo" variant="outline" size="sm" class="!text-emerald-600 !border-emerald-200 hover:!bg-emerald-50">
                            Recibir Todo
                        </flux:button>
                        <flux:button wire:click="limpiarTodo" variant="ghost" size="sm">
                            Limpiar
                        </flux:button>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-100">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Producto</th>
                                <th class="w-20 px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Pedida</th>
                                <th class="w-20 px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Recib. Anterior</th>
                                <th class="w-20 px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Pendiente</th>
                                <th class="w-28 px-3 py-2 text-center text-xs font-semibold text-emerald-600 uppercase">Recibir Ahora</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($recepcionItems as $index => $item)
                                <tr class="{{ $item['cantidad_recibir'] > 0 ? 'bg-emerald-50/50' : 'hover:bg-gray-50' }}">
                                    <td class="px-3 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $item['nombre_producto'] }}</div>
                                        <div class="text-xs text-gray-400">{{ $item['sku'] }}</div>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="text-sm text-gray-600">{{ $item['cantidad_pedida'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="text-sm text-gray-600">{{ $item['cantidad_recibida_previa'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="rounded-md px-2 py-0.5 text-xs font-semibold {{ $item['pendiente'] > 0 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">
                                            {{ $item['pendiente'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <input type="number" wire:model.live="recepcionItems.{{ $index }}.cantidad_recibir"
                                            min="0" max="{{ $item['pendiente'] }}"
                                            class="w-24 rounded-lg border-gray-200 text-center text-sm font-semibold focus:border-emerald-500 focus:ring-emerald-500">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Notes --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <flux:textarea wire:model="notas" label="Notas de Recepción" placeholder="Observaciones sobre la recepción..." rows="3" />
            </div>
        </div>

        <div>
            <div class="sticky top-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-emerald-100">
                <h3 class="mb-4 text-sm font-semibold text-gray-900 uppercase tracking-wider">Resumen de Recepción</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Unidades a recibir</span>
                        <span class="text-2xl font-bold text-emerald-600">{{ $totalRecibir }}</span>
                    </div>
                    <div class="border-t border-gray-100 pt-3">
                        <div class="flex items-start gap-2 rounded-lg bg-emerald-50 p-3">
                            <iconify-icon icon="heroicons:information-circle-solid" class="h-5 w-5 text-emerald-500 shrink-0 mt-0.5"></iconify-icon>
                            <p class="text-xs text-emerald-700">
                                Se crearán movimientos de inventario de tipo <strong>Entrada</strong> para cada producto recibido.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <flux:button wire:click="confirmarRecepcion"
                        wire:confirm="¿Confirmas la recepción de {{ $totalRecibir }} unidad(es)? Se actualizará el stock automáticamente."
                        variant="primary" disabled="{{ $totalRecibir <= 0 }}"
                        class="w-full justify-center !bg-emerald-600 hover:!bg-emerald-700">
                        <iconify-icon icon="heroicons:check-circle" class="h-5 w-4 mr-1"></iconify-icon>
                        Confirmar Recepción
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
</div>
