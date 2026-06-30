<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Tasas de Cambio (VES / USD)</h2>
            <p class="mt-1 text-sm text-gray-500">Administra las tasas de cambio de divisas y sincronización del BCV.</p>
        </div>
        <div class="flex items-center gap-2">
            <flux:button variant="subtle" wire:click="fetchBCVRates" class="hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                <iconify-icon icon="heroicons:arrow-path" class="h-4 w-4 mr-1.5"></iconify-icon>
                Sincronizar BCV
            </flux:button>
            <flux:button variant="primary" wire:click="openCreateModal" class="!bg-cyan-500 hover:!bg-cyan-600">
                <iconify-icon icon="heroicons:plus" class="h-4 w-4 mr-1.5"></iconify-icon>
                Nueva Tasa
            </flux:button>
        </div>
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
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Tasa Actual USD</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-50">
                    <iconify-icon icon="heroicons:currency-dollar" class="h-5 w-5 text-cyan-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $latestRate ? number_format($latestRate->usd_rate, 2, ',', '.') . ' Bs.' : 'No registrada' }}
            </p>
            <p class="mt-1 text-xs text-gray-400">Última tasa activa para conversiones</p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Tasa Actual EUR</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50">
                    <iconify-icon icon="heroicons:currency-euro" class="h-5 w-5 text-indigo-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $latestRate && $latestRate->eur_rate ? number_format($latestRate->eur_rate, 2, ',', '.') . ' Bs.' : 'No registrada' }}
            </p>
            <p class="mt-1 text-xs text-gray-400">Tasa en Euros para transacciones secundarias</p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Última Sincronización</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50">
                    <iconify-icon icon="heroicons:clock" class="h-5 w-5 text-emerald-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-2xl font-bold text-gray-900">
                {{ $latestRate ? $latestRate->date->format('d/m/Y') : 'N/A' }}
            </p>
            <p class="mt-1 text-xs text-gray-400">
                Fuente: <span class="font-semibold capitalize text-emerald-600">{{ $latestRate->source ?? 'N/A' }}</span>
                @if($latestRate && $latestRate->fetch_time)
                    a las {{ $latestRate->fetch_time }}
                @endif
            </p>
        </div>
    </div>

    {{-- Search --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar por fuente o fecha..."
                icon="magnifying-glass"
            />
        </div>
    </div>

    {{-- Rates Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tasa USD</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tasa EUR</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fuente</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Hora Consulta</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 font-sans">
                @forelse ($rates as $rate)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $rate->date->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 font-semibold text-cyan-600">
                            {{ number_format($rate->usd_rate, 4, ',', '.') }} Bs.
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $rate->eur_rate ? number_format($rate->eur_rate, 4, ',', '.') . ' Bs.' : '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span @class([
                                'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                $rate->source === 'manual' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'
                            ])>
                                {{ strtoupper($rate->source) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400">
                            {{ $rate->fetch_time }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <flux:button variant="ghost" size="sm" wire:click="openEditModal({{ $rate->id }})" class="!text-gray-400 hover:!text-cyan-600">
                                    <iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon>
                                </flux:button>
                                <flux:button variant="ghost" size="sm" wire:click="delete({{ $rate->id }})" wire:confirm="¿Estás seguro de eliminar esta tasa de fecha '{{ $rate->date->format('d/m/Y') }}'?" class="!text-gray-400 hover:!text-red-600">
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
                                    <iconify-icon icon="heroicons:currency-dollar" class="h-6 w-6 text-gray-400"></iconify-icon>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-900">No hay tasas de cambio registradas</p>
                                <p class="text-xs text-gray-500">Sincroniza con el BCV o crea una manualmente para empezar</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $rates->links() }}
    </div>

    {{-- Create/Edit Modal --}}
    <flux:modal name="tasa-form" class="w-full max-w-lg">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900">
                {{ $editingId ? 'Editar Tasa de Cambio' : 'Nueva Tasa de Cambio' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                {{ $editingId ? 'Modifica los valores de la tasa seleccionada.' : 'Completa los campos para añadir una nueva tasa manualmente.' }}
            </p>

            <div class="mt-6 space-y-4">
                <flux:input
                    wire:model="date"
                    type="date"
                    label="Fecha de la Tasa"
                    :error="$errors->first('date')"
                />
                
                <div class="grid grid-cols-2 gap-4">
                    <flux:input
                        wire:model="usd_rate"
                        type="number"
                        step="0.0001"
                        label="Tasa USD (VES / USD)"
                        placeholder="Ej: 36.5432"
                        :error="$errors->first('usd_rate')"
                    />
                    <flux:input
                        wire:model="eur_rate"
                        type="number"
                        step="0.0001"
                        label="Tasa EUR (VES / EUR)"
                        placeholder="Ej: 39.1234"
                        :error="$errors->first('eur_rate')"
                    />
                </div>

                <flux:input
                    wire:model="source"
                    label="Fuente"
                    placeholder="manual"
                    :error="$errors->first('source')"
                />
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button wire:click="save" class="!bg-cyan-500 hover:!bg-cyan-600">
                    {{ $editingId ? 'Guardar cambios' : 'Guardar Tasa' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
