<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Alertas de Stock Bajo</h2>
            <p class="mt-1 text-sm text-gray-500">Productos que están por debajo del stock mínimo configurado.</p>
        </div>
        <a href="{{ route('admin.ordenes-compra.create') }}" wire:navigate>
            <flux:button variant="primary">
                <iconify-icon icon="heroicons:shopping-cart" class="h-4 w-4"></iconify-icon>
                Crear Orden de Compra
            </flux:button>
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Total Alertas</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                    <iconify-icon icon="heroicons:exclamation-triangle-solid" class="h-4 w-4 text-indigo-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['total_alertas']) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Críticos (Stock = 0)</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50">
                    <iconify-icon icon="heroicons:x-circle-solid" class="h-4 w-4 text-red-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-red-600">{{ number_format($stats['criticos']) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Advertencia</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50">
                    <iconify-icon icon="heroicons:exclamation-circle-solid" class="h-4 w-4 text-amber-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($stats['advertencia']) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Sin Rastreo (Stock=0)</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100">
                    <iconify-icon icon="heroicons:question-mark-circle-solid" class="h-4 w-4 text-gray-500"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-600">{{ number_format($stats['sin_inventario']) }}</p>
        </div>
    </div>

    {{-- Search + Filters --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar producto o SKU..." icon="magnifying-glass" />
        </div>
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-1 rounded-xl bg-white p-1 shadow-sm overflow-x-auto">
                @foreach (['all' => 'Todos', 'critical' => 'Críticos', 'warning' => 'Advertencia'] as $key => $label)
                    <button wire:click="$set('severity', '{{ $key }}')"
                        @class(['rounded-lg px-3 py-1.5 text-xs font-semibold transition whitespace-nowrap', $severity === $key ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-900'])>
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <flux:select wire:model.live="sortBy" class="w-auto">
                <option value="stock_asc">Stock: menor a mayor</option>
                <option value="stock_desc">Stock: mayor a menor</option>
                <option value="nombre">Nombre A-Z</option>
            </flux:select>
        </div>
    </div>

    {{-- Alert Cards --}}
    <div class="space-y-3">
        @forelse ($products as $product)
            @php
                $isCritical = $product->stock === 0;
                $borderColor = $isCritical ? 'border-red-200' : 'border-amber-200';
                $bgColor = $isCritical ? 'bg-red-50/50' : 'bg-amber-50/50';
                $stockPercent = $product->stock_minimo > 0 ? ($product->stock / $product->stock_minimo) * 100 : 0;
            @endphp
            <div class="rounded-2xl border {{ $borderColor }} {{ $bgColor }} p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    {{-- Product image --}}
                    @if ($product->imagen_principal)
                        <img src="{{ $product->imagen_principal_url }}" alt="{{ $product->nombre }}" class="h-14 w-14 rounded-xl object-cover">
                    @else
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gray-100">
                            <iconify-icon icon="heroicons:photo" class="h-6 w-6 text-gray-400"></iconify-icon>
                        </div>
                    @endif

                    {{-- Product info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-gray-900 truncate">{{ $product->nombre }}</h3>
                            @if ($isCritical)
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-700">
                                    <iconify-icon icon="heroicons:exclamation-triangle-solid" class="h-3.5 w-3.5 text-red-500"></iconify-icon>
                                    CRÍTICO
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                    <iconify-icon icon="heroicons:exclamation-circle-solid" class="h-3.5 w-3.5 text-amber-500"></iconify-icon>
                                    STOCK BAJO
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500">
                            SKU: {{ $product->sku }}
                            @if ($product->category) | {{ $product->category->nombre }} @endif
                            @if ($product->brand) | {{ $product->brand->nombre }} @endif
                        </p>
                    </div>

                    {{-- Stock info --}}
                    <div class="text-right">
                        <div class="flex items-center gap-4">
                            <div class="text-center">
                                <p class="text-xs text-gray-500">Stock</p>
                                <p class="text-xl font-bold {{ $isCritical ? 'text-red-600' : 'text-amber-600' }}">{{ number_format($product->stock) }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500">Mínimo</p>
                                <p class="text-xl font-bold text-gray-600">{{ number_format($product->stock_minimo) }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500">Faltante</p>
                                <p class="text-xl font-bold text-gray-600">{{ number_format(max(0, $product->stock_minimo - $product->stock)) }}</p>
                            </div>
                        </div>
                        {{-- Progress bar --}}
                        <div class="mt-2 h-2 w-48 rounded-full bg-gray-200">
                            <div class="h-2 rounded-full {{ $isCritical ? 'bg-red-500' : ($stockPercent < 50 ? 'bg-amber-500' : 'bg-yellow-400') }}"
                                style="width: {{ min(100, $stockPercent) }}%"></div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-col gap-1">
                        <a href="{{ route('admin.kardex') }}" wire:navigate>
                            <flux:button variant="outline" size="sm">
                                <iconify-icon icon="heroicons:chart-bar" class="h-3.5 w-3.5"></iconify-icon>
                                Kardex
                            </flux:button>
                        </a>
                        <a href="{{ route('admin.ordenes-compra.create') }}" wire:navigate>
                            <flux:button variant="primary" size="sm">
                                <iconify-icon icon="heroicons:shopping-cart" class="h-3.5 w-3.5"></iconify-icon>
                                Crear OC
                            </flux:button>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border-2 border-dashed border-emerald-200 bg-emerald-50/30 p-12 text-center">
                <div class="flex h-14 w-14 mx-auto items-center justify-center rounded-full bg-emerald-100">
                    <iconify-icon icon="heroicons:check-circle-solid" class="h-7 w-7 text-emerald-500"></iconify-icon>
                </div>
                <h3 class="mt-3 text-sm font-semibold text-gray-900">¡Todo en orden!</h3>
                <p class="mt-1 text-sm text-gray-500">No hay productos con stock por debajo del mínimo.</p>
            </div>
        @endforelse
    </div>

    @if ($products->hasPages())
        <div class="mt-4">{{ $products->links() }}</div>
    @endif
</div>
