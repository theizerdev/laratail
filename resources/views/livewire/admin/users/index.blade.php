<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Usuarios</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona los usuarios y sus roles asignados.</p>
        </div>
        @can('users.create')
            <a href="{{ route('admin.users.create') }}">
                <flux:button variant="primary" class="!bg-blue-500 hover:!bg-blue-600">
                    <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
                    Nuevo Usuario
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
                <p class="text-sm font-medium text-gray-400">Total Usuarios</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50">
                    <iconify-icon icon="heroicons:users" class="h-5 w-5 text-blue-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Usuarios registrados</p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Con Grupo</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50">
                    <iconify-icon icon="heroicons:user-group" class="h-5 w-5 text-violet-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['users_with_group'] }}</p>
            <p class="mt-1 text-xs text-gray-400">
                <span class="text-emerald-600 font-medium">
                    {{ $stats['total_users'] > 0 ? round(($stats['users_with_group'] / $stats['total_users']) * 100) : 0 }}%
                </span>
                del total
            </p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Grupos</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50">
                    <iconify-icon icon="heroicons:rectangle-group" class="h-5 w-5 text-emerald-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['total_groups'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Grupos activos</p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Roles</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50">
                    <iconify-icon icon="heroicons:shield-check" class="h-5 w-5 text-indigo-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['total_roles'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Roles disponibles</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="mb-5">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar usuarios..."
            icon="magnifying-glass"
        />
    </div>

    {{-- Users Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Usuario</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Contacto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Empresa / Sucursal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Grupo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Roles</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">
                                    {{ $user->initials() }}
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900">{{ $user->name }}</span>
                                    <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($user->telefono)
                                <div class="flex items-center gap-1 text-sm text-gray-600">
                                    <iconify-icon icon="heroicons:phone" class="h-3.5 w-3.5 text-gray-400"></iconify-icon>
                                    {{ $user->telefono }}
                                </div>
                            @else
                                <span class="text-xs text-gray-400">Sin telefono</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($user->empresa)
                                <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                    {{ $user->empresa->razon_social }}
                                </span>
                                @if($user->sucursal)
                                    <p class="mt-0.5 text-[10px] text-gray-400">{{ $user->sucursal->nombre }}</p>
                                @endif
                            @else
                                <span class="text-xs text-gray-400">Sin empresa</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($user->group)
                                <span class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2 py-1 text-xs font-medium text-violet-700">
                                    <iconify-icon icon="heroicons:user-group" class="h-3 w-3"></iconify-icon>
                                    {{ $user->group->name }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">Sin grupo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse($user->roles->take(3) as $role)
                                    <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-medium text-indigo-700">
                                        {{ $role->name }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-400">Sin roles</span>
                                @endforelse
                                @if($user->roles->count() > 3)
                                    <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600">
                                        +{{ $user->roles->count() - 3 }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-600">{{ $user->email }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('users.edit')
                                    <a href="{{ route('admin.users.edit', $user->id) }}">
                                        <flux:button variant="ghost" size="sm" class="!text-gray-400 hover:!text-blue-600">
                                            <iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon>
                                        </flux:button>
                                    </a>
                                @endcan
                                @can('users.delete')
                                    @if($user->id !== auth()->id())
                                        <flux:button variant="ghost" size="sm" wire:click="delete({{ $user->id }})" wire:confirm="¿Eliminar usuario '{{ $user->name }}'?" class="!text-gray-400 hover:!text-red-600">
                                            <iconify-icon icon="heroicons:trash" class="h-4 w-4"></iconify-icon>
                                        </flux:button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                                    <iconify-icon icon="heroicons:users" class="h-6 w-6 text-gray-400"></iconify-icon>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-900">No hay usuarios</p>
                                <p class="text-xs text-gray-500">Crea tu primer usuario para comenzar</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create/Edit Modal --}}
    <flux:modal name="user-form" class="w-full max-w-lg">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900">
                {{ $editingId ? 'Editar Usuario' : 'Nuevo Usuario' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                {{ $editingId ? 'Modifica los datos del usuario.' : 'Completa los campos para crear un nuevo usuario.' }}
            </p>

            <div class="mt-6 space-y-4">
                <flux:input
                    wire:model="name"
                    label="Nombre"
                    placeholder="Nombre completo"
                    :error="$errors->first('name')"
                />

                <flux:input
                    wire:model="email"
                    label="Email"
                    type="email"
                    placeholder="correo@ejemplo.com"
                    :error="$errors->first('email')"
                />

                <flux:input
                    wire:model="telefono"
                    label="Telefono"
                    placeholder="+58 412 1234567"
                    :error="$errors->first('telefono')"
                />

                <flux:input
                    wire:model="password"
                    label="Contraseña"
                    type="password"
                    placeholder="{{ $editingId ? 'Dejar vacío para mantener' : 'Mínimo 8 caracteres' }}"
                    :error="$errors->first('password')"
                />

                <div>
                    <flux:label>Empresa</flux:label>
                    <select
                        wire:model.live="empresa_id"
                        class="mt-1 block w-full rounded-lg border-gray-300 py-2 pl-3 pr-8 text-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500"
                    >
                        <option value="">Sin empresa</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <flux:label>Sucursal</flux:label>
                    <select
                        wire:model="sucursal_id"
                        class="mt-1 block w-full rounded-lg border-gray-300 py-2 pl-3 pr-8 text-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500"
                        {{ !$empresa_id ? 'disabled' : '' }}
                    >
                        <option value="">Sin sucursal</option>
                        @forelse($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                        @empty
                            <option value="" disabled>Sin sucursales disponibles</option>
                        @endforelse
                    </select>
                </div>

                <div>
                    <flux:label>Grupo</flux:label>
                    <select
                        wire:model="group_id"
                        class="mt-1 block w-full rounded-lg border-gray-300 py-2 pl-3 pr-8 text-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500"
                    >
                        <option value="">Sin grupo</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <flux:label>Roles</flux:label>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        @foreach($roles as $role)
                            <label
                                class="flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 transition"
                                @class([
                                    'border-blue-300 bg-blue-50' => in_array((string) $role->id, $selectedRoles),
                                    'border-gray-200 hover:border-gray-300' => !in_array((string) $role->id, $selectedRoles),
                                ])
                            >
                                <input
                                    type="checkbox"
                                    value="{{ $role->id }}"
                                    wire:model="selectedRoles"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="text-sm font-medium text-gray-700">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button wire:click="save" class="!bg-blue-500 hover:!bg-blue-600">
                    {{ $editingId ? 'Guardar cambios' : 'Crear usuario' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>