<div>
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Movimientos de Inventario</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Registra y consulta todos los movimientos de stock.</p>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/30">
            <p class="text-sm text-emerald-600 dark:text-emerald-400">Entradas</p>
            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($stats['entradas']) }}</p>
        </div>
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/30">
            <p class="text-sm text-red-600 dark:text-red-400">Salidas</p>
            <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ number_format($stats['salidas']) }}</p>
        </div>
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/30">
            <p class="text-sm text-amber-600 dark:text-amber-400">Ajustes</p>
            <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ number_format($stats['ajustes']) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 items-center gap-3">
            <div class="relative flex-1 max-w-md">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar producto, referencia..."
                    class="w-full rounded-lg border-gray-300 pl-10 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                 <iconify-icon icon="heroicons:magnifying-glass" class="absolute left-3 top-2.5 h-5 w-5 text-gray-400"></iconify-icon>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <select wire:model.live="filterTipo" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="all">Todos los tipos</option>
                <option value="entrada">Entrada</option>
                <option value="salida">Salida</option>
                <option value="ajuste">Ajuste</option>
                <option value="transferencia">Transferencia</option>
            </select>
            <input type="date" wire:model.live="dateFrom" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" title="Desde">
            <input type="date" wire:model.live="dateTo" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" title="Hasta">
            <button wire:click="openCreate" class="inline-flex items-center gap-1 rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700">
                <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon> Nuevo Movimiento
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Producto</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Cantidad</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Stock Ant.</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Stock Nuevo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Referencia</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Usuario</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Fecha</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($movements as $mov)
                    @php
                        $color = match($mov->tipo) {
                            'entrada' => 'emerald', 'salida' => 'red', 'ajuste' => 'amber', 'transferencia' => 'blue', default => 'gray'
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="inline-flex items-center gap-1 rounded-full bg-{{ $color }}-100 px-2.5 py-0.5 text-xs font-medium text-{{ $color }}-800 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-400">
                                {{ ucfirst($mov->tipo) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $mov->product?->nombre ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $mov->product?->sku ?? '' }}</div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-center text-sm font-semibold text-{{ $color }}-600 dark:text-{{ $color }}-400">
                            {{ number_format($mov->cantidad) }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-300">{{ number_format($mov->stock_anterior) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($mov->stock_nuevo) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $mov->referencia ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $mov->user?->name ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-center">
                            <button wire:click="showDetail({{ $mov->id }})" class="text-cyan-600 hover:text-cyan-800 dark:text-cyan-400">
                                 <iconify-icon icon="heroicons:eye" class="h-3.5 w-3.5"></iconify-icon>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No se encontraron movimientos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $movements->links() }}</div>

    {{-- Create Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click.self="closeModal">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Nuevo Movimiento de Inventario</h2>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600"><iconify-icon icon="heroicons:x-mark" class="h-6 w-6"></iconify-icon></button>
                </div>

                <div class="space-y-4">
                    {{-- Tipo selector --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Movimiento <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach (['entrada' => ['Entrada', 'emerald'], 'salida' => ['Salida', 'red'], 'ajuste' => ['Ajuste', 'amber'], 'transferencia' => ['Transfer.', 'blue']] as $key => [$label, $clr])
                                <button type="button" wire:click="$set('tipo', '{{ $key }}')"
                                    class="rounded-lg border-2 px-3 py-2 text-sm font-medium transition {{ $tipo === $key ? "border-{$clr}-500 bg-{$clr}-50 text-{$clr}-700 dark:bg-{$clr}-900/30 dark:text-{$clr}-400" : 'border-gray-200 text-gray-600 hover:border-gray-300 dark:border-gray-600 dark:text-gray-400' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Product search --}}
                    <div class="relative">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Producto <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.live.debounce.300ms="productSearch" wire:focus="$set('showProductDropdown', true)"
                            placeholder="Buscar por nombre o SKU..."
                            class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @if ($showProductDropdown && count($searchResults) > 0)
                            <div class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700">
                                @foreach ($searchResults as $p)
                                    <button type="button" wire:click="selectProduct({{ $p->id }})" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $p->nombre }}</span>
                                        <span class="text-gray-400">({{ $p->sku }})</span>
                                        <span class="ml-auto text-xs text-gray-500">Stock: {{ $p->stock }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Cantidad + Costo --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $tipo === 'ajuste' ? 'Stock Corregido' : 'Cantidad' }} <span class="text-red-500">*</span>
                            </label>
                            <input type="number" wire:model.live="cantidad" min="1"
                                class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Costo Unitario</label>
                            <input type="number" wire:model="costo_unitario" min="0" step="0.01"
                                class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>

                    {{-- Transfer fields --}}
                    @if ($tipo === 'transferencia')
                        <div class="grid grid-cols-2 gap-4 rounded-lg border border-blue-200 bg-blue-50 p-3 dark:border-blue-800 dark:bg-blue-900/20">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Sucursal Origen <span class="text-red-500">*</span></label>
                                <select wire:model="sucursal_origen_id" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">Seleccionar...</option>
                                    @foreach ($sucursales as $s)
                                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Sucursal Destino <span class="text-red-500">*</span></label>
                                <select wire:model="sucursal_destino_id" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">Seleccionar...</option>
                                    @foreach ($sucursales as $s)
                                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    {{-- Stock preview --}}
                    @if ($product_id)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-600 dark:bg-gray-700/50">
                            <p class="mb-1 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Vista previa de stock</p>
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-600 dark:text-gray-300">Stock Anterior: <strong>{{ number_format($stockAnterior) }}</strong></span>
                                <iconify-icon icon="heroicons:arrow-right" class="h-4 w-4 text-gray-400" />
                                <span class="text-sm text-gray-600 dark:text-gray-300">Stock Nuevo: <strong class="{{ $stockNuevo > $stockAnterior ? 'text-emerald-600' : ($stockNuevo < $stockAnterior ? 'text-red-600' : '') }}">{{ number_format($stockNuevo) }}</strong></span>
                            </div>
                        </div>
                    @endif

                    {{-- Referencia + Motivo --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Referencia</label>
                        <input type="text" wire:model="referencia" placeholder="Ej: OC-20260625-001"
                            class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Motivo {{ $tipo === 'ajuste' ? '*' : '' }}
                        </label>
                        <textarea wire:model="motivo" rows="2" placeholder="Motivo del movimiento..."
                            class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="closeModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">Cancelar</button>
                    <button wire:click="save" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700">Registrar Movimiento</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Detail Modal --}}
    @if ($showDetailModal && $detailMovement)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click.self="$set('showDetailModal', false)">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Detalle del Movimiento</h2>
                    <button wire:click="$set('showDetailModal', false)" class="text-gray-400 hover:text-gray-600"><iconify-icon icon="heroicons:x-mark" class="h-6 w-6"></iconify-icon></button>
                </div>
                @php $dc = $detailMovement->tipo_color; @endphp
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Tipo</span>
                        <span class="rounded-full bg-{{ $dc }}-100 px-2.5 py-0.5 text-xs font-medium text-{{ $dc }}-800 dark:bg-{{ $dc }}-900/30 dark:text-{{ $dc }}-400">{{ ucfirst($detailMovement->tipo) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Producto</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $detailMovement->product?->nombre }} ({{ $detailMovement->product?->sku }})</span>
                    </div>
                    @if ($detailMovement->variant)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Variante</span>
                            <span class="text-sm text-gray-900 dark:text-white">{{ $detailMovement->variant->combinacion }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Cantidad</span>
                        <span class="text-sm font-bold text-{{ $dc }}-600">{{ number_format($detailMovement->cantidad) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Stock Anterior → Nuevo</span>
                        <span class="text-sm text-gray-900 dark:text-white">{{ number_format($detailMovement->stock_anterior) }} → {{ number_format($detailMovement->stock_nuevo) }}</span>
                    </div>
                    @if ($detailMovement->costo_unitario)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Costo Unitario</span>
                            <span class="text-sm text-gray-900 dark:text-white">${{ number_format($detailMovement->costo_unitario, 2) }}</span>
                        </div>
                    @endif
                    @if ($detailMovement->sucursalOrigen)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Origen</span>
                            <span class="text-sm text-gray-900 dark:text-white">{{ $detailMovement->sucursalOrigen->nombre }}</span>
                        </div>
                    @endif
                    @if ($detailMovement->sucursalDestino)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Destino</span>
                            <span class="text-sm text-gray-900 dark:text-white">{{ $detailMovement->sucursalDestino->nombre }}</span>
                        </div>
                    @endif
                    @if ($detailMovement->referencia)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Referencia</span>
                            <span class="text-sm text-gray-900 dark:text-white">{{ $detailMovement->referencia }}</span>
                        </div>
                    @endif
                    @if ($detailMovement->motivo)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Motivo</span>
                            <span class="text-sm text-gray-900 dark:text-white">{{ $detailMovement->motivo }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Usuario</span>
                        <span class="text-sm text-gray-900 dark:text-white">{{ $detailMovement->user?->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Fecha</span>
                        <span class="text-sm text-gray-900 dark:text-white">{{ $detailMovement->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
                <div class="mt-6 text-right">
                    <button wire:click="$set('showDetailModal', false)" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">Cerrar</button>
                </div>
            </div>
        </div>
    @endif
</div>
