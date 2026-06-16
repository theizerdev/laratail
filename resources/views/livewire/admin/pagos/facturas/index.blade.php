<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Facturas</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestiona las facturas y proformas emitidas.</p>
        </div>
        <a href="{{ route('admin.facturas.create') }}" class="inline-flex items-center gap-1 rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700">
             <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon> Nueva Factura
        </a>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Emitidas</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $stats['total_emitidas'] }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Anuladas</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['total_anuladas'] }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Monto Total</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($stats['monto_total'], 2) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Facturas</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_facturas'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por número o cliente..."
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white sm:max-w-xs" />
        <select wire:model.live="filterEstado" class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
            <option value="all">Todos los estados</option>
            <option value="emitida">Emitida</option>
            <option value="anulada">Anulada</option>
        </select>
        <select wire:model.live="filterTipo" class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
            <option value="all">Todos los tipos</option>
            <option value="factura">Factura</option>
            <option value="proforma">Proforma</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Número</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Total</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Fecha</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($invoices as $invoice)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-cyan-600 dark:text-cyan-400">{{ $invoice->numero }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            <span class="inline-flex rounded-full bg-{{ $invoice->tipo === 'factura' ? 'blue' : 'purple' }}-100 px-2 py-0.5 text-xs font-medium text-{{ $invoice->tipo === 'factura' ? 'blue' : 'purple' }}-700">
                                {{ $invoice->tipo_label }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $invoice->order?->customer?->nombre ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($invoice->total, 2) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            <span class="inline-flex rounded-full bg-{{ $invoice->estado_color }}-100 px-2 py-0.5 text-xs font-medium text-{{ $invoice->estado_color }}-700 dark:bg-{{ $invoice->estado_color }}-900/30 dark:text-{{ $invoice->estado_color }}-400">
                                {{ ucfirst($invoice->estado) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $invoice->fecha_emision?->format('d/m/Y') ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                            <a href="{{ route('admin.facturas.preview', $invoice->id) }}" class="text-gray-500 hover:text-cyan-600 dark:hover:text-cyan-400">
                                 <iconify-icon icon="heroicons:eye" class="h-4 w-4"></iconify-icon>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay facturas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $invoices->links() }}</div>
</div>
