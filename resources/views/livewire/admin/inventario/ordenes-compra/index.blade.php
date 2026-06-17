<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Órdenes de Compra</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona las órdenes de compra a proveedores.</p>
        </div>
        <a href="{{ route('admin.ordenes-compra.create') }}" wire:navigate>
            <flux:button variant="primary">
                <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
                Nueva Orden
            </flux:button>
        </a>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Total Órdenes</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                    <iconify-icon icon="heroicons:document-text-solid" class="h-4 w-4 text-indigo-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Borradores</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100">
                    <iconify-icon icon="heroicons:pencil-solid" class="h-4 w-4 text-gray-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['borrador']) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Pendientes</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50">
                    <iconify-icon icon="heroicons:clock-solid" class="h-4 w-4 text-blue-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['pendientes']) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Recibidas</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50">
                    <iconify-icon icon="heroicons:check-circle-solid" class="h-4 w-4 text-emerald-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['recibidas']) }}</p>
        </div>
    </div>

    {{-- Search + Filters --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por N° o proveedor..." icon="magnifying-glass" />
        </div>
        <div class="flex items-center gap-1 rounded-xl bg-white p-1 shadow-sm overflow-x-auto">
            @foreach(['all' => 'Todos', 'borrador' => 'Borrador', 'enviada' => 'Enviada', 'aprobada' => 'Aprobada', 'recibida_parcial' => 'Parcial', 'recibida' => 'Recibida', 'cancelada' => 'Cancelada'] as $key => $label)
                <button wire:click="$set('filterEstado', '{{ $key }}')"
                    @class(['rounded-lg px-3 py-1.5 text-xs font-semibold transition whitespace-nowrap', $filterEstado === $key ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-900'])>
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Número</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Proveedor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Recepción</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($orders as $order)
                    @php $ec = $order->estado_color; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="font-mono text-sm font-medium text-gray-900">{{ $order->numero }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-700">{{ $order->supplier?->nombre ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 rounded-full bg-{{ $ec }}-50 px-2 py-0.5 text-[10px] font-semibold text-{{ $ec }}-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-{{ $ec }}-500"></span>
                                {{ $order->estado_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-600">{{ $order->fecha?->format('d/m/Y') ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="text-sm font-semibold text-gray-900">${{ number_format($order->total, 2) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($order->total_pedido > 0)
                                <div class="flex items-center justify-center gap-2">
                                    <div class="h-2 w-16 rounded-full bg-gray-100">
                                        <div class="h-2 rounded-full bg-{{ $ec }}-500" style="width: {{ $order->porcentaje_recepcion }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-gray-500">{{ $order->porcentaje_recepcion }}%</span>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.ordenes-compra.edit', $order->id) }}" wire:navigate>
                                    <flux:button variant="ghost" size="sm" class="!text-gray-400 hover:!text-cyan-600">
                                        <iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon>
                                    </flux:button>
                                </a>
                                @if ($order->pendiente_recepcion)
                                    <a href="{{ route('admin.ordenes-compra.recibir', $order->id) }}" wire:navigate>
                                        <flux:button variant="ghost" size="sm" class="!text-gray-400 hover:!text-emerald-600" title="Recibir mercancía">
                                            <iconify-icon icon="heroicons:arrow-down-tray" class="h-4 w-4"></iconify-icon>
                                        </flux:button>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                                    <iconify-icon icon="heroicons:document-text" class="h-6 w-6 text-gray-400"></iconify-icon>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-900">No hay órdenes de compra</p>
                                <p class="text-xs text-gray-500">Crea tu primera orden para comenzar</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</div>
