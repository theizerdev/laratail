<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Envíos</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestiona los envíos y el seguimiento de entregas.</p>
        </div>
        <a href="{{ route('admin.envios.create') }}" class="inline-flex items-center gap-1 rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700">
             <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon> Nuevo Envío
        </a>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Preparando</p>
            <p class="text-2xl font-bold text-gray-600 dark:text-gray-300">{{ number_format($stats['preparando']) }}</p>
        </div>
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/30">
            <p class="text-sm text-amber-600 dark:text-amber-400">En Tránsito</p>
            <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ number_format($stats['en_transito']) }}</p>
        </div>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/30">
            <p class="text-sm text-emerald-600 dark:text-emerald-400">Entregados</p>
            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($stats['entregados']) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1 max-w-md">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar envío, tracking, orden..."
                class="w-full rounded-lg border-gray-300 pl-10 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
             <iconify-icon icon="heroicons:magnifying-glass" class="absolute left-3 top-2.5 h-5 w-5 text-gray-400"></iconify-icon>
        </div>
        <select wire:model.live="filterEstado" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="all">Todos los estados</option>
            <option value="preparando">Preparando</option>
            <option value="enviado">Enviado</option>
            <option value="en_transito">En Tránsito</option>
            <option value="entregado">Entregado</option>
            <option value="devuelto">Devuelto</option>
        </select>
        @if ($carriers->count() > 0)
            <select wire:model.live="filterCarrier" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Todas las transportadoras</option>
                @foreach ($carriers as $c)
                    <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Número</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Orden</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Transportadora</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Tracking</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Envío</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($shipments as $shipment)
                    @php $ec = $shipment->estado_color; @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ $shipment->retrasado ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="text-sm font-semibold text-cyan-600 dark:text-cyan-400">{{ $shipment->numero }}</span>
                            @if ($shipment->retrasado)
                                <span class="ml-1 inline-flex items-center rounded-full bg-red-100 px-1.5 py-0.5 text-xs text-red-700 dark:bg-red-900/50 dark:text-red-400">Retrasado</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $shipment->order?->numero ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="inline-flex items-center rounded-full bg-{{ $ec }}-100 px-2.5 py-0.5 text-xs font-medium text-{{ $ec }}-800 dark:bg-{{ $ec }}-900/30 dark:text-{{ $ec }}-400">
                                {{ $shipment->estado_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $shipment->carrier_name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-gray-600 dark:text-gray-300">{{ $shipment->tracking_number ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $shipment->fecha_envio?->format('d/m/Y') ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.envios.edit', $shipment->id) }}" class="text-gray-600 hover:text-cyan-600 dark:text-gray-400" title="Editar">
                                  
                                    <iconify-icon icon="heroicons:pencil-square" class="h-5 w-5"></iconify-icon>
                                </a>
                                <a href="{{ route('admin.envios.guia-despacho', $shipment->id) }}" class="text-gray-600 hover:text-cyan-600 dark:text-gray-400" title="Guía de Despacho">
                                    <iconify-icon icon="heroicons:document-text" class="h-5 w-5"></iconify-icon>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No se encontraron envíos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $shipments->links() }}</div>
</div>
