<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notas de Crédito</h1>
            <p class="mt-1 text-sm text-gray-500">Gestiona las notas de crédito emitidas contra facturas.</p>
        </div>
        <a href="{{ route('admin.notas-credito.create') }}">
            <flux:button variant="primary">Nueva Nota de Crédito</flux:button>
        </a>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                <iconify-icon icon="heroicons:document-check-solid" class="h-5 w-5 text-emerald-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Emitidas</p>
            <p class="text-xl font-bold text-gray-900">{{ $stats['total_emitidas'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-red-100">
                <iconify-icon icon="heroicons:document-minus-solid" class="h-5 w-5 text-red-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Anuladas</p>
            <p class="text-xl font-bold text-gray-900">{{ $stats['total_anuladas'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
                <iconify-icon icon="heroicons:currency-dollar-solid" class="h-5 w-5 text-blue-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Monto Total</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($stats['monto_total'], 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-gray-100">
                <iconify-icon icon="heroicons:document-text-solid" class="h-5 w-5 text-gray-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Total Notas</p>
            <p class="text-xl font-bold text-gray-900">{{ $stats['total_notas'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por número o motivo..." class="sm:max-w-xs" />
        <flux:select wire:model.live="filterEstado">
            <flux:select.option value="all">Todos los estados</flux:select.option>
            <flux:select.option value="emitida">Emitida</flux:select.option>
            <flux:select.option value="anulada">Anulada</flux:select.option>
        </flux:select>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Número</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Factura</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Monto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Motivo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($creditNotes as $note)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-cyan-600">{{ $note->numero }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">{{ $note->invoice?->numero ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">{{ $note->invoice?->order?->customer?->nombre ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-bold text-gray-900">${{ number_format($note->monto, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ Str::limit($note->motivo, 40) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            @php $color = $note->estado_color; @endphp
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-{{ $color }}-50 px-2.5 py-1 text-xs font-semibold text-{{ $color }}-700 ring-1 ring-inset ring-{{ $color }}-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-{{ $color }}-500"></span>
                                {{ ucfirst($note->estado) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $note->fecha_emision?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <iconify-icon icon="heroicons:document-minus" class="h-10 w-10 text-gray-300"></iconify-icon>
                                <p class="text-sm text-gray-500">No hay notas de crédito.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $creditNotes->links() }}</div>
</div>
