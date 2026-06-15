<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Marcas</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona las marcas de tu catálogo de productos.</p>
        </div>
        @can('marcas.create')
            <flux:button variant="primary" wire:click="openCreate" class="!bg-indigo-500 hover:!bg-indigo-600">
                <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
                Nueva Marca
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

    {{-- Stats Cards --}}
    <div class="mb-6 grid grid-cols-3 gap-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Total</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50">
                    <iconify-icon icon="heroicons:bookmark-solid" class="h-5 w-5 text-indigo-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Activas</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50">
                    <iconify-icon icon="heroicons:check-badge-solid" class="h-5 w-5 text-emerald-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['active'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Inactivas</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-red-50">
                    <iconify-icon icon="heroicons:x-circle-solid" class="h-5 w-5 text-red-500"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['inactive'] }}</p>
        </div>
    </div>

    {{-- Search + Filters --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar marcas..." icon="magnifying-glass" />
        </div>
        <div class="flex items-center gap-1 rounded-xl bg-white p-1 shadow-sm">
            <button wire:click="$set('filter', 'all')" @class(['rounded-lg px-3.5 py-1.5 text-xs font-semibold transition', $filter === 'all' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-900'])>Todas</button>
            <button wire:click="$set('filter', 'active')" @class(['rounded-lg px-3.5 py-1.5 text-xs font-semibold transition', $filter === 'active' ? 'bg-emerald-500 text-white' : 'text-gray-500 hover:text-gray-900'])>Activas</button>
            <button wire:click="$set('filter', 'inactive')" @class(['rounded-lg px-3.5 py-1.5 text-xs font-semibold transition', $filter === 'inactive' ? 'bg-gray-300 text-gray-700' : 'text-gray-500 hover:text-gray-900'])>Inactivas</button>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Marca</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Productos</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Website</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($brands as $brand)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($brand->logo)
                                    <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-white">
                                        <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->nombre }}" class="h-full w-full object-contain p-0.5" />
                                    </div>
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-xs font-bold text-purple-700">
                                        {{ strtoupper(substr($brand->nombre, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <span class="font-medium text-gray-900">{{ $brand->nombre }}</span>
                                    <p class="text-xs text-gray-400">{{ $brand->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700">{{ $brand->products_count }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($brand->website)
                                <a href="{{ $brand->website }}" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-800">{{ parse_url($brand->website, PHP_URL_HOST) }}</a>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @can('marcas.edit')
                                <button wire:click="toggleStatus({{ $brand->id }})" @class(['inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold transition cursor-pointer', $brand->status ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'])>
                                    <span @class(['h-1.5 w-1.5 rounded-full', $brand->status ? 'bg-emerald-500' : 'bg-gray-400'])></span>
                                    {{ $brand->status ? 'Activa' : 'Inactiva' }}
                                </button>
                            @else
                                <span @class(['inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold', $brand->status ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'])>
                                    <span @class(['h-1.5 w-1.5 rounded-full', $brand->status ? 'bg-emerald-500' : 'bg-gray-400'])></span>
                                    {{ $brand->status ? 'Activa' : 'Inactiva' }}
                                </span>
                            @endcan
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('marcas.edit')
                                    <flux:button variant="ghost" size="sm" wire:click="openEdit({{ $brand->id }})" class="!text-gray-400 hover:!text-indigo-600">
                                        <iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon>
                                    </flux:button>
                                @endcan
                                @can('marcas.delete')
                                    <flux:button variant="ghost" size="sm" wire:click="confirmDelete({{ $brand->id }})" class="!text-gray-400 hover:!text-red-600">
                                        <iconify-icon icon="heroicons:trash" class="h-4 w-4"></iconify-icon>
                                    </flux:button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                                    <iconify-icon icon="heroicons:bookmark" class="h-6 w-6 text-gray-400"></iconify-icon>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-900">No hay marcas</p>
                                <p class="text-xs text-gray-500">Crea tu primera marca para comenzar</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $brands->links() }}</div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>
            <div class="relative z-10 w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl mx-4">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $editingId ? 'Editar Marca' : 'Nueva Marca' }}
                    </h3>
                    <button wire:click="closeModal" class="rounded-lg p-1 hover:bg-gray-100">
                        <iconify-icon icon="heroicons:x-mark" class="h-5 w-5 text-gray-400"></iconify-icon>
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- Logo Upload --}}
                    <div>
                        <flux:label>Logo</flux:label>
                        <div class="flex items-center gap-4 mt-2">
                            <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-gray-200 bg-gray-50">
                                @if ($logo)
                                    <img src="{{ $logo->temporaryUrl() }}" alt="Preview" class="h-full w-full object-contain p-1" />
                                @elseif ($existingLogo)
                                    <img src="{{ asset('storage/' . $existingLogo) }}" alt="Actual" class="h-full w-full object-contain p-1" />
                                @else
                                    <iconify-icon icon="heroicons:photo" class="h-6 w-6 text-gray-300"></iconify-icon>
                                @endif
                            </div>
                            <div>
                                <input type="file" wire:model="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="block w-full text-xs text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-indigo-600 hover:file:bg-indigo-100" />
                                @if ($logo || $existingLogo)
                                    <button wire:click="removeLogo" class="mt-1 text-xs text-red-500 hover:text-red-700">
                                        <iconify-icon icon="heroicons:x-mark" class="h-3 w-3 inline"></iconify-icon> Quitar
                                    </button>
                                @endif
                                <div wire:loading wire:target="logo" class="mt-1 text-xs text-indigo-500">
                                    <iconify-icon icon="heroicons:arrow-path" class="h-3 w-3 inline animate-spin"></iconify-icon> Subiendo...
                                </div>
                                @error('logo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <flux:input wire:model="nombre" label="Nombre" placeholder="Ej: Samsung" :error="$errors->first('nombre')" required />
                    <flux:textarea wire:model="descripcion" label="Descripción" placeholder="Descripción de la marca..." rows="3" :error="$errors->first('descripcion')" />
                    <flux:input wire:model="website" label="Sitio Web" placeholder="https://ejemplo.com" :error="$errors->first('website')" />
                    <flux:checkbox wire:model="status" label="Marca activa" />
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <flux:button variant="ghost" wire:click="closeModal">Cancelar</flux:button>
                    <flux:button wire:click="save" class="!bg-indigo-500 hover:!bg-indigo-600">
                        <iconify-icon icon="heroicons:check-circle" class="h-4 w-4"></iconify-icon>
                        {{ $editingId ? 'Actualizar' : 'Crear' }}
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
                    <h3 class="mt-4 text-lg font-bold text-gray-900">Eliminar Marca</h3>
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
