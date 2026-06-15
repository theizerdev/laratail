<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Atributos</h2>
            <p class="mt-1 text-sm text-gray-500">Define atributos como Color, Talla, Material para tus productos y variantes.</p>
        </div>
        @can('atributos.create')
            <flux:button variant="primary" wire:click="openCreateAttr" class="!bg-indigo-500 hover:!bg-indigo-600">
                <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
                Nuevo Atributo
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

    {{-- Search --}}
    <div class="mb-5">
        <div class="w-full sm:max-w-xs">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar atributos..." icon="magnifying-glass" />
        </div>
    </div>

    {{-- Cards Grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($attributes as $attr)
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl @if($attr->tipo === 'color') bg-pink-50 @elseif($attr->tipo === 'text') bg-amber-50 @else bg-indigo-50 @endif">
                            @if($attr->tipo === 'color')
                                <iconify-icon icon="heroicons:swatch-solid" class="h-5 w-5 text-pink-600"></iconify-icon>
                            @elseif($attr->tipo === 'text')
                                <iconify-icon icon="heroicons:document-text-solid" class="h-5 w-5 text-amber-600"></iconify-icon>
                            @else
                                <iconify-icon icon="heroicons:list-bullet-solid" class="h-5 w-5 text-indigo-600"></iconify-icon>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $attr->nombre }}</h3>
                            <p class="text-xs text-gray-400">
                                @if($attr->tipo === 'color') Color
                                @elseif($attr->tipo === 'text') Texto
                                @else Selección
                                @endif
                                @if($attr->usado_para_variantes)
                                    · <span class="text-indigo-500">Variantes</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        @can('atributos.edit')
                            <flux:button variant="ghost" size="sm" wire:click="openEditAttr({{ $attr->id }})" class="!text-gray-400 hover:!text-indigo-600">
                                <iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon>
                            </flux:button>
                        @endcan
                        @can('atributos.delete')
                            <flux:button variant="ghost" size="sm" wire:click="confirmDeleteAttr({{ $attr->id }})" class="!text-gray-400 hover:!text-red-600">
                                <iconify-icon icon="heroicons:trash" class="h-4 w-4"></iconify-icon>
                            </flux:button>
                        @endcan
                    </div>
                </div>

                {{-- Values --}}
                @if($attr->values->count() > 0)
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach($attr->values->take(8) as $val)
                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                @if($attr->tipo === 'color' && $val->codigo_color)
                                    <span class="h-3 w-3 rounded-full border border-gray-300" style="background-color: {{ $val->codigo_color }}"></span>
                                @endif
                                {{ $val->valor }}
                            </span>
                        @endforeach
                        @if($attr->values->count() > 8)
                            <span class="rounded-full bg-gray-50 px-2 py-0.5 text-xs text-gray-400">+{{ $attr->values->count() - 8 }}</span>
                        @endif
                    </div>
                @endif

                {{-- Footer --}}
                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3">
                    <span class="text-xs text-gray-400">{{ $attr->values_count }} valor(es)</span>
                    <div class="flex items-center gap-2">
                        <span @class(['inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold', $attr->status ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'])>
                            <span @class(['h-1.5 w-1.5 rounded-full', $attr->status ? 'bg-emerald-500' : 'bg-gray-400'])></span>
                            {{ $attr->status ? 'Activo' : 'Inactivo' }}
                        </span>
                        @can('atributos.edit')
                            <flux:button variant="ghost" size="sm" wire:click="openValues({{ $attr->id }})" class="!text-indigo-600 hover:!text-indigo-800">
                                <iconify-icon icon="heroicons:adjustments-horizontal" class="h-4 w-4"></iconify-icon>
                                Valores
                            </flux:button>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl bg-white p-12 text-center shadow-sm">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                    <iconify-icon icon="heroicons:swatch" class="h-6 w-6 text-gray-400"></iconify-icon>
                </div>
                <p class="mt-2 text-sm font-medium text-gray-900">No hay atributos</p>
                <p class="text-xs text-gray-500">Crea atributos como Color, Talla o Material</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $attributes->links() }}</div>

    {{-- Attribute Modal --}}
    @if($showAttrModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" wire:click="closeAttrModal"></div>
            <div class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl mx-4">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">{{ $editingAttrId ? 'Editar Atributo' : 'Nuevo Atributo' }}</h3>
                    <button wire:click="closeAttrModal" class="rounded-lg p-1 hover:bg-gray-100">
                        <iconify-icon icon="heroicons:x-mark" class="h-5 w-5 text-gray-400"></iconify-icon>
                    </button>
                </div>
                <div class="space-y-4">
                    <flux:input wire:model="attrNombre" label="Nombre" placeholder="Ej: Color, Talla, Material" :error="$errors->first('attrNombre')" required />
                    <div>
                        <flux:label>Tipo</flux:label>
                        <select wire:model="attrTipo" class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                            <option value="select">Selección (dropdown)</option>
                            <option value="color">Color (selector visual)</option>
                            <option value="text">Texto libre</option>
                        </select>
                    </div>
                    <flux:checkbox wire:model="attrUsadoVariantes" label="Usar para generar variantes" />
                    <flux:checkbox wire:model="attrStatus" label="Activo" />
                </div>
                <div class="mt-6 flex items-center justify-end gap-3">
                    <flux:button variant="ghost" wire:click="closeAttrModal">Cancelar</flux:button>
                    <flux:button wire:click="saveAttr" class="!bg-indigo-500 hover:!bg-indigo-600">
                        <iconify-icon icon="heroicons:check-circle" class="h-4 w-4"></iconify-icon>
                        {{ $editingAttrId ? 'Actualizar' : 'Crear' }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Values Modal --}}
    @if($showValuesModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" wire:click="closeValuesModal"></div>
            <div class="relative z-10 w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl mx-4">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">Valores de: {{ $selectedAttributeName }}</h3>
                    <button wire:click="closeValuesModal" class="rounded-lg p-1 hover:bg-gray-100">
                        <iconify-icon icon="heroicons:x-mark" class="h-5 w-5 text-gray-400"></iconify-icon>
                    </button>
                </div>

                {{-- Add new value --}}
                <div class="mb-4 flex items-end gap-2">
                    <div class="flex-1">
                        <flux:input wire:model="newValue" placeholder="Nuevo valor..." :error="$errors->first('newValue')" />
                    </div>
                    <div class="w-24">
                        <flux:input wire:model="newColorCode" placeholder="#FF0000" label="Color" />
                    </div>
                    <flux:button wire:click="addValue" class="!bg-indigo-500 hover:!bg-indigo-600">
                        <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
                    </flux:button>
                </div>

                {{-- Values list --}}
                <div class="space-y-2">
                    @forelse($selectedValues as $val)
                        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-2.5">
                            <div class="flex items-center gap-2">
                                @if($val->codigo_color)
                                    <span class="h-5 w-5 rounded-full border border-gray-300" style="background-color: {{ $val->codigo_color }}"></span>
                                @endif
                                <span class="text-sm font-medium text-gray-900">{{ $val->valor }}</span>
                            </div>
                            <button wire:click="deleteValue({{ $val->id }})" class="text-gray-400 hover:text-red-600">
                                <iconify-icon icon="heroicons:x-mark" class="h-4 w-4"></iconify-icon>
                            </button>
                        </div>
                    @empty
                        <p class="py-8 text-center text-sm text-gray-400">No hay valores definidos</p>
                    @endforelse
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
                    <h3 class="mt-4 text-lg font-bold text-gray-900">Eliminar Atributo</h3>
                    <p class="mt-2 text-sm text-gray-500">¿Estás seguro? También se eliminarán todos los valores.</p>
                </div>
                <div class="mt-6 flex items-center justify-center gap-3">
                    <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">Cancelar</flux:button>
                    <flux:button wire:click="deleteAttr" class="!bg-red-500 hover:!bg-red-600 !text-white">Eliminar</flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
