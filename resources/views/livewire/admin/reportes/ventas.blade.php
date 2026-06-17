<div @if($preset === 'hoy') wire:poll.30s @endif>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100">
                <iconify-icon icon="heroicons:arrow-trending-up-solid" class="h-6 w-6 text-orange-600"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Reporte de Ventas</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
            </div>
        </div>
        <button wire:click="exportCsv" class="inline-flex items-center gap-2 rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
            <iconify-icon icon="heroicons:arrow-down-tray-solid" class="h-4 w-4"></iconify-icon>
            Exportar CSV
        </button>
    </div>

    {{-- Presets + Date Range --}}
    <div class="mb-6 flex flex-wrap items-center gap-2">
        @foreach([
            'hoy' => 'Hoy',
            'esta_semana' => 'Esta Semana',
            'este_mes' => 'Este Mes',
            'ultimo_mes' => 'Último Mes',
            'este_ano' => 'Este Año',
        ] as $key => $label)
            <button wire:click="applyPreset('{{ $key }}')"
                class="rounded-lg px-3 py-1.5 text-xs font-medium transition
                {{ $preset === $key ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ $label }}
            </button>
        @endforeach
        <div class="ml-auto flex items-center gap-2">
            <input type="date" wire:model.live="dateFrom" class="rounded-lg border border-gray-300 px-2 py-1.5 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-white">
            <span class="text-xs text-gray-400">a</span>
            <input type="date" wire:model.live="dateTo" class="rounded-lg border border-gray-300 px-2 py-1.5 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-white">
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-400">Total Ventas</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $current->total }}</p>
            <p class="mt-1 text-xs text-gray-400">en el período</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-400">Monto Total</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($current->monto, 2) }}</p>
            <p class="mt-1 text-xs {{ $variacionVentas >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                <iconify-icon icon="heroicons:{{ $variacionVentas >= 0 ? 'arrow-trending-up' : 'arrow-trending-down' }}" class="h-3 w-3"></iconify-icon>
                {{ $variacionVentas >= 0 ? '+' : '' }}{{ $variacionVentas }}% vs período anterior
            </p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-400">Ticket Promedio</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($current->ticket_promedio, 2) }}</p>
            <p class="mt-1 text-xs text-gray-400">por pedido</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-400">Período Anterior</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($previous->monto, 2) }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ $previous->total }} ventas</p>
        </div>
    </div>

    {{-- Ventas por Día (Bar Chart) --}}
    @if($ventasPorDia->count() > 1)
        <div class="mb-6 rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Ventas por Día</h3>
            <div class="flex h-48 items-end gap-1 overflow-x-auto">
                @foreach($ventasPorDia as $dia)
                    <div class="flex min-w-[40px] flex-1 flex-col items-center gap-1">
                        <span class="text-[10px] font-semibold text-gray-600">${{ number_format($dia->monto, 0) }}</span>
                        <div class="w-full rounded-t-md bg-orange-400 transition-all" style="height: {{ max(4, ($dia->monto / $maxVentaDia) * 100) }}%"></div>
                        <span class="text-[9px] text-gray-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($dia->fecha)->format('d/m') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Row: Top Productos + Ventas por Estado --}}
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Top 10 Productos --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Top 10 Productos</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-gray-100 text-gray-400">
                        <tr>
                            <th class="pb-2 font-medium">#</th>
                            <th class="pb-2 font-medium">Producto</th>
                            <th class="pb-2 font-medium text-right">Uds</th>
                            <th class="pb-2 font-medium text-right">Ingresos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($topProductos as $i => $prod)
                            <tr>
                                <td class="py-2 text-gray-400">{{ $i + 1 }}</td>
                                <td class="py-2 font-medium text-gray-900 truncate max-w-[180px]">{{ $prod->nombre_producto }}</td>
                                <td class="py-2 text-right text-gray-700">{{ $prod->total_vendido }}</td>
                                <td class="py-2 text-right font-semibold text-gray-900">${{ number_format($prod->total_ingresos, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-gray-400">Sin datos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Ventas por Estado --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Distribución por Estado</h3>
            <div class="space-y-4">
                @foreach($ventasPorEstado as $estado)
                    @php $color = $estadoColors[$estado->estado] ?? 'gray'; @endphp
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs">
                            <span class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-{{ $color }}-500"></span>
                                <span class="font-medium text-gray-700">{{ ucfirst($estado->estado) }}</span>
                            </span>
                            <span class="font-bold text-gray-900">{{ $estado->cantidad }} · ${{ number_format($estado->monto, 2) }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100">
                            <div class="h-2 rounded-full bg-{{ $color }}-400" style="width: {{ ($estado->cantidad / $totalEstados) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Top 10 Clientes --}}
    <div class="rounded-2xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-sm font-semibold text-gray-900">Top 10 Clientes</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-gray-100 text-gray-400">
                    <tr>
                        <th class="pb-2 font-medium">#</th>
                        <th class="pb-2 font-medium">Cliente</th>
                        <th class="pb-2 font-medium text-right">Pedidos</th>
                        <th class="pb-2 font-medium text-right">Total Gastado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($topClientes as $i => $cliente)
                        <tr>
                            <td class="py-2 text-gray-400">{{ $i + 1 }}</td>
                            <td class="py-2">
                                <p class="font-medium text-gray-900">{{ $cliente->nombre ?? 'Sin nombre' }}</p>
                                <p class="text-[10px] text-gray-400">{{ $cliente->email }}</p>
                            </td>
                            <td class="py-2 text-right text-gray-700">{{ $cliente->total_pedidos }}</td>
                            <td class="py-2 text-right font-semibold text-gray-900">${{ number_format($cliente->total_gastado, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-gray-400">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
