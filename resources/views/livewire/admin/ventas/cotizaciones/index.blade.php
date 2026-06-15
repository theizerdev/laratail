<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Cotizaciones</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona cotizaciones y conviértelas en pedidos.</p>
        </div>
        @can('cotizaciones.create')
            <a href="{{ route('admin.cotizaciones.create') }}" wire:navigate>
                <flux:button variant="primary" class="!bg-amber-500 hover:!bg-amber-600"><iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>Nueva Cotización</flux:button>
            </a>
        @endcan
    </div>

    @if (session()->has('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="mb-6 grid grid-cols-3 gap-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-400">Total</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-400">Pendientes</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['pendientes'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-400">Convertidas</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['convertidas'] }}</p>
        </div>
    </div>

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs"><flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar..." icon="magnifying-glass" /></div>
        <div class="flex items-center gap-1 rounded-xl bg-white p-1 shadow-sm">
            @foreach(['all' => 'Todas', 'borrador' => 'Borrador', 'pendiente' => 'Pendiente', 'confirmado' => 'Confirmado'] as $key => $label)
                <button wire:click="$set('filter', '{{ $key }}')" @class(['rounded-lg px-3 py-1.5 text-xs font-semibold transition', $filter === $key ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-900'])>{{ $label }}</button>
            @endforeach
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">N°</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Items</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($cotizaciones as $cot)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><span class="font-mono text-sm font-medium">{{ $cot->numero }}</span><p class="text-xs text-gray-400">{{ $cot->created_at->format('d/m/Y') }}</p></td>
                        <td class="px-4 py-3">{{ $cot->customer?->nombre_completo ?? 'Sin cliente' }}</td>
                        <td class="px-4 py-3"><span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700">{{ $cot->items_count }}</span></td>
                        <td class="px-4 py-3 font-semibold">${{ number_format($cot->total, 2) }}</td>
                        <td class="px-4 py-3"><span class="inline-flex items-center gap-1 rounded-full bg-{{ $cot->estado_color }}-50 px-2 py-0.5 text-[10px] font-semibold text-{{ $cot->estado_color }}-700"><span class="h-1.5 w-1.5 rounded-full bg-{{ $cot->estado_color }}-500"></span>{{ $cot->estado_label }}</span></td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('cotizaciones.convert')
                                    @if(in_array($cot->estado, ['borrador', 'pendiente', 'confirmado']))
                                        <flux:button variant="ghost" size="sm" wire:click="convertirAPedido({{ $cot->id }})" wire:confirm="¿Convertir esta cotización a pedido?" class="!text-emerald-600 hover:!text-emerald-700" title="Convertir a pedido">
                                            <iconify-icon icon="heroicons:arrow-right-circle" class="h-4 w-4"></iconify-icon>
                                        </flux:button>
                                    @endif
                                @endcan
                                @can('cotizaciones.edit')
                                    <a href="{{ route('admin.cotizaciones.edit', $cot->id) }}" wire:navigate><flux:button variant="ghost" size="sm" class="!text-gray-400 hover:!text-amber-600"><iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon></flux:button></a>
                                @endcan
                                @can('cotizaciones.delete')
                                    <flux:button variant="ghost" size="sm" wire:click="delete({{ $cot->id }})" wire:confirm="¿Eliminar?" class="!text-gray-400 hover:!text-red-600"><iconify-icon icon="heroicons:trash" class="h-4 w-4"></iconify-icon></flux:button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-gray-400">No hay cotizaciones</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $cotizaciones->links() }}</div>
</div>
