<div>
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Kardex - Historial de Stock</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Consulta el historial completo de movimientos por producto.</p>
    </div>

    {{-- Filters --}}
    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
            <div class="relative flex-1 max-w-md">
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Producto</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="productSearch" wire:focus="$set('showProductDropdown', true)"
                        placeholder="Buscar por nombre o SKU..."
                        class="w-full rounded-lg border-gray-300 pl-10 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                     <iconify-icon icon="heroicons:magnifying-glass" class="absolute left-3 top-2.5 h-5 w-5 text-gray-400"></iconify-icon>
                    @if ($product_id)
                        <button wire:click="clearProduct" class="absolute right-2 top-2 text-gray-400 hover:text-gray-600">
                             <iconify-icon icon="heroicons:x-mark" class="h-4 w-4"></iconify-icon>
                        </button>
                    @endif
                </div>
                @if ($showProductDropdown && count($searchResults) > 0)
                    <div class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700">
                        @foreach ($searchResults as $p)
                            <button type="button" wire:click="selectProduct({{ $p->id }})" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $p->nombre }}</span>
                                <span class="text-gray-400">({{ $p->sku }})</span>
                                <span class="ml-auto text-xs text-gray-500">Stock: {{ $p->stock }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Desde</label>
                <input type="date" wire:model.live="dateFrom" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Hasta</label>
                <input type="date" wire:model.live="dateTo" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>
        </div>
    </div>

    @if ($product)
        {{-- Summary Cards --}}
        <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/30">
                <p class="text-sm text-emerald-600 dark:text-emerald-400">Total Entradas</p>
                <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($summary['total_entradas']) }}</p>
            </div>
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/30">
                <p class="text-sm text-red-600 dark:text-red-400">Total Salidas</p>
                <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ number_format($summary['total_salidas']) }}</p>
            </div>
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/30">
                <p class="text-sm text-amber-600 dark:text-amber-400">Ajustes</p>
                <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ number_format($summary['total_ajustes']) }}</p>
            </div>
            <div class="rounded-lg border border-cyan-200 bg-cyan-50 p-4 dark:border-cyan-800 dark:bg-cyan-900/30">
                <p class="text-sm text-cyan-600 dark:text-cyan-400">Stock Actual</p>
                <p class="text-2xl font-bold text-cyan-700 dark:text-cyan-300">{{ number_format($summary['stock_actual']) }}</p>
            </div>
        </div>

        {{-- Product Info --}}
        <div class="mb-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-4">
                @if ($product->imagen_principal)
                    <img src="{{ Storage::url($product->imagen_principal) }}" alt="{{ $product->nombre }}" class="h-12 w-12 rounded-lg object-cover">
                @else
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-600">
                        <iconify-icon icon="heroicons:photo" class="h-6 w-6 text-gray-400"></iconify-icon>
                    </div>
                @endif
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $product->nombre }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">SKU: {{ $product->sku }} | Stock mínimo: {{ $product->stock_minimo }}</p>
                </div>
                <div class="ml-auto text-right text-sm text-gray-500 dark:text-gray-400">
                    @if ($summary['primer_movimiento'])
                        <p>Desde: {{ $summary['primer_movimiento']->format('d/m/Y') }}</p>
                    @endif
                    @if ($summary['ultimo_movimiento'])
                        <p>Hasta: {{ $summary['ultimo_movimiento']->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Movements Table --}}
        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Tipo</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Cantidad</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Stock Ant.</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Stock Nuevo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Variante</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Referencia</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Motivo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($movements as $mov)
                        @php
                            $color = match($mov->tipo) {
                                'entrada' => 'emerald', 'salida' => 'red', 'ajuste' => 'amber', 'transferencia' => 'blue', default => 'gray'
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-{{ $color }}-100 px-2.5 py-0.5 text-xs font-medium text-{{ $color }}-800 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-400">
                                    {{ ucfirst($mov->tipo) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-center text-sm font-semibold text-{{ $color }}-600 dark:text-{{ $color }}-400">
                                @if ($mov->tipo === 'salida')-@elseif($mov->tipo === 'entrada')+@endif{{ number_format($mov->cantidad) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-300">{{ number_format($mov->stock_anterior) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($mov->stock_nuevo) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $mov->variant?->combinacion ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $mov->referencia ?? '-' }}</td>
                            <td class="max-w-xs truncate px-4 py-3 text-sm text-gray-600 dark:text-gray-300" title="{{ $mov->motivo }}">{{ $mov->motivo ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $mov->user?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No hay movimientos registrados para este producto en el rango seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="rounded-lg border-2 border-dashed border-gray-300 p-12 text-center dark:border-gray-600">
            <iconify-icon icon="heroicons:cube" class="h-6 w-6 text-gray-400" /></iconify-icon>
            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Selecciona un producto</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Busca y selecciona un producto para ver su historial de movimientos (Kardex).</p>
        </div>
    @endif
</div>
