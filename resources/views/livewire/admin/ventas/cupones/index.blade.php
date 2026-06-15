<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Cupones</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona cupones de descuento para tus clientes.</p>
        </div>
        @can('cupones.create')
            <flux:button variant="primary" wire:click="openCreate" class="!bg-amber-500 hover:!bg-amber-600"><iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>Nuevo Cupón</flux:button>
        @endcan
    </div>

    @if (session()->has('success'))<div class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"><iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5"></iconify-icon>{{ session('success') }}</div>@endif

    <div class="mb-6 grid grid-cols-4 gap-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-xs font-medium text-gray-400">Total</p><p class="mt-2 text-2xl font-bold">{{ $stats['total'] }}</p></div>
        <div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-xs font-medium text-gray-400">Activos</p><p class="mt-2 text-2xl font-bold text-emerald-600">{{ $stats['active'] }}</p></div>
        <div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-xs font-medium text-gray-400">Inactivos</p><p class="mt-2 text-2xl font-bold text-gray-400">{{ $stats['inactive'] }}</p></div>
        <div class="rounded-2xl bg-white p-5 shadow-sm"><p class="text-xs font-medium text-gray-400">Usos Totales</p><p class="mt-2 text-2xl font-bold text-indigo-600">{{ $stats['total_usos'] }}</p></div>
    </div>

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs"><flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por código o nombre..." icon="magnifying-glass" /></div>
        <div class="flex items-center gap-1 rounded-xl bg-white p-1 shadow-sm">
            @foreach(['all' => 'Todos', 'active' => 'Activos', 'inactive' => 'Inactivos'] as $key => $label)
                <button wire:click="$set('filter', '{{ $key }}')" @class(['rounded-lg px-3 py-1.5 text-xs font-semibold transition', $filter === $key ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-900'])>{{ $label }}</button>
            @endforeach
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Código</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nombre</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Descuento</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Usos</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Vigencia</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($coupons as $coupon)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><code class="rounded bg-gray-100 px-2 py-1 text-xs font-mono font-bold text-gray-800">{{ $coupon->codigo }}</code></td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $coupon->nombre }}</td>
                        <td class="px-4 py-3">
                            <span class="text-sm font-semibold">{{ $coupon->tipo === 'porcentaje' ? $coupon->valor . '%' : '$' . number_format($coupon->valor, 2) }}</span>
                            @if($coupon->compra_minima > 0)<p class="text-xs text-gray-400">Min: ${{ number_format($coupon->compra_minima, 2) }}</p>@endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm">{{ $coupon->usos_actuales }}</span>
                            @if($coupon->usos_maximos)<span class="text-xs text-gray-400"> / {{ $coupon->usos_maximos }}</span>@endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            @if($coupon->fecha_inicio){{ $coupon->fecha_inicio->format('d/m/Y') }}@endif
                            @if($coupon->fecha_fin) - {{ $coupon->fecha_fin->format('d/m/Y') }}@endif
                            @if(!$coupon->fecha_inicio && !$coupon->fecha_fin)<span class="text-gray-400">Sin límite</span>@endif
                        </td>
                        <td class="px-4 py-3">
                            @can('cupones.edit')
                                <button wire:click="toggleStatus({{ $coupon->id }})" @class(['inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold transition cursor-pointer', $coupon->activo ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'])>
                                    <span @class(['h-1.5 w-1.5 rounded-full', $coupon->activo ? 'bg-emerald-500' : 'bg-gray-400'])></span>{{ $coupon->activo ? 'Activo' : 'Inactivo' }}
                                </button>
                            @else
                                <span @class(['inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold', $coupon->activo ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'])>{{ $coupon->activo ? 'Activo' : 'Inactivo' }}</span>
                            @endcan
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('cupones.edit')<flux:button variant="ghost" size="sm" wire:click="openEdit({{ $coupon->id }})" class="!text-gray-400 hover:!text-amber-600"><iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon></flux:button>@endcan
                                @can('cupones.delete')<flux:button variant="ghost" size="sm" wire:click="confirmDelete({{ $coupon->id }})" class="!text-gray-400 hover:!text-red-600"><iconify-icon icon="heroicons:trash" class="h-4 w-4"></iconify-icon></flux:button>@endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-gray-400">No hay cupones</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $coupons->links() }}</div>

    {{-- Modal Create/Edit --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>
            <div class="relative z-10 w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl mx-4">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-lg font-bold">{{ $editingId ? 'Editar Cupón' : 'Nuevo Cupón' }}</h3>
                    <button wire:click="closeModal" class="rounded-lg p-1 hover:bg-gray-100"><iconify-icon icon="heroicons:x-mark" class="h-5 w-5 text-gray-400"></iconify-icon></button>
                </div>
                <div class="space-y-4">
                    <div class="flex gap-2">
                        <div class="flex-1"><flux:input wire:model="codigo" label="Código *" placeholder="DESCUENTO10" :error="$errors->first('codigo')" /></div>
                        <div class="pt-6"><flux:button variant="ghost" wire:click="generarCodigo" class="!text-amber-600"><iconify-icon icon="heroicons:arrow-path" class="h-4 w-4"></iconify-icon>Generar</flux:button></div>
                    </div>
                    <flux:input wire:model="nombre" label="Nombre *" placeholder="Descuento de bienvenida" :error="$errors->first('nombre')" />
                    <flux:textarea wire:model="descripcion" label="Descripción" rows="2" />
                    <div class="grid grid-cols-2 gap-4">
                        <flux:select wire:model="tipo" label="Tipo">
                            <option value="porcentaje">Porcentaje (%)</option>
                            <option value="fijo">Monto Fijo ($)</option>
                        </flux:select>
                        <flux:input wire:model="valor" type="number" step="0.01" label="Valor *" :error="$errors->first('valor')" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input wire:model="compra_minima" type="number" step="0.01" label="Compra mínima" />
                        <flux:input wire:model="descuento_maximo" type="number" step="0.01" label="Descuento máximo" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input wire:model="usos_maximos" type="number" label="Usos máximos" />
                        <flux:input wire:model="usos_por_cliente" type="number" label="Usos por cliente" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input wire:model="fecha_inicio" type="datetime-local" label="Fecha inicio" />
                        <flux:input wire:model="fecha_fin" type="datetime-local" label="Fecha fin" />
                    </div>
                    <div class="flex gap-4">
                        <flux:checkbox wire:model="activo" label="Activo" />
                        <flux:checkbox wire:model="acumular" label="Acumulable" />
                    </div>
                </div>
                <div class="mt-6 flex items-center justify-end gap-3">
                    <flux:button variant="ghost" wire:click="closeModal">Cancelar</flux:button>
                    <flux:button wire:click="save" class="!bg-amber-500 hover:!bg-amber-600"><iconify-icon icon="heroicons:check-circle" class="h-4 w-4"></iconify-icon>{{ $editingId ? 'Actualizar' : 'Crear' }}</flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('showDeleteModal', false)"></div>
            <div class="relative z-10 w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl mx-4">
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100"><iconify-icon icon="heroicons:exclamation-triangle-solid" class="h-6 w-6 text-red-600"></iconify-icon></div>
                    <h3 class="mt-4 text-lg font-bold">Eliminar Cupón</h3>
                    <p class="mt-2 text-sm text-gray-500">¿Estás seguro?</p>
                </div>
                <div class="mt-6 flex items-center justify-center gap-3">
                    <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">Cancelar</flux:button>
                    <flux:button wire:click="delete" class="!bg-red-500 hover:!bg-red-600 !text-white">Eliminar</flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
