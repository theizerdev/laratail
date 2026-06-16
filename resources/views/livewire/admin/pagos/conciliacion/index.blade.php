<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Conciliación Bancaria</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Verifica y concilia los pagos con transferencias, tarjetas y pagos digitales.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">{{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Transferencias</p>
            <p class="text-xl font-bold text-blue-600">${{ number_format($stats['total_transferencias'], 2) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Tarjetas</p>
            <p class="text-xl font-bold text-purple-600">${{ number_format($stats['total_tarjetas'], 2) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Digital (Zelle/Binance)</p>
            <p class="text-xl font-bold text-indigo-600">${{ number_format($stats['total_digital'], 2) }}</p>
        </div>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/30">
            <p class="text-sm text-emerald-700 dark:text-emerald-400">Conciliados</p>
            <p class="text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ $stats['conciliados'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por referencia u orden..."
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white sm:max-w-xs" />
        <select wire:model.live="filterMetodo" class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
            <option value="all">Todos los métodos</option>
            <option value="transferencia">Transferencia</option>
            <option value="tarjeta">Tarjeta</option>
            <option value="zelle">Zelle</option>
            <option value="binance">Binance</option>
        </select>
        <select wire:model.live="filterConciliado" class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
            <option value="all">Todos</option>
            <option value="no_conciliado">No conciliados</option>
            <option value="conciliado">Conciliados</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Orden</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Monto</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Método</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Referencia</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($payments as $payment)
                    @php $isConciliado = str_contains($payment->notas ?? '', 'CONCILIADO'); @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ $isConciliado ? 'bg-emerald-50/50 dark:bg-emerald-900/10' : '' }}">
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            <span class="font-medium text-cyan-600 dark:text-cyan-400">{{ $payment->order?->numero ?? '-' }}</span>
                            <br><span class="text-xs text-gray-500">{{ $payment->order?->customer?->nombre ?? '-' }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($payment->amount, 2) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $payment->metodo_pago_label }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $payment->referencia ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $payment->fecha_pago?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            @if ($isConciliado)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    <x-heroicons:check-circle class="h-3 w-3" /> Conciliado
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                    Pendiente
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                            @if (!$isConciliado)
                                <button wire:click="conciliar({{ $payment->id }})" class="text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300" title="Marcar como conciliado">
                                    <x-heroicons:check class="h-5 w-5" />
                                </button>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay pagos para conciliar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>
</div>
