<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Países</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona los países y su configuración regional.</p>
        </div>
        @can('paises.create')
            <flux:button variant="primary" wire:click="openCreateModal" class="!bg-cyan-500 hover:!bg-cyan-600">
                <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
                Nuevo País
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
            <iconify-icon icon="heroicons:exclamation-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-3">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Total Países</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-50">
                    <iconify-icon icon="heroicons:globe-alt" class="h-5 w-5 text-cyan-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['total_paises'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Países registrados</p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Activos</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50">
                    <iconify-icon icon="heroicons:check-badge" class="h-5 w-5 text-emerald-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['paises_activos'] }}</p>
            <p class="mt-1 text-xs text-gray-400">
                <span class="text-emerald-600 font-medium">
                    {{ $stats['total_paises'] > 0 ? round(($stats['paises_activos'] / $stats['total_paises']) * 100) : 0 }}%
                </span>
                del total
            </p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Inactivos</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-red-50">
                    <iconify-icon icon="heroicons:x-circle" class="h-5 w-5 text-red-500"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['paises_inactivos'] }}</p>
            <p class="mt-1 text-xs text-gray-400">
                @if($stats['paises_inactivos'] > 0)
                    <span class="text-red-500 font-medium">Requieren atención</span>
                @else
                    <span class="text-emerald-600 font-medium">Todos activos</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Search + Filters --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar países..."
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

    {{-- Countries Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">País</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Código</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Moneda</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($paises as $pais)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-100 text-xs font-bold text-cyan-700">
                                    {{ strtoupper(substr($pais->nombre, 0, 2)) }}
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900">{{ $pais->nombre }}</span>
                                    @if($pais->continente)
                                        <p class="text-xs text-gray-400">{{ $pais->continente }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-mono font-medium text-gray-700">
                                    {{ $pais->codigo_iso2 }}
                                </span>
                                <span class="text-xs text-gray-400">{{ $pais->codigo_iso3 }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($pais->moneda_principal)
                                <span class="text-sm text-gray-600">{{ $pais->moneda_principal }}</span>
                                @if($pais->codigo_telefonico)
                                    <p class="text-xs text-gray-400">{{ $pais->codigo_telefonico }}</p>
                                @endif
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <button
                                wire:click="toggleStatus({{ $pais->id }})"
                                @class([
                                    'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold transition cursor-pointer',
                                    $pais->activo
                                        ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                        : 'bg-gray-100 text-gray-500 hover:bg-gray-200',
                                ])
                            >
                                <span @class([
                                    'h-1.5 w-1.5 rounded-full',
                                    $pais->activo ? 'bg-emerald-500' : 'bg-gray-400',
                                ])></span>
                                {{ $pais->activo ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('paises.edit')
                                    <flux:button variant="ghost" size="sm" wire:click="openEditModal({{ $pais->id }})" class="!text-gray-400 hover:!text-cyan-600">
                                        <iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon>
                                    </flux:button>
                                @endcan
                                @can('paises.delete')
                                    <flux:button variant="ghost" size="sm" wire:click="delete({{ $pais->id }})" wire:confirm="¿Estás seguro de eliminar el país '{{ $pais->nombre }}'?" class="!text-gray-400 hover:!text-red-600">
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
                                    <iconify-icon icon="heroicons:globe-alt" class="h-6 w-6 text-gray-400"></iconify-icon>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-900">No hay países</p>
                                <p class="text-xs text-gray-500">Crea tu primer país para comenzar</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create/Edit Modal --}}
    <flux:modal name="pais-form" class="w-full max-w-2xl">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900">
                {{ $editingId ? 'Editar País' : 'Nuevo País' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                {{ $editingId ? 'Modifica los datos del país.' : 'Completa los campos para crear un nuevo país.' }}
            </p>

            <div class="mt-6 space-y-4">
                {{-- Basic Info --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <flux:input
                        wire:model="nombre"
                        label="Nombre"
                        placeholder="Ej: Venezuela"
                        :error="$errors->first('nombre')"
                    />
                    <flux:input
                        wire:model="codigo_iso2"
                        label="Código ISO 2"
                        placeholder="Ej: VE"
                        :error="$errors->first('codigo_iso2')"
                    />
                    <flux:input
                        wire:model="codigo_iso3"
                        label="Código ISO 3"
                        placeholder="Ej: VEN"
                        :error="$errors->first('codigo_iso3')"
                    />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <flux:input
                        wire:model="codigo_telefonico"
                        label="Código Telefónico"
                        placeholder="+58"
                        :error="$errors->first('codigo_telefonico')"
                    />
                    <flux:input
                        wire:model="moneda_principal"
                        label="Moneda"
                        placeholder="VES, USD"
                        :error="$errors->first('moneda_principal')"
                    />
                    <flux:input
                        wire:model="idioma_principal"
                        label="Idioma"
                        placeholder="es, en"
                        :error="$errors->first('idioma_principal')"
                    />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:input
                        wire:model="continente"
                        label="Continente"
                        placeholder="América del Sur"
                        :error="$errors->first('continente')"
                    />
                    <flux:input
                        wire:model="zona_horaria"
                        label="Zona Horaria"
                        placeholder="America/Caracas"
                        :error="$errors->first('zona_horaria')"
                    />
                </div>

                {{-- Configuración Regional --}}
                <div class="border-t pt-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Configuración Regional</h4>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <flux:input
                            wire:model="formato_fecha"
                            label="Formato Fecha"
                            placeholder="dd/mm/yyyy"
                            :error="$errors->first('formato_fecha')"
                        />
                        <flux:input
                            wire:model="formato_moneda"
                            label="Formato Moneda"
                            placeholder="1.234,56"
                            :error="$errors->first('formato_moneda')"
                        />
                        <flux:input
                            wire:model="impuesto_predeterminado"
                            label="Impuesto (%)"
                            type="number"
                            step="0.01"
                            :error="$errors->first('impuesto_predeterminado')"
                        />
                    </div>
                </div>

                {{-- Separadores --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <flux:label>Separador Miles</flux:label>
                        <select
                            wire:model="separador_miles"
                            class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 px-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-cyan-500"
                        >
                            <option value=".">Punto (.)</option>
                            <option value=",">Coma (,)</option>
                        </select>
                    </div>
                    <div>
                        <flux:label>Separador Decimales</flux:label>
                        <select
                            wire:model="separador_decimales"
                            class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 px-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-cyan-500"
                        >
                            <option value=",">Coma (,)</option>
                            <option value=".">Punto (.)</option>
                        </select>
                    </div>
                    <div>
                        <flux:label>Decimales Moneda</flux:label>
                        <select
                            wire:model="decimales_moneda"
                            class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 px-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-cyan-500"
                        >
                            <option value="0">0</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                </div>

                <flux:checkbox wire:model="is_active" label="País activo" />
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button wire:click="save" class="!bg-cyan-500 hover:!bg-cyan-600">
                    {{ $editingId ? 'Guardar cambios' : 'Crear país' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>