<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Proveedores</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona los proveedores de tu empresa.</p>
        </div>
        <button wire:click="openCreate" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700 transition-colors">
            <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
            Nuevo Proveedor
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-100 text-cyan-600">
                    <iconify-icon icon="heroicons:building-office-solid" class="h-5 w-5"></iconify-icon>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Total</p>
                    <p class="text-lg font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                    <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5"></iconify-icon>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Activos</p>
                    <p class="text-lg font-bold text-emerald-600">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600">
                    <iconify-icon icon="heroicons:x-circle-solid" class="h-5 w-5"></iconify-icon>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Inactivos</p>
                    <p class="text-lg font-bold text-red-600">{{ $stats['inactive'] }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <iconify-icon icon="heroicons:shopping-bag-solid" class="h-5 w-5"></iconify-icon>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Con Órdenes</p>
                    <p class="text-lg font-bold text-amber-600">{{ $stats['with_orders'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="flex-1">
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
    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Proveedor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contacto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">RIF</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Órdenes</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
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
                            <span class="inline-flex items-center rounded-full bg-cyan-100 px-2 py-0.5 text-xs font-semibold text-cyan-700">
                                {{ $s->purchase_orders_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleStatus({{ $s->id }})" class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold transition-colors {{ $s->status ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                <div class="h-1.5 w-1.5 rounded-full {{ $s->status ? 'bg-emerald-500' : 'bg-gray-400' }}"></div>
                                {{ $s->status ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="openEdit({{ $s->id }})" class="rounded-lg p-1.5 text-gray-400 hover:text-cyan-600 hover:bg-cyan-50 transition-colors">
                                    <iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon>
                                </button>
                                <button wire:click="confirmDelete({{ $s->id }})" class="rounded-lg p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <iconify-icon icon="heroicons:trash" class="h-4 w-4"></iconify-icon>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                            <iconify-icon icon="heroicons:building-office" class="h-10 w-10 mx-auto mb-2 text-gray-300"></iconify-icon>
                            <p class="font-medium">No se encontraron proveedores</p>
                            <p class="text-xs mt-1">Crea tu primer proveedor para comenzar</p>
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
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-4 pt-16" wire:click.self="closeModal">
            <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl" wire:click.stop>
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">{{ $editingId ? 'Editar Proveedor' : 'Nuevo Proveedor' }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $editingId ? 'Modifica la información del proveedor' : 'Completa la información del proveedor' }}</p>
                    </div>
                    <button wire:click="closeModal" class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                        <iconify-icon icon="heroicons:x-mark" class="h-5 w-5"></iconify-icon>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="max-h-[70vh] overflow-y-auto px-6 py-5 space-y-5">
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
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4">
                    <button wire:click="closeModal" class="rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">Cancelar</button>
                    <button wire:click="save" class="rounded-xl bg-cyan-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700 transition-colors">
                        <iconify-icon icon="heroicons:check-circle" class="h-4 w-4 inline mr-1"></iconify-icon>
                        {{ $editingId ? 'Actualizar' : 'Crear Proveedor' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click.self="$set('showDeleteModal', false)">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl" wire:click.stop>
                <div class="text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
                        <iconify-icon icon="heroicons:exclamation-triangle-solid" class="h-7 w-7 text-red-600"></iconify-icon>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-gray-900">Eliminar Proveedor</h3>
                    <p class="mt-2 text-sm text-gray-500">¿Estás seguro? Esta acción no se puede deshacer.</p>
                </div>
                <div class="mt-6 flex gap-3">
                    <button wire:click="$set('showDeleteModal', false)" class="flex-1 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">Cancelar</button>
                    <button wire:click="delete" class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors">Eliminar</button>
                </div>
            </div>
        </div>
    @endif
</div>
