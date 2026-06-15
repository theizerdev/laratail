<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Pedidos</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona los pedidos y ventas de tu negocio.</p>
        </div>
        @can('pedidos.create')
            <a href="{{ route('admin.pedidos.create') }}" wire:navigate>
                <flux:button variant="primary" class="!bg-amber-500 hover:!bg-amber-600">
                    <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
                    Nuevo Pedido
                </flux:button>
            </a>
        @endcan
    </div>

    @if (session()->has('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-5">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Total</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50"><iconify-icon icon="heroicons:shopping-cart-solid" class="h-4 w-4 text-indigo-600"></iconify-icon></div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Pendientes</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-yellow-50"><iconify-icon icon="heroicons:clock-solid" class="h-4 w-4 text-yellow-600"></iconify-icon></div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['pendientes'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Completados</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50"><iconify-icon icon="heroicons:check-circle-solid" class="h-4 w-4 text-emerald-600"></iconify-icon></div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['completados'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Cancelados</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50"><iconify-icon icon="heroicons:x-circle-solid" class="h-4 w-4 text-red-500"></iconify-icon></div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['cancelados'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Ingresos Hoy</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-50"><iconify-icon icon="heroicons:currency-dollar-solid" class="h-4 w-4 text-green-600"></iconify-icon></div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($stats['ingresos_hoy'], 2) }}</p>
        </div>
    </div>

    {{-- Search + Filters --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por N° o cliente..." icon="magnifying-glass" />
        </div>
        <div class="flex items-center gap-1 rounded-xl bg-white p-1 shadow-sm overflow-x-auto">
            @foreach(['all' => 'Todos', 'pendiente' => 'Pendiente', 'confirmado' => 'Confirmado', 'procesando' => 'Procesando', 'enviado' => 'Enviado', 'entregado' => 'Entregado', 'cancelado' => 'Cancelado'] as $key => $label)
                <button wire:click="$set('filter', '{{ $key }}')" @class(['rounded-lg px-3 py-1.5 text-xs font-semibold transition whitespace-nowrap', $filter === $key ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-900'])>{{ $label }}</button>
            @endforeach
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">N° Pedido</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Items</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pago</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="font-mono text-sm font-medium text-gray-900">{{ $order->numero }}</span>
                            <p class="text-xs text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($order->customer)
                                <span class="text-sm text-gray-700">{{ $order->customer->nombre_completo }}</span>
                            @else
                                <span class="text-xs text-gray-400">Sin cliente</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700">{{ $order->items_count }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm font-semibold text-gray-900">${{ number_format($order->total, 2) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @php $color = $order->estado_color; @endphp
                            <span class="inline-flex items-center gap-1 rounded-full bg-{{ $color }}-50 px-2 py-0.5 text-[10px] font-semibold text-{{ $color }}-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-{{ $color }}-500"></span>
                                {{ $order->estado_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @php $pColor = $order->pago_color; @endphp
                            <span class="inline-flex items-center rounded-full bg-{{ $pColor }}-50 px-2 py-0.5 text-[10px] font-semibold text-{{ $pColor }}-700">
                                {{ ucfirst($order->estado_pago) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('pedidos.edit')
                                    <a href="{{ route('admin.pedidos.edit', $order->id) }}" wire:navigate>
                                        <flux:button variant="ghost" size="sm" class="!text-gray-400 hover:!text-amber-600">
                                            <iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon>
                                        </flux:button>
                                    </a>
                                @endcan
                                @can('pedidos.delete')
                                    <flux:button variant="ghost" size="sm" wire:click="delete({{ $order->id }})" wire:confirm="¿Eliminar este pedido?" class="!text-gray-400 hover:!text-red-600">
                                        <iconify-icon icon="heroicons:trash" class="h-4 w-4"></iconify-icon>
                                    </flux:button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                                    <iconify-icon icon="heroicons:shopping-cart" class="h-6 w-6 text-gray-400"></iconify-icon>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-900">No hay pedidos</p>
                                <p class="text-xs text-gray-500">Crea tu primer pedido para comenzar</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</div>
