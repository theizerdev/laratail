<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Grupos</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona los grupos y sus roles asignados.</p>
        </div>
        <a href="{{ route('admin.grupos.create') }}">
            <flux:button variant="primary" class="!bg-emerald-500 hover:!bg-emerald-600">
                <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
                Nuevo Grupo
            </flux:button>
        </a>
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
        {{-- Total Groups --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Total Grupos</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50">
                    <iconify-icon icon="heroicons:user-group" class="h-5 w-5 text-emerald-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['total_groups'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Grupos registrados</p>
        </div>

        {{-- Active Groups --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Activos</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50">
                    <iconify-icon icon="heroicons:check-badge" class="h-5 w-5 text-blue-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['active_groups'] }}</p>
            <p class="mt-1 text-xs text-gray-400">
                <span class="text-emerald-600 font-medium">
                    {{ $stats['total_groups'] > 0 ? round(($stats['active_groups'] / $stats['total_groups']) * 100) : 0 }}%
                </span>
                del total
            </p>
        </div>

        {{-- Users in Groups --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Usuarios</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50">
                    <iconify-icon icon="heroicons:users" class="h-5 w-5 text-violet-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Asignados a grupos</p>
        </div>

        {{-- Total Roles --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Roles</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50">
                    <iconify-icon icon="heroicons:shield-check" class="h-5 w-5 text-amber-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['total_roles'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Roles disponibles</p>
        </div>
    </div>

    {{-- Search + Filters --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar grupos..."
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
                Todos
            </button>
            <button
                wire:click="$set('filter', 'active')"
                @class([
                    'rounded-lg px-3.5 py-1.5 text-xs font-semibold transition',
                    $filter === 'active' ? 'bg-emerald-500 text-white' : 'text-gray-500 hover:text-gray-900',
                ])
            >
                Activos
            </button>
            <button
                wire:click="$set('filter', 'inactive')"
                @class([
                    'rounded-lg px-3.5 py-1.5 text-xs font-semibold transition',
                    $filter === 'inactive' ? 'bg-gray-300 text-gray-700' : 'text-gray-500 hover:text-gray-900',
                ])
            >
                Inactivos
            </button>
        </div>
    </div>

    {{-- Groups Card List --}}
    @if ($groups->count() > 0)
        <div class="space-y-3">
            @foreach ($groups as $group)
                <div
                    wire:key="group-{{ $group->id }}"
                    class="group rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md"
                >
                    <div class="flex items-start gap-4">
                        {{-- Group Avatar --}}
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-base font-bold text-emerald-700">
                            {{ strtoupper(substr($group->name, 0, 2)) }}
                        </div>

                        {{-- Info --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-gray-900">{{ $group->name }}</h3>
                                {{-- Status pill --}}
                                <button
                                    wire:click="toggleStatus({{ $group->id }})"
                                    @class([
                                        'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold transition',
                                        $group->is_active
                                            ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                            : 'bg-gray-100 text-gray-500 hover:bg-gray-200',
                                    ])
                                >
                                    <span @class([
                                        'h-1.5 w-1.5 rounded-full',
                                        $group->is_active ? 'bg-emerald-500' : 'bg-gray-400',
                                    ])></span>
                                    {{ $group->is_active ? 'Activo' : 'Inactivo' }}
                                </button>
                            </div>

                            @if ($group->description)
                                <p class="mt-0.5 text-xs text-gray-500 line-clamp-1">{{ $group->description }}</p>
                            @endif

                            {{-- Roles + Users + Date --}}
                            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2">
                                {{-- Roles --}}
                                <div class="flex items-center gap-1.5">
                                    <iconify-icon icon="heroicons:shield-check" class="h-3.5 w-3.5 text-gray-400"></iconify-icon>
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($group->roles as $role)
                                            <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                                {{ $role->name }}
                                            </span>
                                        @empty
                                            <span class="text-[11px] text-gray-400">Sin roles</span>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- Users --}}
                                <div class="flex items-center gap-1.5">
                                    @if ($group->users_count > 0)
                                        <div class="flex -space-x-1.5">
                                            @foreach ($group->users->take(4) as $user)
                                                <div class="flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-gray-200 text-[9px] font-bold text-gray-600" title="{{ $user->name }}">
                                                    {{ $user->initials() }}
                                                </div>
                                            @endforeach
                                        </div>
                                        <span class="text-[11px] font-medium text-gray-500">
                                            {{ $group->users_count }} {{ Str::plural('usuario', $group->users_count) }}
                                        </span>
                                    @else
                                        <iconify-icon icon="heroicons:users" class="h-3.5 w-3.5 text-gray-400"></iconify-icon>
                                        <span class="text-[11px] text-gray-400">Sin usuarios</span>
                                    @endif
                                </div>

                                {{-- Created --}}
                                <span class="text-[11px] text-gray-400">
                                    {{ $group->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex shrink-0 items-center gap-1 opacity-0 transition group-hover:opacity-100">
                            <a href="{{ route('admin.grupos.edit', $group->id) }}">
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    class="!text-gray-400 hover:!text-emerald-600"
                                >
                                    <iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon>
                                </flux:button>
                            </a>
                            <flux:button
                                variant="ghost"
                                size="sm"
                                wire:click="delete({{ $group->id }})"
                                wire:confirm="¿Estás seguro de eliminar el grupo '{{ $group->name }}'?"
                                class="!text-gray-400 hover:!text-red-600"
                            >
                                <iconify-icon icon="heroicons:trash" class="h-4 w-4"></iconify-icon>
                            </flux:button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div class="flex flex-col items-center justify-center rounded-2xl bg-white py-16 text-center shadow-sm">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-50">
                <iconify-icon icon="heroicons:user-group" class="h-8 w-8 text-gray-300"></iconify-icon>
            </div>
            <h3 class="mt-4 text-sm font-semibold text-gray-900">
                @if ($search)
                    Sin resultados para "{{ $search }}"
                @elseif ($filter !== 'all')
                    No hay grupos {{ $filter === 'active' ? 'activos' : 'inactivos' }}
                @else
                    No hay grupos creados
                @endif
            </h3>
            <p class="mt-1 text-xs text-gray-500">
                @if ($search)
                    Intenta con otro termino de busqueda.
                @elseif ($filter !== 'all')
                    Cambia el filtro para ver otros grupos.
                @else
                    Crea tu primer grupo para comenzar a organizar usuarios.
                @endif
            </p>
            @if (! $search && $filter === 'all')
                <a href="{{ route('admin.grupos.create') }}">
                    <flux:button variant="primary" class="mt-4 !bg-emerald-500 hover:!bg-emerald-600">
                        <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
                        Crear primer grupo
                    </flux:button>
                </a>
            @endif
        </div>
    @endif

    {{-- Create/Edit Modal --}}
    <flux:modal name="group-form" class="w-full max-w-lg">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900">
                {{ $editingId ? 'Editar Grupo' : 'Nuevo Grupo' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                {{ $editingId ? 'Modifica los datos del grupo.' : 'Completa los campos para crear un nuevo grupo.' }}
            </p>

            <div class="mt-6 space-y-4">
                {{-- Name --}}
                <flux:input
                    wire:model="name"
                    label="Nombre"
                    placeholder="Ej: Administradores"
                    :error="$errors->first('name')"
                />

                {{-- Description --}}
                <flux:textarea
                    wire:model="description"
                    label="Descripción"
                    placeholder="Descripción del grupo (opcional)"
                    rows="2"
                    :error="$errors->first('description')"
                />

                {{-- Roles --}}
                <div>
                    <flux:label>Roles asignados</flux:label>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        @foreach ($roles as $role)
                            <label
                                class="flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 transition"
                                @class([
                                    'border-emerald-300 bg-emerald-50' => in_array((string) $role->id, $selectedRoles),
                                    'border-gray-200 hover:border-gray-300' => ! in_array((string) $role->id, $selectedRoles),
                                ])
                            >
                                <input
                                    type="checkbox"
                                    value="{{ $role->id }}"
                                    wire:model="selectedRoles"
                                    class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                >
                                <span class="text-sm font-medium text-gray-700">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Active --}}
                <flux:checkbox wire:model="is_active" label="Grupo activo" />
            </div>

            {{-- Actions --}}
            <div class="mt-6 flex items-center justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button
                    wire:click="save"
                    class="!bg-emerald-500 hover:!bg-emerald-600"
                >
                    {{ $editingId ? 'Guardar cambios' : 'Crear grupo' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
