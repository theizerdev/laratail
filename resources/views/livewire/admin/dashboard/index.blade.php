<div wire:poll.60s>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100">
                <iconify-icon icon="heroicons:presentation-chart-bar-solid" class="h-6 w-6 text-emerald-600"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900">Dashboard</h2>
                <p class="text-sm text-gray-500">Resumen general del negocio · {{ now()->format('d M Y, H:i') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reportes.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                <iconify-icon icon="heroicons:chart-bar-solid" class="h-4 w-4"></iconify-icon>
                Reportes Avanzados
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
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
                <p class="text-xs font-medium text-gray-400">Ingresos Cobrados</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50">
                    <iconify-icon icon="heroicons:banknotes-solid" class="h-4 w-4 text-rose-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($ingresosMes, 2) }}</p>
            <p class="mt-1 text-xs text-gray-400">Pagos completados este mes</p>
        </div>

        {{-- Pendientes + Facturas --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Pendientes / Facturas</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50">
                    <iconify-icon icon="heroicons:clock-solid" class="h-4 w-4 text-amber-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold {{ $pedidosPendientes > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $pedidosPendientes }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ $facturasMes }} facturas · ${{ number_format($totalFacturado, 2) }}</p>
        </div>
    </div>

    {{-- Row 1: Area Chart (30-day Sales vs Payments) --}}
    <div class="mb-6 rounded-2xl bg-white p-5 shadow-sm">
        <div class="mb-2 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Ventas vs Cobros — Últimos 30 Días</h3>
            <div class="flex items-center gap-3 text-xs text-gray-500">
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Ventas</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-blue-500"></span> Cobros</span>
            </div>
        </div>
        <div id="chart-daily-sales" style="min-height: 300px;"></div>
    </div>

    {{-- Row 2: Monthly Revenue Bar + Donut charts --}}
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Monthly Revenue --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm lg:col-span-2">
            <h3 class="mb-2 text-sm font-semibold text-gray-900">Ingresos Mensuales (12 meses)</h3>
            <div id="chart-monthly-revenue" style="min-height: 280px;"></div>
        </div>

        {{-- Sales by Status Donut --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-2 text-sm font-semibold text-gray-900">Pedidos por Estado</h3>
            <div id="chart-status-donut" style="min-height: 280px;"></div>
        </div>
    </div>

    {{-- Row 3: Payments by Method Donut + Recent Orders --}}
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Payments by Method --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-2 text-sm font-semibold text-gray-900">Pagos por Método</h3>
            <div id="chart-payments-method" style="min-height: 280px;"></div>
        </div>

        {{-- Recent Orders --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm lg:col-span-2">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Últimos Pedidos</h3>
            <div class="space-y-3">
                @forelse($ultimosPedidos as $pedido)
                    <div class="flex items-center justify-between rounded-lg border border-gray-100 p-3 transition hover:bg-gray-50">
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
                    <p class="py-8 text-center text-sm text-gray-400">No hay pedidos aún</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Row 4: Top Products + Top Clients --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Top Productos --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                <iconify-icon icon="heroicons:fire-solid" class="h-4 w-4 text-red-500"></iconify-icon>
                Top 5 Productos del Mes
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-gray-100 text-gray-400">
                        <tr>
                            <th class="pb-2 font-medium">Producto</th>
                            <th class="pb-2 font-medium text-right">Uds</th>
                            <th class="pb-2 font-medium text-right">Ingresos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($topProductos as $prod)
                            <tr>
                                <td class="py-2.5 font-medium text-gray-900 truncate max-w-[200px]">{{ $prod->nombre_producto }}</td>
                                <td class="py-2.5 text-right text-gray-700">{{ $prod->total_vendido }}</td>
                                <td class="py-2.5 text-right font-semibold text-emerald-600">${{ number_format($prod->total_ingresos, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-8 text-center text-gray-400">Sin ventas este mes</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Clientes --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                <iconify-icon icon="heroicons:user-group-solid" class="h-4 w-4 text-emerald-500"></iconify-icon>
                Top 5 Clientes del Mes
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-gray-100 text-gray-400">
                        <tr>
                            <th class="pb-2 font-medium">Cliente</th>
                            <th class="pb-2 font-medium text-right">Pedidos</th>
                            <th class="pb-2 font-medium text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($topClientes as $cliente)
                            <tr>
                                <td class="py-2.5">
                                    <p class="font-medium text-gray-900">{{ $cliente->nombre }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $cliente->email }}</p>
                                </td>
                                <td class="py-2.5 text-right text-gray-700">{{ $cliente->total_pedidos }}</td>
                                <td class="py-2.5 text-right font-semibold text-emerald-600">${{ number_format($cliente->total_gastado, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-8 text-center text-gray-400">Sin datos este mes</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ApexCharts Scripts --}}
    @script
    <script>
        // ── Daily Sales Area Chart (30 days) ──
        const dailySalesData = @js($dailySales);

        const dailyChart = new ApexCharts(document.querySelector('#chart-daily-sales'), {
            chart: {
                type: 'area',
                height: 300,
                fontFamily: 'Inter, sans-serif',
                toolbar: { show: false },
                zoom: { enabled: false },
            },
            series: [
                {
                    name: 'Ventas',
                    data: dailySalesData.map(d => d.sales),
                },
                {
                    name: 'Cobros',
                    data: dailySalesData.map(d => d.payments),
                },
            ],
            colors: ['#10b981', '#3b82f6'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] },
            },
            stroke: { curve: 'smooth', width: [2.5, 2.5] },
            xaxis: {
                categories: dailySalesData.map(d => d.date),
                labels: {
                    style: { fontSize: '10px', colors: '#9ca3af' },
                    rotate: -45,
                    rotateAlways: false,
                    hideOverlappingLabels: true,
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px', colors: '#9ca3af' },
                    formatter: (v) => '$' + v.toLocaleString(),
                },
            },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f3f4f6', strokeDashArray: 3 },
            tooltip: {
                theme: 'light',
                y: { formatter: (v) => '$' + v.toLocaleString(undefined, {minimumFractionDigits: 2}) },
            },
            legend: { show: false },
        });
        dailyChart.render();

        // ── Monthly Revenue Bar Chart ──
        const monthlyData = @js($monthlyRevenue);

        const monthlyChart = new ApexCharts(document.querySelector('#chart-monthly-revenue'), {
            chart: {
                type: 'bar',
                height: 280,
                fontFamily: 'Inter, sans-serif',
                toolbar: { show: false },
            },
            series: [{
                name: 'Ingresos',
                data: monthlyData.map(d => d.revenue),
            }],
            colors: ['#10b981'],
            plotOptions: {
                bar: { borderRadius: 6, columnWidth: '55%', distributed: false },
            },
            xaxis: {
                categories: monthlyData.map(d => d.month),
                labels: { style: { fontSize: '11px', colors: '#9ca3af' } },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px', colors: '#9ca3af' },
                    formatter: (v) => '$' + v.toLocaleString(),
                },
            },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f3f4f6', strokeDashArray: 3 },
            tooltip: {
                theme: 'light',
                y: { formatter: (v) => '$' + v.toLocaleString(undefined, {minimumFractionDigits: 2}) },
            },
        });
        monthlyChart.render();

        // ── Sales by Status Donut ──
        const estadoLabels = @js($estadoLabels);
        const estadoSeries = @js($estadoSeries);

        if (estadoSeries.length > 0) {
            const statusChart = new ApexCharts(document.querySelector('#chart-status-donut'), {
                chart: {
                    type: 'donut',
                    height: 280,
                    fontFamily: 'Inter, sans-serif',
                },
                series: estadoSeries,
                labels: estadoLabels,
                colors: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
                stroke: { width: 0 },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '12px',
                                    color: '#6b7280',
                                    formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0),
                                },
                            },
                        },
                    },
                },
                dataLabels: { enabled: false },
                legend: { position: 'bottom', fontSize: '11px', labels: { colors: '#6b7280' } },
                tooltip: { theme: 'light' },
            });
            statusChart.render();
        } else {
            document.querySelector('#chart-status-donut').innerHTML = '<p class="flex h-full items-center justify-center text-sm text-gray-400">Sin datos este mes</p>';
        }

        // ── Payments by Method Donut ──
        const metodoLabels = @js($metodoLabels);
        const metodoSeries = @js($metodoSeries);

        if (metodoSeries.length > 0) {
            const methodChart = new ApexCharts(document.querySelector('#chart-payments-method'), {
                chart: {
                    type: 'donut',
                    height: 280,
                    fontFamily: 'Inter, sans-serif',
                },
                series: metodoSeries,
                labels: metodoLabels,
                colors: ['#f43f5e', '#8b5cf6', '#06b6d4', '#f59e0b', '#10b981'],
                stroke: { width: 0 },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '12px',
                                    color: '#6b7280',
                                    formatter: (w) => '$' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString(undefined, {minimumFractionDigits: 2}),
                                },
                            },
                        },
                    },
                },
                dataLabels: { enabled: false },
                legend: { position: 'bottom', fontSize: '11px', labels: { colors: '#6b7280' } },
                tooltip: {
                    theme: 'light',
                    y: { formatter: (v) => '$' + v.toLocaleString(undefined, {minimumFractionDigits: 2}) },
                },
            });
            methodChart.render();
        } else {
            document.querySelector('#chart-payments-method').innerHTML = '<p class="flex h-full items-center justify-center text-sm text-gray-400">Sin pagos este mes</p>';
        }
    </script>
    @endscript
</div>
