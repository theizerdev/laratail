<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Crear Nuevo Grupo</h2>
            <p class="mt-1 text-sm text-gray-500">Completa los campos para crear un nuevo grupo.</p>
        </div>
        <a href="{{ route('admin.grupos') }}">
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
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <flux:input
                        wire:model="name"
                        label="Nombre del Grupo"
                        placeholder="Ej: Gerencia, Departamento, Área"
                        :error="$errors->first('name')"
                        required
                    />

                    <div>
                        <flux:label>Estado</flux:label>
                        <div class="mt-2 flex items-center gap-3">
                            <label class="flex cursor-pointer items-center gap-2">
                                <input
                                    type="radio"
                                    name="is_active"
                                    value="1"
                                    wire:model="is_active"
                                    class="h-4 w-4 border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                >
                                <span class="text-sm font-medium text-gray-700">Activo</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2">
                                <input
                                    type="radio"
                                    name="is_active"
                                    value="0"
                                    wire:model="is_active"
                                    class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500"
                                >
                                <span class="text-sm font-medium text-gray-700">Inactivo</span>
                            </label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Los grupos inactivos no aparecerán en las listas.</p>
                    </div>
                </div>

                <div>
                    <flux:input
                        wire:model="description"
                        label="Descripción"
                        placeholder="Descripción opcional del grupo"
                        :error="$errors->first('description')"
                    />
                </div>

                {{-- Roles Section --}}
                <div>
                    <flux:label>Roles</flux:label>
                    <p class="mt-1 text-xs text-gray-500 mb-3">
                        Selecciona los roles que se asignarán por defecto a los usuarios de este grupo.
                    </p>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($roles as $role)
                            <label
                                class="flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 transition"
                                @class([
                                    'border-violet-300 bg-violet-50' => in_array((string) $role->id, $selectedRoles),
                                    'border-gray-200 hover:border-gray-300' => !in_array((string) $role->id, $selectedRoles),
                                ])
                            >
                                <input
                                    type="checkbox"
                                    value="{{ $role->id }}"
                                    wire:model="selectedRoles"
                                    class="h-4 w-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                                >
                                <span class="text-sm font-medium text-gray-700">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('admin.grupos') }}">
                        <flux:button variant="ghost" type="button">
                            Cancelar
                        </flux:button>
                    </a>
                    <flux:button type="submit" class="!bg-violet-500 hover:!bg-violet-600">
                        <iconify-icon icon="heroicons:check-circle" class="h-4 w-4"></iconify-icon>
                        Crear Grupo
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</div>