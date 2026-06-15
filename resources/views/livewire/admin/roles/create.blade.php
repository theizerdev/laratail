<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Crear Nuevo Rol</h2>
            <p class="mt-1 text-sm text-gray-500">Completa los campos para crear un nuevo rol.</p>
        </div>
        <a href="{{ route('admin.roles') }}">
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
                {{-- Role Name --}}
                <div>
                    <flux:input
                        wire:model="name"
                        label="Nombre del Rol"
                        placeholder="Ej: Editor, Administrador, etc."
                        :error="$errors->first('name')"
                        required
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        El nombre debe ser único y descriptivo del rol.
                    </p>
                </div>

                {{-- Permissions Section --}}
                <div>
                    <flux:label>Permisos</flux:label>
                    <p class="mt-1 text-xs text-gray-500 mb-3">
                        Selecciona los permisos que tendrá este rol, organizados por sectores y módulos.
                    </p>

                    {{-- Select All / Clear Buttons --}}
                    <div class="flex gap-2 mb-4">
                        <button type="button" wire:click="selectAllPermissions" class="rounded-md bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-100">
                            Seleccionar todos
                        </button>
                        <button type="button" wire:click="clearPermissions" class="rounded-md bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100">
                            Limpiar selección
                        </button>
                    </div>

                    {{-- Permissions by Sector and Module --}}
                    <div class="space-y-4">
                        @foreach($permissionsBySector as $sector => $modules)
                            <div class="rounded-lg border border-gray-200 p-4">
                                {{-- Sector Header --}}
                                <div class="mb-3 border-b pb-2">
                                    <h3 class="text-sm font-semibold text-gray-900">{{ $sector }}</h3>
                                    <p class="text-xs text-gray-500 mt-1">Módulos de {{ $sector }}</p>
                                </div>

                                {{-- Modules --}}
                                <div class="space-y-4">
                                    @foreach($modules as $module => $perms)
                                        <div>
                                            {{-- Module Header --}}
                                            <h4 class="mb-2 text-xs font-medium text-gray-700">
                                                {{ ucfirst($module) }}
                                            </h4>

                                            {{-- Permissions --}}
                                            <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                                                @foreach($perms as $permission)
                                                    <label class="flex cursor-pointer items-center gap-2 rounded-md border border-gray-200 px-3 py-2 transition hover:bg-gray-50">
                                                        <input
                                                            type="checkbox"
                                                            value="{{ $permission->id }}"
                                                            wire:model="selectedPermissions"
                                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                        >
                                                        <div class="flex-1">
                                                            <span class="text-sm font-medium text-gray-700">
                                                                {{ $permission->slug ?? $permission->name }}
                                                            </span>
                                                            <p class="text-xs text-gray-500 mt-0.5">
                                                                {{ $permission->name }}
                                                            </p>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('admin.roles') }}">
                        <flux:button variant="ghost" type="button">
                            Cancelar
                        </flux:button>
                    </a>
                    <flux:button type="submit" class="!bg-indigo-500 hover:!bg-indigo-600">
                        <iconify-icon icon="heroicons:check-circle" class="h-4 w-4"></iconify-icon>
                        Crear Rol
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</div>