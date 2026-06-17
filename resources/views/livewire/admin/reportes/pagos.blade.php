<div @if($preset === 'hoy') wire:poll.30s @endif>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100">
                <iconify-icon icon="heroicons:banknotes-solid" class="h-6 w-6 text-orange-600"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Reporte de Pagos</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
            </div>
        </div>
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
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Total Cobrado</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50">
                    <iconify-icon icon="heroicons:check-circle-solid" class="h-4 w-4 text-emerald-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($totalCobrado, 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Pendiente por Cobrar</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50">
                    <iconify-icon icon="heroicons:clock-solid" class="h-4 w-4 text-amber-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-amber-600">${{ number_format($pendientePorCobrar, 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Métodos Activos</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50">
                    <iconify-icon icon="heroicons:credit-card-solid" class="h-4 w-4 text-blue-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $metodosActivos }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Tasa de Cobro</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50">
                    <iconify-icon icon="heroicons:chart-pie-solid" class="h-4 w-4 text-rose-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $tasaCobro }}%</p>
        </div>
    </div>

    {{-- Row: Ingresos por Método + Cobros por Día --}}
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Ingresos por Método --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Ingresos por Método de Pago</h3>
            <div class="space-y-4">
                @forelse($ingresosPorMetodo as $metodo)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs">
                            <span class="font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $metodo->metodo_pago)) }} <span class="text-gray-400">({{ $metodo->cantidad }})</span></span>
                            <span class="font-bold text-gray-900">${{ number_format($metodo->total, 2) }} ({{ round(($metodo->total / $totalIngresosMetodo) * 100) }}%)</span>
                        </div>
                        <div class="h-3 rounded-full bg-gray-100">
                            <div class="h-3 rounded-full bg-rose-400" style="width: {{ ($metodo->total / $totalIngresosMetodo) * 100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">Sin datos</p>
                @endforelse
            </div>
        </div>

        {{-- Cobros por Día --}}
        @if($cobrosPorDia->count() > 1)
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Cobros por Día</h3>
                <div class="flex h-48 items-end gap-1 overflow-x-auto">
                    @foreach($cobrosPorDia as $dia)
                        <div class="flex min-w-[40px] flex-1 flex-col items-center gap-1">
                            <span class="text-[10px] font-semibold text-gray-600">${{ number_format($dia->monto, 0) }}</span>
                            <div class="w-full rounded-t-md bg-emerald-400 transition-all" style="height: {{ max(4, ($dia->monto / $maxCobroDia) * 100) }}%"></div>
                            <span class="text-[9px] text-gray-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($dia->fecha)->format('d/m') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Resumen por Estado</h3>
                <div class="space-y-3">
                    @foreach($pagosPorEstado as $estado)
                        @php $color = $pagoEstadoColors[$estado->estado] ?? 'gray'; @endphp
                        <div class="flex items-center justify-between rounded-lg border border-gray-100 p-3">
                            <span class="flex items-center gap-2 text-sm">
                                <span class="h-2.5 w-2.5 rounded-full bg-{{ $color }}-500"></span>
                                <span class="font-medium text-gray-700">{{ ucfirst($estado->estado) }}</span>
                            </span>
                            <span class="text-sm font-bold text-gray-900">{{ $estado->cantidad }} · ${{ number_format($estado->monto, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Row: Estado Distribution + Facturación --}}
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Pagos por Estado --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Distribución de Pagos por Estado</h3>
            <div class="space-y-4">
                @foreach($pagosPorEstado as $estado)
                    @php $color = $pagoEstadoColors[$estado->estado] ?? 'gray'; @endphp
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs">
                            <span class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-{{ $color }}-500"></span>
                                <span class="font-medium text-gray-700">{{ ucfirst($estado->estado) }}</span>
                            </span>
                            <span class="font-bold text-gray-900">{{ $estado->cantidad }} ({{ round(($estado->cantidad / $totalPagosEstados) * 100) }}%)</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100">
                            <div class="h-2 rounded-full bg-{{ $color }}-400" style="width: {{ ($estado->cantidad / $totalPagosEstados) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Facturación --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Facturación del Período</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-xl bg-emerald-50 p-4 text-center">
                    <p class="text-2xl font-bold text-emerald-700">{{ $facturasEmitidas }}</p>
                    <p class="mt-1 text-xs text-emerald-600">Facturas Emitidas</p>
                </div>
                <div class="rounded-xl bg-red-50 p-4 text-center">
                    <p class="text-2xl font-bold text-red-700">{{ $facturasAnuladas }}</p>
                    <p class="mt-1 text-xs text-red-600">Anuladas</p>
                </div>
                <div class="rounded-xl bg-blue-50 p-4 text-center">
                    <p class="text-2xl font-bold text-blue-700">{{ $proformas }}</p>
                    <p class="mt-1 text-xs text-blue-600">Proformas</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($totalFacturado, 2) }}</p>
                    <p class="mt-1 text-xs text-gray-500">Total Facturado</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Últimos Pagos --}}
    <div class="rounded-2xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-sm font-semibold text-gray-900">Últimos Pagos</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-gray-100 text-gray-400">
                    <tr>
                        <th class="pb-2 font-medium">Fecha</th>
                        <th class="pb-2 font-medium">Pedido</th>
                        <th class="pb-2 font-medium">Método</th>
                        <th class="pb-2 font-medium">Estado</th>
                        <th class="pb-2 font-medium text-right">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($ultimosPagos as $pago)
                        @php $color = $pagoEstadoColors[$pago->estado] ?? 'gray'; @endphp
                        <tr>
                            <td class="py-2 text-gray-500">{{ $pago->fecha_pago?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="py-2 font-medium text-gray-900">{{ $pago->order?->numero ?? '-' }}</td>
                            <td class="py-2 text-gray-700">{{ ucfirst(str_replace('_', ' ', $pago->metodo_pago)) }}</td>
                            <td class="py-2">
                                <span class="rounded-full bg-{{ $color }}-100 px-2 py-0.5 text-[10px] font-medium text-{{ $color }}-700">{{ ucfirst($pago->estado) }}</span>
                            </td>
                            <td class="py-2 text-right font-semibold text-gray-900">${{ number_format($pago->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-gray-400">Sin pagos en el período</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
