<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Empleados</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona la nómina y los empleados de tu empresa.</p>
        </div>
        <flux:button variant="primary" class="!bg-amber-500 hover:!bg-amber-600" wire:click="openCreateModal">
            <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
            Nuevo Empleado
        </flux:button>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            <iconify-icon icon="heroicons:x-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('error') }}
        </div>
    @endif

    {{-- Search + Filters --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, documento..." icon="magnifying-glass" />
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nombre</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Documento / Cargo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Contacto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sucursal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($empleados as $empleado)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">
                                    {{ $empleado->initials() }}
                                </div>
                                <div>
                                    <span class="block font-medium text-gray-900">{{ $empleado->full_name }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="block text-sm text-gray-900">{{ $empleado->documento }}</span>
                            <span class="text-xs text-gray-500">{{ $empleado->cargo }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="block text-sm text-gray-600">{{ $empleado->telefono ?? 'Sin teléfono' }}</span>
                            @if($empleado->email)
                                <span class="text-xs text-gray-500">{{ $empleado->email }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-600">{{ $empleado->sucursal->nombre ?? 'Principal' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($empleado->estado === 'activo')
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Activo</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <flux:button variant="ghost" size="sm" wire:click="openEditModal({{ $empleado->id }})" class="!text-gray-400 hover:!text-amber-600">
                                    <iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon>
                                </flux:button>
                                <flux:button variant="ghost" size="sm" wire:click="delete({{ $empleado->id }})" wire:confirm="¿Estás seguro de eliminar este empleado?" class="!text-gray-400 hover:!text-red-600">
                                    <iconify-icon icon="heroicons:trash" class="h-4 w-4"></iconify-icon>
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                                    <iconify-icon icon="heroicons:user-group" class="h-6 w-6 text-gray-400"></iconify-icon>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-900">No hay empleados registrados</p>
                                <p class="text-xs text-gray-500">Agrega empleados para llevar el control de tu personal.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $empleados->links() }}</div>

    {{-- Modal Formulario Empleado --}}
    <flux:modal name="empleado-modal" class="min-w-[40rem]">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">
                {{ $empleadoId ? 'Editar Empleado' : 'Nuevo Empleado' }}
            </h2>
            
            <form wire:submit.prevent="save">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:input wire:model="nombre" label="Nombres *" placeholder="Ej. Juan" />
                    <flux:input wire:model="apellidos" label="Apellidos" placeholder="Ej. Pérez" />
                    
                    <flux:input wire:model="documento" label="Documento (Cédula/ID) *" />
                    <flux:input wire:model="cargo" label="Cargo *" placeholder="Ej. Cajero, Gerente..." />

                    <flux:input type="email" wire:model="email" label="Correo Electrónico" placeholder="opcional@ejemplo.com" />
                    <flux:input type="tel" wire:model="telefono" label="Teléfono" placeholder="+584241234567" />
                    
                    <flux:input type="date" wire:model="fecha_ingreso" label="Fecha de Ingreso" />
                    <flux:input type="number" step="0.01" wire:model="salario" label="Salario" placeholder="Ej. 500.00" />
                    
                    <flux:select wire:model="sucursal_id" label="Sucursal" placeholder="Principal">
                        <flux:select.option value="">Principal</flux:select.option>
                        @foreach($sucursales as $sucursal)
                            <flux:select.option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    
                    <flux:select wire:model="estado" label="Estado">
                        <flux:select.option value="activo">Activo</flux:select.option>
                        <flux:select.option value="inactivo">Inactivo</flux:select.option>
                    </flux:select>
                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <flux:button x-on:click="Flux.modal('empleado-modal').close()">Cancelar</flux:button>
                    <flux:button type="submit" variant="primary">Guardar Empleado</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
