<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Sucursales</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona las sucursales de cada empresa.</p>
        </div>
        @can('sucursales.create')
            <a href="{{ route('admin.sucursales.create') }}" wire:navigate>
                <flux:button variant="primary" class="!bg-amber-500 hover:!bg-amber-600">
                    <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
                    Nueva Sucursal
                </flux:button>
            </a>
        @endcan
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-3">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Total Sucursales</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50">
                    <iconify-icon icon="heroicons:building-storefront" class="h-5 w-5 text-amber-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['total_sucursales'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Sucursales registradas</p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Activas</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50">
                    <iconify-icon icon="heroicons:check-badge" class="h-5 w-5 text-emerald-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['sucursales_activas'] }}</p>
            <p class="mt-1 text-xs text-gray-400">
                <span class="text-emerald-600 font-medium">
                    {{ $stats['total_sucursales'] > 0 ? round(($stats['sucursales_activas'] / $stats['total_sucursales']) * 100) : 0 }}%
                </span>
                del total
            </p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Inactivas</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-red-50">
                    <iconify-icon icon="heroicons:x-circle" class="h-5 w-5 text-red-500"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['sucursales_inactivas'] }}</p>
            <p class="mt-1 text-xs text-gray-400">
                @if($stats['sucursales_inactivas'] > 0)
                    <span class="text-red-500 font-medium">Requieren atencion</span>
                @else
                    <span class="text-emerald-600 font-medium">Todas activas</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Search + Filters --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar sucursales..."
                icon="magnifying-glass"
            />
        </div>
        <div class="flex flex-wrap items-center gap-2">
            {{-- Filter by empresa --}}
            <select
                wire:model.live="filterEmpresa"
                class="rounded-lg border-gray-200 py-1.5 pl-3 pr-8 text-xs font-medium text-gray-600 focus:border-amber-500 focus:ring-amber-500"
            >
                <option value="">Todas las empresas</option>
                @foreach ($empresas as $empresa)
                    <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                @endforeach
            </select>

            <div class="flex items-center gap-1 rounded-xl bg-white p-1 shadow-sm">
                <button wire:click="$set('filter', 'all')"
                    @class(['rounded-lg px-3.5 py-1.5 text-xs font-semibold transition', $filter === 'all' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-900'])>
                    Todas
                </button>
                <button wire:click="$set('filter', 'active')"
                    @class(['rounded-lg px-3.5 py-1.5 text-xs font-semibold transition', $filter === 'active' ? 'bg-emerald-500 text-white' : 'text-gray-500 hover:text-gray-900'])>
                    Activas
                </button>
                <button wire:click="$set('filter', 'inactive')"
                    @class(['rounded-lg px-3.5 py-1.5 text-xs font-semibold transition', $filter === 'inactive' ? 'bg-gray-300 text-gray-700' : 'text-gray-500 hover:text-gray-900'])>
                    Inactivas
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sucursal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Empresa</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Contacto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ubicacion</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($sucursales as $sucursal)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-100 text-xs font-bold text-amber-700">
                                    {{ strtoupper(substr($sucursal->nombre, 0, 2)) }}
                                </div>
                                <span class="font-medium text-gray-900">{{ $sucursal->nombre }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($sucursal->empresa)
                                <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                    {{ $sucursal->empresa->razon_social }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">Sin empresa</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($sucursal->telefono)
                                <span class="text-sm text-gray-600">{{ $sucursal->telefono }}</span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($sucursal->latitud && $sucursal->longitud)
                                <span class="text-xs text-gray-500">{{ number_format($sucursal->latitud, 4) }}, {{ number_format($sucursal->longitud, 4) }}</span>
                            @else
                                <span class="text-xs text-gray-400">Sin ubicacion</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <button
                                wire:click="toggleStatus({{ $sucursal->id }})"
                                @class([
                                    'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold transition cursor-pointer',
                                    $sucursal->status
                                        ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                        : 'bg-gray-100 text-gray-500 hover:bg-gray-200',
                                ])
                            >
                                <span @class(['h-1.5 w-1.5 rounded-full', $sucursal->status ? 'bg-emerald-500' : 'bg-gray-400'])></span>
                                {{ $sucursal->status ? 'Activa' : 'Inactiva' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('sucursales.edit')
                                    <a href="{{ route('admin.sucursales.edit', $sucursal->id) }}" wire:navigate>
                                        <flux:button variant="ghost" size="sm" class="!text-gray-400 hover:!text-amber-600">
                                            <iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon>
                                        </flux:button>
                                    </a>
                                @endcan
                                @can('sucursales.delete')
                                    <flux:button variant="ghost" size="sm" wire:click="delete({{ $sucursal->id }})" wire:confirm="Estas seguro de eliminar la sucursal '{{ $sucursal->nombre }}'?" class="!text-gray-400 hover:!text-red-600">
                                        <iconify-icon icon="heroicons:trash" class="h-4 w-4"></iconify-icon>
                                    </flux:button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                                    <iconify-icon icon="heroicons:building-storefront" class="h-6 w-6 text-gray-400"></iconify-icon>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-900">No hay sucursales</p>
                                <p class="text-xs text-gray-500">Crea tu primera sucursal para comenzar</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
