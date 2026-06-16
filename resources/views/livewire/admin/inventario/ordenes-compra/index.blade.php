<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Órdenes de Compra</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestiona las órdenes de compra a proveedores.</p>
        </div>
        <a href="{{ route('admin.ordenes-compra.create') }}" class="inline-flex items-center gap-1 rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700">
            <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon> Nueva Orden
        </a>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Órdenes</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Borradores</p>
            <p class="text-2xl font-bold text-gray-600 dark:text-gray-300">{{ number_format($stats['borrador']) }}</p>
        </div>
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/30">
            <p class="text-sm text-blue-600 dark:text-blue-400">Pendientes</p>
            <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ number_format($stats['pendientes']) }}</p>
        </div>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/30">
            <p class="text-sm text-emerald-600 dark:text-emerald-400">Recibidas</p>
            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($stats['recibidas']) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1 max-w-md">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por número o proveedor..."
                class="w-full rounded-lg border-gray-300 pl-10 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
             <iconify-icon icon="heroicons:magnifying-glass" class="absolute left-3 top-2.5 h-5 w-5 text-gray-400"></iconify-icon>
        </div>
        <select wire:model.live="filterEstado" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="all">Todos los estados</option>
            <option value="borrador">Borrador</option>
            <option value="enviada">Enviada</option>
            <option value="aprobada">Aprobada</option>
            <option value="recibida_parcial">Recibida Parcial</option>
            <option value="recibida">Recibida</option>
            <option value="cancelada">Cancelada</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Número</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Proveedor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Fecha</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Total</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Recepción</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($orders as $order)
                    @php $ec = $order->estado_color; @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="text-sm font-semibold text-cyan-600 dark:text-cyan-400">{{ $order->numero }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $order->supplier?->nombre ?? 'N/A' }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="inline-flex items-center rounded-full bg-{{ $ec }}-100 px-2.5 py-0.5 text-xs font-medium text-{{ $ec }}-800 dark:bg-{{ $ec }}-900/30 dark:text-{{ $ec }}-400">
                                {{ $order->estado_label }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $order->fecha?->format('d/m/Y') ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($order->total, 2) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-center">
                            @if ($order->total_pedido > 0)
                                <div class="flex items-center justify-center gap-2">
                                    <div class="h-2 w-16 rounded-full bg-gray-200 dark:bg-gray-600">
                                        <div class="h-2 rounded-full bg-{{ $ec }}-500" style="width: {{ $order->porcentaje_recepcion }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $order->porcentaje_recepcion }}%</span>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.ordenes-compra.edit', $order->id) }}" class="text-gray-600 hover:text-cyan-600 dark:text-gray-400">
                                   <iconify-icon icon="heroicons:pencil-square" class="h-5 w-5"></iconify-icon>
                                </a>
                                @if ($order->pendiente_recepcion)
                                    <a href="{{ route('admin.ordenes-compra.recibir', $order->id) }}" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400" title="Recibir mercancía">
                                        <iconify-icon icon="heroicons:arrow-down-tray" class="h-5 w-5"></iconify-icon>
                                        
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No se encontraron órdenes de compra.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</div>
