<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Empresas</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona las empresas registradas en el sistema.</p>
        </div>
        @can('empresas.create')
            <a href="{{ route('admin.empresas.create') }}" wire:navigate>
                <flux:button variant="primary" class="!bg-indigo-500 hover:!bg-indigo-600">
                    <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
                    Nueva Empresa
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

    @if (session()->has('error'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            <iconify-icon icon="heroicons:exclamation-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-3">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Total Empresas</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50">
                    <iconify-icon icon="heroicons:building-office-2" class="h-5 w-5 text-indigo-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['total_empresas'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Empresas registradas</p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Activas</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50">
                    <iconify-icon icon="heroicons:check-badge" class="h-5 w-5 text-emerald-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['empresas_activas'] }}</p>
            <p class="mt-1 text-xs text-gray-400">
                <span class="text-emerald-600 font-medium">
                    {{ $stats['total_empresas'] > 0 ? round(($stats['empresas_activas'] / $stats['total_empresas']) * 100) : 0 }}%
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
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['empresas_inactivas'] }}</p>
            <p class="mt-1 text-xs text-gray-400">
                @if($stats['empresas_inactivas'] > 0)
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
                placeholder="Buscar empresas..."
                icon="magnifying-glass"
            />
        </div>
        <div class="flex items-center gap-1 rounded-xl bg-white p-1 shadow-sm">
            <button
                wire:click="$set('filter', 'all')"
                @class([
                    'rounded-lg px-3.5 py-1.5 text-xs font-semibold transition',
                    $filter === 'all' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-900',
                ])
            >
                Todas
            </button>
            <button
                wire:click="$set('filter', 'active')"
                @class([
                    'rounded-lg px-3.5 py-1.5 text-xs font-semibold transition',
                    $filter === 'active' ? 'bg-emerald-500 text-white' : 'text-gray-500 hover:text-gray-900',
                ])
            >
                Activas
            </button>
            <button
                wire:click="$set('filter', 'inactive')"
                @class([
                    'rounded-lg px-3.5 py-1.5 text-xs font-semibold transition',
                    $filter === 'inactive' ? 'bg-gray-300 text-gray-700' : 'text-gray-500 hover:text-gray-900',
                ])
            >
                Inactivas
            </button>
        </div>
    </div>

    {{-- Empresas Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Empresa</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Documento</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Contacto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pais</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($empresas as $empresa)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($empresa->logo)
                                    <div class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-white">
                                        <img src="{{ asset('storage/' . $empresa->logo) }}" alt="{{ $empresa->razon_social }}" class="h-full w-full object-contain p-0.5" />
                                    </div>
                                @else
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-xs font-bold text-indigo-700">
                                        {{ strtoupper(substr($empresa->razon_social, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <span class="font-medium text-gray-900">{{ $empresa->razon_social }}</span>
                                    @if($empresa->representante_legal)
                                        <p class="text-xs text-gray-400">{{ $empresa->representante_legal }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-mono font-medium text-gray-700">
                                {{ $empresa->documento }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($empresa->email || $empresa->telefono)
                                @if($empresa->email)
                                    <span class="text-sm text-gray-600">{{ $empresa->email }}</span>
                                @endif
                                @if($empresa->telefono)
                                    <p class="text-xs text-gray-400">{{ $empresa->telefono }}</p>
                                @endif
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($empresa->pais)
                                <div class="flex items-center gap-1.5">
                                    <span class="rounded-md bg-cyan-50 px-2 py-0.5 text-xs font-medium text-cyan-700">
                                        {{ $empresa->pais->codigo_iso2 }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $empresa->pais->nombre }}</span>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">Sin pais</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <button
                                wire:click="toggleStatus({{ $empresa->id }})"
                                @class([
                                    'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold transition cursor-pointer',
                                    $empresa->status
                                        ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                        : 'bg-gray-100 text-gray-500 hover:bg-gray-200',
                                ])
                            >
                                <span @class([
                                    'h-1.5 w-1.5 rounded-full',
                                    $empresa->status ? 'bg-emerald-500' : 'bg-gray-400',
                                ])></span>
                                {{ $empresa->status ? 'Activa' : 'Inactiva' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('empresas.edit')
                                    <a href="{{ route('admin.empresas.edit', $empresa->id) }}" wire:navigate>
                                        <flux:button variant="ghost" size="sm" class="!text-gray-400 hover:!text-indigo-600">
                                            <iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon>
                                        </flux:button>
                                    </a>
                                @endcan
                                @can('empresas.delete')
                                    <flux:button variant="ghost" size="sm" wire:click="delete({{ $empresa->id }})" wire:confirm="Estas seguro de eliminar la empresa '{{ $empresa->razon_social }}'?" class="!text-gray-400 hover:!text-red-600">
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
                                    <iconify-icon icon="heroicons:building-office-2" class="h-6 w-6 text-gray-400"></iconify-icon>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-900">No hay empresas</p>
                                <p class="text-xs text-gray-500">Crea tu primera empresa para comenzar</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
