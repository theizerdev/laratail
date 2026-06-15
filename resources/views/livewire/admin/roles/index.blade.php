<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Roles</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona los roles y sus permisos.</p>
        </div>
        @can('roles.create')
            <a href="{{ route('admin.roles.create') }}">
                <flux:button variant="primary" class="!bg-indigo-500 hover:!bg-indigo-600">
                    <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
                    Nuevo Rol
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
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Total Roles</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50">
                    <iconify-icon icon="heroicons:shield-check" class="h-5 w-5 text-indigo-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['total_roles'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Roles registrados</p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Permisos</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50">
                    <iconify-icon icon="heroicons:key" class="h-5 w-5 text-amber-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['total_permissions'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Permisos disponibles</p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Sectores</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50">
                    <iconify-icon icon="heroicons:cube" class="h-5 w-5 text-violet-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['total_sectors'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Sectores de permisos</p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Usuarios</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50">
                    <iconify-icon icon="heroicons:users" class="h-5 w-5 text-blue-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Usuarios totales</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="mb-5">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar roles..."
            icon="magnifying-glass"
        />
    </div>

    {{-- Roles Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rol</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Permisos</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Usuarios</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($roles as $role)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-xs font-bold text-indigo-700">
                                    {{ strtoupper(substr($role->name, 0, 2)) }}
                                </div>
                                <span class="font-medium text-gray-900">{{ $role->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse($role->permissions->take(4) as $permission)
                                    <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600">
                                        {{ $permission->slug ?? $permission->name }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-400">Sin permisos</span>
                                @endforelse
                                @if($role->permissions->count() > 4)
                                    <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600">
                                        +{{ $role->permissions->count() - 4 }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-600">{{ $usersPerRole[$role->id] ?? 0 }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('roles.edit')
                                    <a href="{{ route('admin.roles.edit', $role->id) }}">
                                        <flux:button variant="ghost" size="sm" class="!text-gray-400 hover:!text-indigo-600">
                                            <iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon>
                                        </flux:button>
                                    </a>
                                @endcan
                                @can('roles.delete')
                                    @if($role->name !== 'super-admin')
                                        <flux:button variant="ghost" size="sm" wire:click="delete({{ $role->id }})" wire:confirm="¿Eliminar el rol '{{ $role->name }}'?" class="!text-gray-400 hover:!text-red-600">
                                            <iconify-icon icon="heroicons:trash" class="h-4 w-4"></iconify-icon>
                                        </flux:button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                                    <iconify-icon icon="heroicons:shield-check" class="h-6 w-6 text-gray-400"></iconify-icon>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-900">No hay roles</p>
                                <p class="text-xs text-gray-500">Crea tu primer rol para comenzar</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>


</div>