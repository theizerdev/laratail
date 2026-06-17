<div wire:poll.120s>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100">
                <iconify-icon icon="heroicons:cube-solid" class="h-6 w-6 text-orange-600"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Reporte de Inventario</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Actualizado {{ now()->format('H:i') }}</p>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Total Productos</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                    <iconify-icon icon="heroicons:cube-solid" class="h-4 w-4 text-indigo-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $totalProductos }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Valorización Total</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50">
                    <iconify-icon icon="heroicons:currency-dollar-solid" class="h-4 w-4 text-emerald-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($valorizacionTotal, 2) }}</p>
            <p class="mt-1 text-xs text-gray-400">Stock × Costo</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Bajo Stock</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50">
                    <iconify-icon icon="heroicons:exclamation-triangle-solid" class="h-4 w-4 text-amber-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold {{ $bajoStock > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $bajoStock }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Sin Stock</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50">
                    <iconify-icon icon="heroicons:x-circle-solid" class="h-4 w-4 text-red-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold {{ $sinStock > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $sinStock }}</p>
        </div>
    </div>

    {{-- Row: Valorización por Categoría + Movimientos por Tipo --}}
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Valorización por Categoría --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Valorización por Categoría</h3>
            <div class="space-y-3">
                @forelse($valorizacionPorCategoria as $cat)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs">
                            <span class="font-medium text-gray-700">{{ $cat->categoria ?? 'Sin Categoría' }} <span class="text-gray-400">({{ $cat->total_productos }} prod)</span></span>
                            <span class="font-bold text-gray-900">${{ number_format($cat->valor_total, 2) }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-gray-100">
                            <div class="h-2.5 rounded-full bg-indigo-500" style="width: {{ ($cat->valor_total / $maxValorCategoria) * 100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">Sin datos</p>
                @endforelse
            </div>
        </div>

        {{-- Movimientos por Tipo --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Movimientos por Tipo</h3>
            <div class="space-y-4">
                @forelse($movimientosPorTipo as $mov)
                    @php $color = $tipoColors[$mov->tipo] ?? 'gray'; @endphp
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs">
                            <span class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-{{ $color }}-500"></span>
                                <span class="font-medium text-gray-700">{{ ucfirst($mov->tipo) }}</span>
                            </span>
                            <span class="font-bold text-gray-900">{{ $mov->cantidad }} mov · {{ $mov->total_unidades }} uds</span>
                        </div>
                        <div class="h-3 rounded-full bg-gray-100">
                            <div class="h-3 rounded-full bg-{{ $color }}-400" style="width: {{ ($mov->cantidad / $totalMovimientos) * 100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">Sin movimientos registrados</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Row: Bajo Stock + Top Stock --}}
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Productos bajo stock mínimo --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                <iconify-icon icon="heroicons:exclamation-triangle-solid" class="h-4 w-4 text-amber-500"></iconify-icon>
                Productos Bajo Stock Mínimo
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-gray-100 text-gray-400">
                        <tr>
                            <th class="pb-2 font-medium">Producto</th>
                            <th class="pb-2 font-medium text-right">Stock</th>
                            <th class="pb-2 font-medium text-right">Mínimo</th>
                            <th class="pb-2 font-medium text-right">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($productosBajoStock as $prod)
                            @php $pct = $prod->stock_minimo > 0 ? round(($prod->stock / $prod->stock_minimo) * 100) : 0; @endphp
                            <tr>
                                <td class="py-2">
                                    <p class="font-medium text-gray-900 truncate max-w-[160px]">{{ $prod->nombre }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $prod->sku }}</p>
                                </td>
                                <td class="py-2 text-right font-semibold text-amber-600">{{ $prod->stock }}</td>
                                <td class="py-2 text-right text-gray-500">{{ $prod->stock_minimo }}</td>
                                <td class="py-2 text-right">
                                    <span class="rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-medium text-amber-700">{{ $pct }}%</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-gray-400">No hay productos bajo stock mínimo</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top 10 productos con más stock --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                <iconify-icon icon="heroicons:arrow-up-circle-solid" class="h-4 w-4 text-blue-500"></iconify-icon>
                Top 10 Productos con Más Stock
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-gray-100 text-gray-400">
                        <tr>
                            <th class="pb-2 font-medium">#</th>
                            <th class="pb-2 font-medium">Producto</th>
                            <th class="pb-2 font-medium text-right">Stock</th>
                            <th class="pb-2 font-medium text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($topStock as $i => $prod)
                            <tr>
                                <td class="py-2 text-gray-400">{{ $i + 1 }}</td>
                                <td class="py-2 font-medium text-gray-900 truncate max-w-[160px]">{{ $prod->nombre }}</td>
                                <td class="py-2 text-right font-semibold text-gray-900">{{ $prod->stock }}</td>
                                <td class="py-2 text-right text-gray-700">${{ number_format($prod->valor, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-gray-400">Sin datos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Movimientos Recientes --}}
    <div class="mb-6 rounded-2xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-sm font-semibold text-gray-900">Últimos 20 Movimientos de Inventario</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-gray-100 text-gray-400">
                    <tr>
                        <th class="pb-2 font-medium">Fecha</th>
                        <th class="pb-2 font-medium">Producto</th>
                        <th class="pb-2 font-medium">Tipo</th>
                        <th class="pb-2 font-medium text-right">Cantidad</th>
                        <th class="pb-2 font-medium text-right">Stock Anterior</th>
                        <th class="pb-2 font-medium text-right">Stock Nuevo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($movimientosRecientes as $mov)
                        @php $color = $tipoColors[$mov->tipo] ?? 'gray'; @endphp
                        <tr>
                            <td class="py-2 text-gray-500">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-2 font-medium text-gray-900 truncate max-w-[180px]">{{ $mov->product?->nombre ?? '-' }}</td>
                            <td class="py-2">
                                <span class="rounded-full bg-{{ $color }}-100 px-2 py-0.5 text-[10px] font-medium text-{{ $color }}-700">{{ ucfirst($mov->tipo) }}</span>
                            </td>
                            <td class="py-2 text-right text-gray-700">{{ $mov->cantidad }}</td>
                            <td class="py-2 text-right text-gray-500">{{ $mov->stock_anterior }}</td>
                            <td class="py-2 text-right font-semibold text-gray-900">{{ $mov->stock_nuevo }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-gray-400">Sin movimientos registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Productos Estancados (30+ días sin movimiento) --}}
    @if($productosEstancados->isNotEmpty())
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                <iconify-icon icon="heroicons:pause-circle-solid" class="h-4 w-4 text-gray-400"></iconify-icon>
                Inventario Estancado (30+ días sin movimientos)
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-gray-100 text-gray-400">
                        <tr>
                            <th class="pb-2 font-medium">Producto</th>
                            <th class="pb-2 font-medium">SKU</th>
                            <th class="pb-2 font-medium text-right">Stock</th>
                            <th class="pb-2 font-medium text-right">Valor Unit.</th>
                            <th class="pb-2 font-medium text-right">Valor Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($productosEstancados as $prod)
                            <tr>
                                <td class="py-2 font-medium text-gray-900 truncate max-w-[200px]">{{ $prod->nombre }}</td>
                                <td class="py-2 text-gray-500">{{ $prod->sku }}</td>
                                <td class="py-2 text-right text-gray-700">{{ $prod->stock }}</td>
                                <td class="py-2 text-right text-gray-500">${{ number_format($prod->precio_compra ?? $prod->precio, 2) }}</td>
                                <td class="py-2 text-right font-semibold text-gray-900">${{ number_format(($prod->precio_compra ?? 0) * $prod->stock, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
