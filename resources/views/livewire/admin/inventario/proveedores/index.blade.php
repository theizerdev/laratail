<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Proveedores</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona los proveedores de tu empresa.</p>
        </div>
        <flux:button wire:click="openCreate" variant="primary">
            <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
            Nuevo Proveedor
        </flux:button>
    </div>

    {{-- Stats Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Total</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-50">
                    <iconify-icon icon="heroicons:building-office-solid" class="h-4 w-4 text-cyan-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Activos</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50">
                    <iconify-icon icon="heroicons:check-circle-solid" class="h-4 w-4 text-emerald-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $stats['active'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Inactivos</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50">
                    <iconify-icon icon="heroicons:x-circle-solid" class="h-4 w-4 text-red-500"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-red-600">{{ $stats['inactive'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Con Órdenes</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50">
                    <iconify-icon icon="heroicons:shopping-bag-solid" class="h-4 w-4 text-amber-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $stats['with_orders'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, contacto, email, RIF..." icon="magnifying-glass" />
        </div>
        <flux:select wire:model.live="filter" class="w-auto">
            <option value="all">Todos</option>
            <option value="active">Activos</option>
            <option value="inactive">Inactivos</option>
        </flux:select>
    </div>

    {{-- Alerts --}}
    @if (session()->has('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Proveedor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Contacto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">RIF</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Órdenes</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($suppliers as $s)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-100 text-cyan-700 text-xs font-bold">
                                    {{ strtoupper(substr($s->nombre, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-800">{{ $s->nombre }}</div>
                                    <div class="text-xs text-gray-400">{{ $s->email ?? 'Sin email' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-gray-700">{{ $s->contacto ?? '-' }}</div>
                            <div class="text-xs text-gray-400">{{ $s->telefono ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $s->rif ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center rounded-full bg-cyan-50 px-2 py-0.5 text-xs font-semibold text-cyan-700">
                                {{ $s->purchase_orders_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleStatus({{ $s->id }})"
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold transition-colors {{ $s->status ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $s->status ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                {{ $s->status ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <flux:button wire:click="openEdit({{ $s->id }})" variant="ghost" size="sm" class="!text-gray-400 hover:!text-cyan-600">
                                    <iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon>
                                </flux:button>
                                <flux:button wire:click="confirmDelete({{ $s->id }})" variant="ghost" size="sm" class="!text-gray-400 hover:!text-red-600">
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
                                    <iconify-icon icon="heroicons:building-office" class="h-6 w-6 text-gray-400"></iconify-icon>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-900">No se encontraron proveedores</p>
                                <p class="text-xs text-gray-500">Crea tu primer proveedor para comenzar</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($suppliers->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">{{ $suppliers->links() }}</div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    <flux:modal name="modal-proveedor" class="min-w-[40rem]">
        <div class="p-6">
            {{-- Modal Header --}}
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $editingId ? 'Editar Proveedor' : 'Nuevo Proveedor' }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $editingId ? 'Modifica la información del proveedor' : 'Completa la información del proveedor' }}</p>
                </div>
                <flux:button variant="ghost" size="sm" wire:click="closeModal" x-on:click="Flux.modal('modal-proveedor').close()">
                    <iconify-icon icon="heroicons:x-mark" class="h-5 w-5"></iconify-icon>
                </flux:button>
            </div>

            {{-- Modal Body --}}
            <div class="space-y-5">
                {{-- Company Info --}}
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <iconify-icon icon="heroicons:building-office-solid" class="h-4 w-4 text-cyan-500"></iconify-icon>
                        Información de la Empresa
                    </h4>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <flux:input wire:model="nombre" label="Nombre / Razón Social *" placeholder="Ej: Distribuidora XYZ" />
                        <flux:input wire:model="rif" label="RIF / NIT" placeholder="J-12345678-9" />
                        <div class="sm:col-span-2">
                            <flux:input wire:model="direccion" label="Dirección" placeholder="Dirección completa" />
                        </div>
                    </div>
                </div>

                {{-- Contact Info --}}
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <iconify-icon icon="heroicons:user-solid" class="h-4 w-4 text-cyan-500"></iconify-icon>
                        Información de Contacto
                    </h4>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <flux:input wire:model="contacto" label="Persona de Contacto" placeholder="Nombre del contacto" />
                        <flux:input wire:model="telefono" label="Teléfono" placeholder="+58 412 1234567" />
                        <div class="sm:col-span-2">
                            <flux:input wire:model="email" label="Email" type="email" placeholder="proveedor@email.com" />
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <iconify-icon icon="heroicons:document-text-solid" class="h-4 w-4 text-cyan-500"></iconify-icon>
                        Notas Adicionales
                    </h4>
                    <flux:textarea wire:model="notas" label="Notas" rows="3" placeholder="Condiciones de pago, horarios de entrega, etc." />
                </div>

                {{-- Status --}}
                <div class="flex items-center gap-3 rounded-xl bg-gray-50 p-4">
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input wire:model="status" type="checkbox" class="peer sr-only" />
                        <div class="h-6 w-11 rounded-full bg-gray-300 peer-checked:bg-cyan-600 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-full"></div>
                    </label>
                    <div>
                        <span class="text-sm font-medium text-gray-700">Proveedor Activo</span>
                        <p class="text-xs text-gray-400">Desactiva para ocultar de órdenes de compra</p>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="mt-6 flex justify-end gap-2">
                <flux:button x-on:click="Flux.modal('modal-proveedor').close()">Cancelar</flux:button>
                <flux:button wire:click="save" variant="primary">
                    <iconify-icon icon="heroicons:check-circle" class="h-4 w-4"></iconify-icon>
                    {{ $editingId ? 'Actualizar' : 'Crear Proveedor' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Delete Modal --}}
    <flux:modal name="modal-delete-proveedor" class="min-w-[22rem]">
        <div class="p-6 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
                <iconify-icon icon="heroicons:exclamation-triangle-solid" class="h-7 w-7 text-red-600"></iconify-icon>
            </div>
            <h3 class="mt-4 text-lg font-bold text-gray-900">Eliminar Proveedor</h3>
            <p class="mt-2 text-sm text-gray-500">¿Estás seguro? Esta acción no se puede deshacer.</p>
            <div class="mt-6 flex gap-3">
                <flux:button x-on:click="Flux.modal('modal-delete-proveedor').close()" class="flex-1 justify-center">Cancelar</flux:button>
                <flux:button wire:click="delete" variant="primary" class="flex-1 justify-center !bg-red-600 hover:!bg-red-700">Eliminar</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
