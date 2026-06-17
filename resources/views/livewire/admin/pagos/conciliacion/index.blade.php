<div>
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Conciliación Bancaria</h1>
        <p class="mt-1 text-sm text-gray-500">Verifica y concilia los pagos con transferencias, tarjetas y pagos digitales.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
                <iconify-icon icon="heroicons:building-library-solid" class="h-5 w-5 text-blue-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Transferencias</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($stats['total_transferencias'], 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-purple-100">
                <iconify-icon icon="heroicons:credit-card-solid" class="h-5 w-5 text-purple-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Tarjetas</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($stats['total_tarjetas'], 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100">
                <iconify-icon icon="heroicons:globe-alt-solid" class="h-5 w-5 text-indigo-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Digital (Zelle/Binance)</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($stats['total_digital'], 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                <iconify-icon icon="heroicons:check-badge-solid" class="h-5 w-5 text-emerald-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Conciliados</p>
            <p class="text-xl font-bold text-gray-900">{{ $stats['conciliados'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por referencia u orden..." class="sm:max-w-xs" />
        <flux:select wire:model.live="filterMetodo">
            <flux:select.option value="all">Todos los métodos</flux:select.option>
            <flux:select.option value="transferencia">Transferencia</flux:select.option>
            <flux:select.option value="tarjeta">Tarjeta</flux:select.option>
            <flux:select.option value="zelle">Zelle</flux:select.option>
            <flux:select.option value="binance">Binance</flux:select.option>
        </flux:select>
        <flux:select wire:model.live="filterConciliado">
            <flux:select.option value="all">Todos</flux:select.option>
            <flux:select.option value="no_conciliado">No conciliados</flux:select.option>
            <flux:select.option value="conciliado">Conciliados</flux:select.option>
        </flux:select>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Orden</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Monto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Método</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Referencia</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($payments as $payment)
                    @php $isConciliado = str_contains($payment->notas ?? '', 'CONCILIADO'); @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors {{ $isConciliado ? 'bg-emerald-50/30' : '' }}">
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            <span class="font-semibold text-cyan-600">{{ $payment->order?->numero ?? '-' }}</span>
                            <br><span class="text-xs text-gray-500">{{ $payment->order?->customer?->nombre ?? '-' }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-bold text-gray-900">${{ number_format($payment->amount, 2) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">{{ $payment->metodo_pago_label }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $payment->referencia ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $payment->fecha_pago?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            @if ($isConciliado)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                    <iconify-icon icon="heroicons:check-circle-solid" class="h-3.5 w-3.5"></iconify-icon>
                                    Conciliado
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    Pendiente
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                            @if (!$isConciliado)
                                <flux:button wire:click="conciliar({{ $payment->id }})" variant="ghost" size="sm" class="text-gray-400 hover:text-emerald-600">
                                    <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5"></iconify-icon>
                                </flux:button>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <iconify-icon icon="heroicons:document-check" class="h-10 w-10 text-gray-300"></iconify-icon>
                                <p class="text-sm text-gray-500">No hay pagos para conciliar.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>
</div>
