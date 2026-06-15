<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Clientes</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona tus clientes y crea cuentas de usuario automáticamente.</p>
        </div>
        @can('clientes.create')
            <flux:button variant="primary" wire:click="openCreate" class="!bg-amber-500 hover:!bg-amber-600">
                <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
                Nuevo Cliente
            </flux:button>
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
            <iconify-icon icon="heroicons:x-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-5">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Total</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50">
                    <iconify-icon icon="heroicons:user-group-solid" class="h-4 w-4 text-amber-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Activos</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50">
                    <iconify-icon icon="heroicons:check-badge-solid" class="h-4 w-4 text-emerald-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['active'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Inactivos</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50">
                    <iconify-icon icon="heroicons:x-circle-solid" class="h-4 w-4 text-red-500"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['inactive'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Con Cuenta</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50">
                    <iconify-icon icon="heroicons:user-circle-solid" class="h-4 w-4 text-blue-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['with_user'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Pedidos</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                    <iconify-icon icon="heroicons:shopping-cart-solid" class="h-4 w-4 text-indigo-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
        </div>
    </div>

    {{-- Search + Filters --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar clientes..." icon="magnifying-glass" />
        </div>
        <div class="flex items-center gap-1 rounded-xl bg-white p-1 shadow-sm">
            <button wire:click="$set('filter', 'all')" @class(['rounded-lg px-3 py-1.5 text-xs font-semibold transition', $filter === 'all' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-900'])>Todos</button>
            <button wire:click="$set('filter', 'active')" @class(['rounded-lg px-3 py-1.5 text-xs font-semibold transition', $filter === 'active' ? 'bg-emerald-500 text-white' : 'text-gray-500 hover:text-gray-900'])>Activos</button>
            <button wire:click="$set('filter', 'inactive')" @class(['rounded-lg px-3 py-1.5 text-xs font-semibold transition', $filter === 'inactive' ? 'bg-gray-300 text-gray-700' : 'text-gray-500 hover:text-gray-900'])>Inactivos</button>
            <button wire:click="$set('filter', 'with_user')" @class(['rounded-lg px-3 py-1.5 text-xs font-semibold transition', $filter === 'with_user' ? 'bg-blue-500 text-white' : 'text-gray-500 hover:text-gray-900'])>Con cuenta</button>
            <button wire:click="$set('filter', 'without_user')" @class(['rounded-lg px-3 py-1.5 text-xs font-semibold transition', $filter === 'without_user' ? 'bg-purple-500 text-white' : 'text-gray-500 hover:text-gray-900'])>Sin cuenta</button>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Contacto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pedidos</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cuenta</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($customers as $customer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-700">
                                    {{ $customer->iniciales }}
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900">{{ $customer->nombre_completo }}</span>
                                    <p class="text-xs text-gray-400">{{ $customer->empresa_nombre ?: 'Sin empresa' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm">
                                @if($customer->email)
                                    <p class="text-gray-700">{{ $customer->email }}</p>
                                @endif
                                @if($customer->telefono)
                                    <p class="text-xs text-gray-400">{{ $customer->telefono }}</p>
                                @endif
                                @if($customer->whatsapp)
                                    <p class="text-xs text-green-600">
                                        <iconify-icon icon="mdi:whatsapp" class="h-3 w-3 inline"></iconify-icon>
                                        {{ $customer->whatsapp }}
                                    </p>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700">{{ $customer->orders_count }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($customer->tiene_cuenta)
                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700">
                                    <iconify-icon icon="heroicons:user-circle-solid" class="h-3 w-3"></iconify-icon>
                                    Usuario
                                </span>
                            @else
                                <span class="text-xs text-gray-400">Sin cuenta</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @can('clientes.edit')
                                <button wire:click="toggleStatus({{ $customer->id }})" @class(['inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold transition cursor-pointer', $customer->activo ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'])>
                                    <span @class(['h-1.5 w-1.5 rounded-full', $customer->activo ? 'bg-emerald-500' : 'bg-gray-400'])></span>
                                    {{ $customer->activo ? 'Activo' : 'Inactivo' }}
                                </button>
                            @else
                                <span @class(['inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold', $customer->activo ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'])>
                                    <span @class(['h-1.5 w-1.5 rounded-full', $customer->activo ? 'bg-emerald-500' : 'bg-gray-400'])></span>
                                    {{ $customer->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            @endcan
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('clientes.edit')
                                    <flux:button variant="ghost" size="sm" wire:click="openEdit({{ $customer->id }})" class="!text-gray-400 hover:!text-amber-600">
                                        <iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon>
                                    </flux:button>
                                @endcan
                                @can('clientes.delete')
                                    <flux:button variant="ghost" size="sm" wire:click="confirmDelete({{ $customer->id }})" class="!text-gray-400 hover:!text-red-600">
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
                                    <iconify-icon icon="heroicons:user-group" class="h-6 w-6 text-gray-400"></iconify-icon>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-900">No hay clientes</p>
                                <p class="text-xs text-gray-500">Crea tu primer cliente para comenzar</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $customers->links() }}</div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>
            <div class="relative z-10 w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl mx-4">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $editingId ? 'Editar Cliente' : 'Nuevo Cliente' }}
                    </h3>
                    <button wire:click="closeModal" class="rounded-lg p-1 hover:bg-gray-100">
                        <iconify-icon icon="heroicons:x-mark" class="h-5 w-5 text-gray-400"></iconify-icon>
                    </button>
                </div>

                <div class="space-y-5">
                    {{-- Datos Personales --}}
                    <div>
                        <h4 class="mb-3 text-xs font-bold text-gray-400 uppercase tracking-wide">Datos Personales</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <flux:input wire:model="nombre" label="Nombre *" placeholder="Nombre" :error="$errors->first('nombre')" required />
                            <flux:input wire:model="apellido" label="Apellido" placeholder="Apellido" />
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <flux:input wire:model="email" label="Email" type="email" placeholder="correo@ejemplo.com" :error="$errors->first('email')" />
                            <flux:input wire:model="telefono" label="Teléfono" placeholder="+58 412 1234567" />
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div>
                                <flux:label>Tipo Documento</flux:label>
                                <flux:select wire:model="tipo_documento">
                                    <option value="">Seleccionar...</option>
                                    <option value="V">CI / V</option>
                                    <option value="E">CI / E</option>
                                    <option value="J">RIF / J</option>
                                    <option value="G">RIF / G</option>
                                    <option value="P">Pasaporte</option>
                                </flux:select>
                            </div>
                            <flux:input wire:model="documento" label="N° Documento" placeholder="12345678" />
                        </div>
                        <div class="mt-4">
                            <flux:input wire:model="fecha_nacimiento" label="Fecha de Nacimiento" type="date" />
                        </div>
                    </div>

                    {{-- WhatsApp --}}
                    <div>
                        <h4 class="mb-3 text-xs font-bold text-gray-400 uppercase tracking-wide">WhatsApp</h4>
                        <flux:input wire:model="whatsapp" label="WhatsApp" placeholder="+58 412 1234567" />
                    </div>

                    {{-- Dirección --}}
                    <div>
                        <h4 class="mb-3 text-xs font-bold text-gray-400 uppercase tracking-wide">Dirección</h4>
                        <flux:input wire:model="direccion" label="Dirección" placeholder="Calle, casa, apto..." />
                        <div class="mt-4 grid grid-cols-3 gap-4">
                            <flux:input wire:model="ciudad" label="Ciudad" />
                            <flux:input wire:model="estado_region" label="Estado/Región" />
                            <flux:input wire:model="codigo_postal" label="Código Postal" />
                        </div>
                        <div class="mt-4">
                            <flux:label>País</flux:label>
                            <flux:select wire:model="pais_id">
                                <option value="">Seleccionar país...</option>
                                @foreach($paises as $pais)
                                    <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                                @endforeach
                            </flux:select>
                        </div>
                    </div>

                    {{-- Empresa --}}
                    <div>
                        <h4 class="mb-3 text-xs font-bold text-gray-400 uppercase tracking-wide">Empresa</h4>
                        <flux:input wire:model="empresa_nombre" label="Nombre de Empresa" placeholder="Razón social o nombre comercial" />
                    </div>

                    {{-- Fuente y Notas --}}
                    <div>
                        <h4 class="mb-3 text-xs font-bold text-gray-400 uppercase tracking-wide">Información Adicional</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <flux:label>Fuente</flux:label>
                                <flux:select wire:model="fuente_form">
                                    <option value="">Seleccionar...</option>
                                    <option value="web">Web</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="referido">Referido</option>
                                    <option value="tienda">Tienda</option>
                                    <option value="redes">Redes Sociales</option>
                                    <option value="otro">Otro</option>
                                </flux:select>
                            </div>
                            <flux:checkbox wire:model="activo" label="Cliente activo" />
                        </div>
                        <div class="mt-4">
                            <flux:textarea wire:model="notas" label="Notas" placeholder="Notas internas sobre el cliente..." rows="2" />
                        </div>
                    </div>

                    {{-- Crear Usuario + WhatsApp --}}
                    @if(!$editingId)
                    <div class="rounded-xl border-2 border-dashed border-amber-200 bg-amber-50/50 p-4">
                        <h4 class="mb-3 text-xs font-bold text-amber-700 uppercase tracking-wide">
                            <iconify-icon icon="heroicons:user-plus-solid" class="h-4 w-4 inline"></iconify-icon>
                            Crear Cuenta de Usuario
                        </h4>
                        <div class="space-y-3">
                            <flux:checkbox wire:model.live="crear_usuario" label="Crear usuario automáticamente (se requiere email)" />
                            @if($crear_usuario)
                                <flux:checkbox wire:model.live="enviar_whatsapp" label="Enviar credenciales por WhatsApp (se requiere número)" />
                                @if($enviar_whatsapp && !$whatsapp)
                                    <p class="text-xs text-amber-600">
                                        <iconify-icon icon="heroicons:exclamation-triangle" class="h-3 w-3 inline"></iconify-icon>
                                        Ingresa un número de WhatsApp para enviar las credenciales.
                                    </p>
                                @endif
                                @if(!$email)
                                    <p class="text-xs text-amber-600">
                                        <iconify-icon icon="heroicons:exclamation-triangle" class="h-3 w-3 inline"></iconify-icon>
                                        El email es obligatorio para crear un usuario.
                                    </p>
                                @endif
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <flux:button variant="ghost" wire:click="closeModal">Cancelar</flux:button>
                    <flux:button wire:click="save" class="!bg-amber-500 hover:!bg-amber-600">
                        <iconify-icon icon="heroicons:check-circle" class="h-4 w-4"></iconify-icon>
                        {{ $editingId ? 'Actualizar' : 'Crear Cliente' }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('showDeleteModal', false)"></div>
            <div class="relative z-10 w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl mx-4">
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                        <iconify-icon icon="heroicons:exclamation-triangle-solid" class="h-6 w-6 text-red-600"></iconify-icon>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-gray-900">Eliminar Cliente</h3>
                    <p class="mt-2 text-sm text-gray-500">¿Estás seguro? Esta acción no se puede deshacer.</p>
                </div>
                <div class="mt-6 flex items-center justify-center gap-3">
                    <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">Cancelar</flux:button>
                    <flux:button wire:click="delete" class="!bg-red-500 hover:!bg-red-600 !text-white">Eliminar</flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
