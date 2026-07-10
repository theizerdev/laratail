<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Editar Usuario: {{ $user->name }}</h2>
            <p class="mt-1 text-sm text-gray-500">Modifica los datos del usuario y sus roles.</p>
        </div>
        <a href="{{ route('admin.users') }}">
            <flux:button variant="ghost">
                <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon>
                Volver
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

    {{-- Form --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm">
        <form wire:submit="save">
            <div class="space-y-6">
                {{-- Basic Info --}}
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <flux:input
                        wire:model.live="name"
                        label="Nombre Completo"
                        placeholder="Ej: Juan Pérez"
                        :error="$errors->first('name')"
                        required
                    />
                    
                    <flux:input
                        wire:model="username"
                        label="Username"
                        placeholder="jponce"
                        :error="$errors->first('username')"
                        required
                    />

                    <flux:input
                        wire:model="email"
                        label="Email"
                        type="email"
                        placeholder="correo@ejemplo.com"
                        :error="$errors->first('email')"
                        required
                    />
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <flux:input
                        wire:model="password"
                        label="Nueva Contraseña"
                        type="password"
                        placeholder="Dejar vacío para mantener"
                        :error="$errors->first('password')"
                    />

                    <flux:input
                        wire:model="telefono"
                        label="Telefono"
                        placeholder="+58 412 1234567"
                        :error="$errors->first('telefono')"
                    />
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <flux:label>Empresa</flux:label>
                        <select
                            wire:model.live="empresa_id"
                            class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 px-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500"
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
                            class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 px-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500"
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
                </div>

                <div>
                    <flux:label>Grupo</flux:label>
                    <select
                        wire:model="group_id"
                        class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 px-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500"
                    >
                        <option value="">Sin grupo</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Asigna el grupo al que pertenece el usuario.</p>
                </div>

                {{-- Current Roles Summary --}}
                <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
                    <h3 class="text-sm font-semibold text-blue-900">Roles actuales: {{ count($selectedRoles) }}</h3>
                    <p class="mt-1 text-xs text-blue-700">
                        Este usuario tiene {{ count($selectedRoles) }} rol(es) asignado(s).
                    </p>
                </div>

                {{-- Roles Section --}}
                <div>
                    <flux:label>Roles</flux:label>
                    <p class="mt-1 text-xs text-gray-500 mb-3">
                        Selecciona los roles que tendrá este usuario.
                    </p>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
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

                {{-- Form Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('admin.users') }}">
                        <flux:button variant="ghost" type="button">
                            Cancelar
                        </flux:button>
                    </a>
                    <flux:button type="submit" class="!bg-blue-500 hover:!bg-blue-600">
                        <iconify-icon icon="heroicons:check-circle" class="h-4 w-4"></iconify-icon>
                        Guardar Cambios
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</div>