<div>
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Kardex - Historial de Stock</h2>
        <p class="mt-1 text-sm text-gray-500">Consulta el historial completo de movimientos por producto.</p>
    </div>

    {{-- Filters --}}
    <div class="mb-6 rounded-2xl bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
            <div class="relative flex-1 max-w-md">
                <label class="mb-1 block text-xs font-semibold text-gray-500 uppercase tracking-wider">Producto</label>
                <div class="relative">
                    <flux:input wire:model.live.debounce.300ms="productSearch" wire:focus="$set('showProductDropdown', true)"
                        placeholder="Buscar por nombre o SKU..." icon="magnifying-glass" />
                    @if ($product_id)
                        <button wire:click="clearProduct" class="absolute right-2 top-2 text-gray-400 hover:text-gray-600 transition-colors">
                            <iconify-icon icon="heroicons:x-mark" class="h-4 w-4"></iconify-icon>
                        </button>
                    @endif
                </div>
                @if ($showProductDropdown && count($searchResults) > 0)
                    <div class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                        @foreach ($searchResults as $p)
                            <button type="button" wire:click="selectProduct({{ $p->id }})"
                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-gray-50 transition-colors">
                                <span class="font-medium text-gray-900">{{ $p->nombre }}</span>
                                <span class="text-gray-400">({{ $p->sku }})</span>
                                <span class="ml-auto text-xs text-gray-500">Stock: {{ $p->stock }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            <div>
                <flux:input type="date" wire:model.live="dateFrom" label="Desde" />
            </div>
            <div>
                <flux:input type="date" wire:model.live="dateTo" label="Hasta" />
            </div>
        </div>
    </div>

    @if ($product)
        {{-- Summary Cards --}}
        <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-gray-400">Total Entradas</p>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50">
                        <iconify-icon icon="heroicons:arrow-down-circle-solid" class="h-4 w-4 text-emerald-600"></iconify-icon>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($summary['total_entradas']) }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-gray-400">Total Salidas</p>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50">
                        <iconify-icon icon="heroicons:arrow-up-circle-solid" class="h-4 w-4 text-red-600"></iconify-icon>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-red-600">{{ number_format($summary['total_salidas']) }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-gray-400">Ajustes</p>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50">
                        <iconify-icon icon="heroicons:wrench-screwdriver-solid" class="h-4 w-4 text-amber-600"></iconify-icon>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($summary['total_ajustes']) }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-gray-400">Stock Actual</p>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-50">
                        <iconify-icon icon="heroicons:cube-solid" class="h-4 w-4 text-cyan-600"></iconify-icon>
                    </div>
                </div>
                <p class="mt-2 text-2xl font-bold text-cyan-600">{{ number_format($summary['stock_actual']) }}</p>
            </div>
        </div>

        {{-- Product Info --}}
        <div class="mb-4 rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                @if ($product->imagen_principal)
                    <img src="{{ Storage::url($product->imagen_principal) }}" alt="{{ $product->nombre }}" class="h-12 w-12 rounded-xl object-cover">
                @else
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100">
                        <iconify-icon icon="heroicons:photo" class="h-6 w-6 text-gray-400"></iconify-icon>
                    </div>
                @endif
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $product->nombre }}</h3>
                    <p class="text-sm text-gray-500">SKU: {{ $product->sku }} | Stock mínimo: {{ $product->stock_minimo }}</p>
                </div>
                <div class="ml-auto text-right text-sm text-gray-500">
                    @if ($summary['primer_movimiento'])
                        <p class="flex items-center gap-1 justify-end">
                            <iconify-icon icon="heroicons:calendar" class="h-4 w-4 text-gray-400"></iconify-icon>
                            Desde: {{ $summary['primer_movimiento']->format('d/m/Y') }}
                        </p>
                    @endif
                    @if ($summary['ultimo_movimiento'])
                        <p class="flex items-center gap-1 justify-end">
                            <iconify-icon icon="heroicons:calendar" class="h-4 w-4 text-gray-400"></iconify-icon>
                            Hasta: {{ $summary['ultimo_movimiento']->format('d/m/Y') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Movements Table --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Cantidad</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Stock Ant.</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Stock Nuevo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Variante</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Referencia</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Motivo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($movements as $mov)
                        @php
                            $color = match($mov->tipo) {
                                'entrada' => 'emerald', 'salida' => 'red', 'ajuste' => 'amber', 'transferencia' => 'blue', default => 'gray'
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex items-center gap-1 rounded-full bg-{{ $color }}-50 px-2.5 py-0.5 text-[10px] font-semibold text-{{ $color }}-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-{{ $color }}-500"></span>
                                    {{ ucfirst($mov->tipo) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-center text-sm font-semibold text-{{ $color }}-600">
                                @if ($mov->tipo === 'salida')-@elseif($mov->tipo === 'entrada')+@endif{{ number_format($mov->cantidad) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-gray-600">{{ number_format($mov->stock_anterior) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-center text-sm font-semibold text-gray-900">{{ number_format($mov->stock_nuevo) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $mov->variant?->combinacion ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $mov->referencia ?? '-' }}</td>
                            <td class="max-w-xs truncate px-4 py-3 text-sm text-gray-600" title="{{ $mov->motivo }}">{{ $mov->motivo ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $mov->user?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                                        <iconify-icon icon="heroicons:arrow-path" class="h-6 w-6 text-gray-400"></iconify-icon>
                                    </div>
                                    <p class="mt-2 text-sm font-medium text-gray-900">Sin movimientos</p>
                                    <p class="text-xs text-gray-500">No hay movimientos registrados para este producto en el rango seleccionado.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center">
            <div class="flex h-14 w-14 mx-auto items-center justify-center rounded-full bg-gray-100">
                <iconify-icon icon="heroicons:cube-solid" class="h-7 w-7 text-gray-400"></iconify-icon>
            </div>
            <h3 class="mt-3 text-sm font-semibold text-gray-900">Selecciona un producto</h3>
            <p class="mt-1 text-sm text-gray-500">Busca y selecciona un producto para ver su historial de movimientos (Kardex).</p>
        </div>
    @endif
</div>
