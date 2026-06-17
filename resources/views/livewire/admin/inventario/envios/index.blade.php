<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Envíos</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona los envíos y el seguimiento de entregas.</p>
        </div>
        <a href="{{ route('admin.envios.create') }}" wire:navigate>
            <flux:button variant="primary">
                <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon> Nuevo Envío
            </flux:button>
        </a>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Total</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                    <iconify-icon icon="heroicons:document-text-solid" class="h-4 w-4 text-indigo-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Preparando</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100">
                    <iconify-icon icon="heroicons:clock-solid" class="h-4 w-4 text-gray-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-600">{{ number_format($stats['preparando']) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">En Tránsito</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50">
                    <iconify-icon icon="heroicons:truck-solid" class="h-4 w-4 text-amber-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($stats['en_transito']) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Entregados</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50">
                    <iconify-icon icon="heroicons:check-circle-solid" class="h-4 w-4 text-emerald-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($stats['entregados']) }}</p>
        </div>
    </div>

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar envío, tracking, orden..." icon="magnifying-glass" />
        </div>
        <div class="flex items-center gap-2">
            <flux:select wire:model.live="filterEstado" class="w-auto">
                <flux:select.option value="all">Todos los estados</flux:select.option>
                <flux:select.option value="preparando">Preparando</flux:select.option>
                <flux:select.option value="enviado">Enviado</flux:select.option>
                <flux:select.option value="en_transito">En Tránsito</flux:select.option>
                <flux:select.option value="entregado">Entregado</flux:select.option>
                <flux:select.option value="devuelto">Devuelto</flux:select.option>
            </flux:select>
            @if ($carriers->count() > 0)
                <flux:select wire:model.live="filterCarrier" class="w-auto">
                    <flux:select.option value="">Todas las transportadoras</flux:select.option>
                    @foreach ($carriers as $c)
                        <flux:select.option value="{{ $c }}">{{ $c }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Número</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Orden</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Transportadora</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tracking</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Envío</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($shipments as $shipment)
                    @php $ec = $shipment->estado_color; @endphp
                    <tr class="hover:bg-gray-50 {{ $shipment->retrasado ? 'bg-red-50/50' : '' }}">
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="font-mono text-sm font-medium text-gray-900">{{ $shipment->numero }}</span>
                            @if ($shipment->retrasado)
                                <span class="ml-1 inline-flex items-center gap-0.5 rounded-full bg-red-50 px-1.5 py-0.5 text-[10px] font-semibold text-red-700">
                                    <iconify-icon icon="heroicons:exclamation-circle-solid" class="h-3 w-3"></iconify-icon>
                                    Retrasado
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-700">{{ $shipment->order?->numero ?? '-' }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="inline-flex items-center gap-1 rounded-full bg-{{ $ec }}-50 px-2.5 py-0.5 text-[10px] font-semibold text-{{ $ec }}-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-{{ $ec }}-500"></span>
                                {{ $shipment->estado_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($shipment->empleado)
                                <span class="flex items-center gap-1.5 text-sm font-medium text-gray-900">
                                    <iconify-icon icon="heroicons:user-solid" class="h-4 w-4 text-cyan-500"></iconify-icon>
                                    {{ $shipment->empleado->full_name }}
                                    <span class="inline-flex items-center rounded-full bg-cyan-50 px-1.5 py-0.5 text-[10px] font-semibold text-cyan-700">Interno</span>
                                </span>
                            @else
                                <span class="flex items-center gap-1.5 text-sm text-gray-600">
                                    <iconify-icon icon="heroicons:truck-solid" class="h-4 w-4 text-gray-400"></iconify-icon>
                                    {{ $shipment->carrier_name ?? '-' }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $shipment->tracking_number ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ $shipment->fecha_envio?->format('d/m/Y') ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.envios.edit', $shipment->id) }}" wire:navigate>
                                    <flux:button variant="ghost" size="sm" class="!text-gray-400 hover:!text-cyan-600" title="Editar">
                                        <iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon>
                                    </flux:button>
                                </a>
                                <a href="{{ route('admin.envios.guia-despacho', $shipment->id) }}" wire:navigate>
                                    <flux:button variant="ghost" size="sm" class="!text-gray-400 hover:!text-cyan-600" title="Guía de Despacho">
                                        <iconify-icon icon="heroicons:document-text" class="h-4 w-4"></iconify-icon>
                                    </flux:button>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                                    <iconify-icon icon="heroicons:truck" class="h-6 w-6 text-gray-400"></iconify-icon>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-900">No hay envíos</p>
                                <p class="text-xs text-gray-500">Crea tu primer envío para comenzar</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $shipments->links() }}</div>
</div>
