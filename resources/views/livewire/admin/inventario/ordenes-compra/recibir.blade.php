<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Recibir Mercancía</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Orden: <span class="font-semibold text-cyan-600 dark:text-cyan-400">{{ $order->numero }}</span> — Proveedor: {{ $order->supplier?->nombre }}</p>
        </div>
        <a href="{{ route('admin.ordenes-compra.edit', $order->id) }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
             <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon> Volver
        </a>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-400">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Items to receive --}}
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Productos a Recibir</h2>
                    <div class="flex gap-2">
                        <button wire:click="recibirTodo" class="rounded-lg border border-emerald-300 px-3 py-1 text-xs font-medium text-emerald-600 hover:bg-emerald-50 dark:border-emerald-700 dark:text-emerald-400">
                            Recibir Todo
                        </button>
                        <button wire:click="limpiarTodo" class="rounded-lg border border-gray-300 px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400">
                            Limpiar
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Producto</th>
                                <th class="w-20 px-3 py-2 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Pedida</th>
                                <th class="w-20 px-3 py-2 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Recib. Anterior</th>
                                <th class="w-20 px-3 py-2 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Pendiente</th>
                                <th class="w-28 px-3 py-2 text-center text-xs font-semibold uppercase text-emerald-500 dark:text-emerald-400">Recibir Ahora</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($recepcionItems as $index => $item)
                                <tr class="{{ $item['cantidad_recibir'] > 0 ? 'bg-emerald-50 dark:bg-emerald-900/10' : '' }}">
                                    <td class="px-3 py-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $item['nombre_producto'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $item['sku'] }}</div>
                                    </td>
                                    <td class="px-3 py-3 text-center text-sm text-gray-600 dark:text-gray-300">{{ $item['cantidad_pedida'] }}</td>
                                    <td class="px-3 py-3 text-center text-sm text-gray-600 dark:text-gray-300">{{ $item['cantidad_recibida_previa'] }}</td>
                                    <td class="px-3 py-3 text-center text-sm font-semibold {{ $item['pendiente'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600' }}">
                                        {{ $item['pendiente'] }}
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <input type="number" wire:model.live="recepcionItems.{{ $index }}.cantidad_recibir"
                                            min="0" max="{{ $item['pendiente'] }}"
                                            class="w-24 rounded-lg border-gray-300 text-center text-sm font-semibold dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Notes --}}
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Notas de Recepción</label>
                <textarea wire:model="notas" rows="3" placeholder="Observaciones sobre la recepción..."
                    class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
            </div>
        </div>

        <div>
            <div class="sticky top-6 rounded-lg border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-800 dark:bg-emerald-900/20">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Resumen de Recepción</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Unidades a recibir</span>
                        <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $totalRecibir }}</span>
                    </div>
                    <div class="border-t border-emerald-200 pt-3 dark:border-emerald-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Se crearán movimientos de inventario de tipo <strong>Entrada</strong> para cada producto recibido.
                        </p>
                    </div>
                </div>
                <div class="mt-6">
                    <button wire:click="confirmarRecepcion" wire:confirm="¿Confirmas la recepción de {{ $totalRecibir }} unidad(es)? Se actualizará el stock automáticamente."
                        disabled="{{ $totalRecibir <= 0 }}"
                        class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <x-heroicons:check-circle class="inline h-5 w-5 mr-1" /> Confirmar Recepción
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
