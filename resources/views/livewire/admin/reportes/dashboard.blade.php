<div wire:poll.60s>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100">
                <iconify-icon icon="heroicons:chart-bar-solid" class="h-6 w-6 text-orange-600"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Dashboard Analítico</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Estadísticas en tiempo real del negocio · Actualizado {{ now()->format('H:i') }}</p>
            </div>
        </div>
        <a href="{{ route('admin.reportes.ventas') }}" class="inline-flex items-center gap-2 rounded-lg bg-orange-600 px-4 py-2 text-sm font-medium text-white hover:bg-orange-700">
            <iconify-icon icon="heroicons:arrow-trending-up-solid" class="h-4 w-4"></iconify-icon>
            Ver Reportes
        </a>
    </div>

    {{-- KPI Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
        {{-- Ventas Hoy --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Ventas Hoy</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50">
                    <iconify-icon icon="heroicons:shopping-cart-solid" class="h-4 w-4 text-emerald-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $ventasHoy->total }}</p>
            <p class="mt-1 text-sm font-semibold text-emerald-600">${{ number_format($ventasHoy->monto, 2) }}</p>
        </div>

        {{-- Ventas del Mes --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Ventas del Mes</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50">
                    <iconify-icon icon="heroicons:calendar-days-solid" class="h-4 w-4 text-blue-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $ventasMes->total }}</p>
            <p class="mt-1 text-sm font-semibold text-blue-600">${{ number_format($ventasMes->monto, 2) }}</p>
        </div>

        {{-- Ingresos del Mes --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Ingresos del Mes</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50">
                    <iconify-icon icon="heroicons:banknotes-solid" class="h-4 w-4 text-rose-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($ingresosMes, 2) }}</p>
            <p class="mt-1 text-xs text-gray-400">Pagos completados</p>
        </div>

        {{-- Pedidos Pendientes --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Pedidos Pendientes</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50">
                    <iconify-icon icon="heroicons:clock-solid" class="h-4 w-4 text-amber-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold {{ $pedidosPendientes > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $pedidosPendientes }}</p>
            <p class="mt-1 text-xs text-gray-400">Sin procesar</p>
        </div>

        {{-- Bajo Stock --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Bajo Stock</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50">
                    <iconify-icon icon="heroicons:exclamation-triangle-solid" class="h-4 w-4 text-red-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold {{ $productosBajoStock > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $productosBajoStock }}</p>
            <p class="mt-1 text-xs text-gray-400">Productos bajo mínimo</p>
        </div>
    </div>

    {{-- Row 1: Bar Chart + Últimos Pedidos --}}
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Ventas últimos 7 días --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                <iconify-icon icon="heroicons:arrow-trending-up-solid" class="h-4 w-4 text-orange-500"></iconify-icon>
                Ventas Últimos 7 Días
            </h3>
            <div class="flex h-40 items-end gap-2">
                @foreach($ventas7Dias as $dia)
                    <div class="flex flex-1 flex-col items-center gap-1">
                        <span class="text-[10px] font-semibold text-gray-600">${{ number_format($dia['monto'], 0) }}</span>
                        <div class="w-full rounded-t-md bg-orange-400 transition-all" style="height: {{ max(4, ($dia['monto'] / $maxVentaDia) * 100) }}%"></div>
                        <span class="text-[10px] text-gray-400">{{ $dia['fecha'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Últimos Pedidos --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                <iconify-icon icon="heroicons:shopping-bag-solid" class="h-4 w-4 text-blue-500"></iconify-icon>
                Últimos Pedidos
            </h3>
            <div class="space-y-3">
                @forelse($ultimosPedidos as $pedido)
                    <div class="flex items-center justify-between rounded-lg border border-gray-100 p-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $pedido->numero }}</p>
                            <p class="text-xs text-gray-400">{{ $pedido->customer?->nombre ?? 'Sin cliente' }} · {{ $pedido->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900">${{ number_format($pedido->total, 2) }}</p>
                            <span class="inline-block rounded-full px-2 py-0.5 text-[10px] font-medium
                                @if($pedido->estado === 'completado') bg-emerald-100 text-emerald-700
                                @elseif($pedido->estado === 'pendiente') bg-amber-100 text-amber-700
                                @elseif($pedido->estado === 'cancelado') bg-red-100 text-red-700
                                @else bg-blue-100 text-blue-700 @endif">{{ ucfirst($pedido->estado) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">No hay pedidos aún</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Row 2: Top Productos + Top Clientes --}}
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Top Productos --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                <iconify-icon icon="heroicons:fire-solid" class="h-4 w-4 text-red-500"></iconify-icon>
                Top 5 Productos del Mes
            </h3>
            <div class="space-y-3">
                @forelse($topProductos as $producto)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs">
                            <span class="font-medium text-gray-700 truncate max-w-[200px]">{{ $producto->nombre_producto }}</span>
                            <span class="font-bold text-gray-900">{{ $producto->total_vendido }} uds · ${{ number_format($producto->total_ingresos, 2) }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100">
                            <div class="h-2 rounded-full bg-indigo-500" style="width: {{ ($producto->total_vendido / $maxProductoVendido) * 100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">No hay ventas este mes</p>
                @endforelse
            </div>
        </div>

        {{-- Top Clientes --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                <iconify-icon icon="heroicons:user-group-solid" class="h-4 w-4 text-emerald-500"></iconify-icon>
                Top 5 Clientes del Mes
            </h3>
            <div class="space-y-3">
                @forelse($topClientes as $cliente)
                    <div class="flex items-center justify-between rounded-lg border border-gray-100 p-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $cliente->nombre ?? 'Sin nombre' }}</p>
                            <p class="text-xs text-gray-400">{{ $cliente->total_pedidos }} pedidos</p>
                        </div>
                        <p class="text-sm font-bold text-gray-900">${{ number_format($cliente->total_gastado, 2) }}</p>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">No hay datos este mes</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Row 3: Pagos por Método --}}
    <div class="rounded-2xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
            <iconify-icon icon="heroicons:credit-card-solid" class="h-4 w-4 text-rose-500"></iconify-icon>
            Distribución de Pagos por Método (Este Mes)
        </h3>
        @forelse($pagosPorMetodo as $pago)
            <div class="mb-3">
                <div class="mb-1 flex items-center justify-between text-xs">
                    <span class="font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $pago->metodo_pago)) }}</span>
                    <span class="font-bold text-gray-900">${{ number_format($pago->total, 2) }} ({{ round(($pago->total / $totalPagosMetodo) * 100) }}%)</span>
                </div>
                <div class="h-3 rounded-full bg-gray-100">
                    <div class="h-3 rounded-full bg-rose-400" style="width: {{ ($pago->total / $totalPagosMetodo) * 100 }}%"></div>
                </div>
            </div>
        @empty
            <p class="py-6 text-center text-sm text-gray-400">No hay pagos este mes</p>
        @endforelse
    </div>
</div>
