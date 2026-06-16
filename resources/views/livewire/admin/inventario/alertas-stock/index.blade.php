<div>
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Alertas de Stock Bajo</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Productos que están por debajo del stock mínimo configurado.</p>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Alertas</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_alertas']) }}</p>
        </div>
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/30">
            <p class="text-sm text-red-600 dark:text-red-400">Críticos (Stock = 0)</p>
            <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ number_format($stats['criticos']) }}</p>
        </div>
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/30">
            <p class="text-sm text-amber-600 dark:text-amber-400">Advertencia</p>
            <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ number_format($stats['advertencia']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Sin Rastreo (Stock=0)</p>
            <p class="text-2xl font-bold text-gray-600 dark:text-gray-300">{{ number_format($stats['sin_inventario']) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative flex-1 max-w-md">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar producto o SKU..."
                class="w-full rounded-lg border-gray-300 pl-10 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
             <iconify-icon icon="heroicons:magnifying-glass" class="absolute left-3 top-2.5 h-5 w-5 text-gray-400"></iconify-icon>
        </div>
        <div class="flex items-center gap-2">
            <div class="flex rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden">
                @foreach (['all' => 'Todos', 'critical' => 'Críticos', 'warning' => 'Advertencia'] as $key => $label)
                    <button wire:click="$set('severity', '{{ $key }}')"
                        class="px-3 py-1.5 text-sm font-medium transition {{ $severity === $key ? 'bg-cyan-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <select wire:model.live="sortBy" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="stock_asc">Stock: menor a mayor</option>
                <option value="stock_desc">Stock: mayor a menor</option>
                <option value="nombre">Nombre A-Z</option>
            </select>
        </div>
    </div>

    {{-- Alert Cards --}}
    <div class="space-y-3">
        @forelse ($products as $product)
            @php
                $isCritical = $product->stock === 0;
                $borderColor = $isCritical ? 'border-red-300 dark:border-red-700' : 'border-amber-300 dark:border-amber-700';
                $bgColor = $isCritical ? 'bg-red-50 dark:bg-red-900/20' : 'bg-amber-50 dark:bg-amber-900/20';
                $stockPercent = $product->stock_minimo > 0 ? ($product->stock / $product->stock_minimo) * 100 : 0;
            @endphp
            <div class="rounded-lg border-2 {{ $borderColor }} {{ $bgColor }} p-4">
                <div class="flex items-center gap-4">
                    {{-- Product image --}}
                    @if ($product->imagen_principal)
                        <img src="{{ Storage::url($product->imagen_principal) }}" alt="{{ $product->nombre }}" class="h-14 w-14 rounded-lg object-cover">
                    @else
                        <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-600">
                           <iconify-icon icon="heroicons:photo" class="h-6 w-6 text-gray-400" /></iconify-icon>
                        </div>
                    @endif

                    {{-- Product info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-gray-900 dark:text-white truncate">{{ $product->nombre }}</h3>
                            @if ($isCritical)
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-700 dark:bg-red-900/50 dark:text-red-400">
                                    <iconify-icon icon="heroicons:exclamation-triangle" class="h-6 w-6 text-gray-400" /></iconify-icon> CRÍTICO
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/50 dark:text-amber-400">
                                    STOCK BAJO
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            SKU: {{ $product->sku }}
                            @if ($product->category) | {{ $product->category->nombre }} @endif
                            @if ($product->brand) | {{ $product->brand->nombre }} @endif
                        </p>
                    </div>

                    {{-- Stock info --}}
                    <div class="text-right">
                        <div class="flex items-center gap-4">
                            <div class="text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Stock</p>
                                <p class="text-xl font-bold {{ $isCritical ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }}">{{ number_format($product->stock) }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Mínimo</p>
                                <p class="text-xl font-bold text-gray-600 dark:text-gray-300">{{ number_format($product->stock_minimo) }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Faltante</p>
                                <p class="text-xl font-bold text-gray-600 dark:text-gray-300">{{ number_format(max(0, $product->stock_minimo - $product->stock)) }}</p>
                            </div>
                        </div>
                        {{-- Progress bar --}}
                        <div class="mt-1 h-2 w-48 rounded-full bg-gray-200 dark:bg-gray-600">
                            <div class="h-2 rounded-full {{ $isCritical ? 'bg-red-500' : ($stockPercent < 50 ? 'bg-amber-500' : 'bg-yellow-400') }}"
                                style="width: {{ min(100, $stockPercent) }}%"></div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-col gap-1">
                        <a href="{{ route('admin.kardex') }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-white dark:border-gray-600 dark:text-gray-300">
                            <iconify-icon icon="heroicons:chart-bar" class="h-3.5 w-3.5" /></iconify-icon> Kardex
                        </a>
                        <a href="{{ route('admin.ordenes-compra.create') }}" class="inline-flex items-center gap-1 rounded-lg bg-cyan-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-cyan-700">
                            <iconify-icon icon="heroicons:shopping-cart" class="h-3.5 w-3.5" /></iconify-icon> Crear OC
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-lg border-2 border-dashed border-emerald-300 p-12 text-center dark:border-emerald-700">
                <iconify-icon icon="heroicons:check-circle" class="mx-auto h-12 w-12 text-emerald-500"></iconify-icon>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">¡Todo en orden!</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No hay productos con stock por debajo del mínimo.</p>
            </div>
        @endforelse
    </div>

    @if ($products->hasPages())
        <div class="mt-4">{{ $products->links() }}</div>
    @endif
</div>
