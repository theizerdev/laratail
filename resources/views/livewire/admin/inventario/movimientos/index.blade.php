<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Movimientos de Inventario</h2>
            <p class="mt-1 text-sm text-gray-500">Registra y consulta todos los movimientos de stock.</p>
        </div>
        <flux:button wire:click="openCreate" variant="primary">
            <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
            Nuevo Movimiento
        </flux:button>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            <iconify-icon icon="heroicons:x-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Total</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                    <iconify-icon icon="heroicons:arrow-path-solid" class="h-4 w-4 text-indigo-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Entradas</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50">
                    <iconify-icon icon="heroicons:arrow-down-circle-solid" class="h-4 w-4 text-emerald-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($stats['entradas']) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Salidas</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50">
                    <iconify-icon icon="heroicons:arrow-up-circle-solid" class="h-4 w-4 text-red-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-red-600">{{ number_format($stats['salidas']) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-400">Ajustes</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50">
                    <iconify-icon icon="heroicons:wrench-screwdriver-solid" class="h-4 w-4 text-amber-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($stats['ajustes']) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar producto, referencia..." icon="magnifying-glass" />
        </div>
        <div class="flex items-center gap-2">
            <flux:select wire:model.live="filterTipo" class="w-auto">
                <option value="all">Todos los tipos</option>
                <option value="entrada">Entrada</option>
                <option value="salida">Salida</option>
                <option value="ajuste">Ajuste</option>
                <option value="transferencia">Transferencia</option>
            </flux:select>
            <flux:input type="date" wire:model.live="dateFrom" title="Desde" />
            <flux:input type="date" wire:model.live="dateTo" title="Hasta" />
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Producto</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Cantidad</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Stock Ant.</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Stock Nuevo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Referencia</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Usuario</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($movements as $mov)
                    @php
                        $color = match($mov->tipo) {
                            'entrada' => 'emerald', 'salida' => 'red', 'ajuste' => 'amber', 'transferencia' => 'blue', default => 'gray'
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="inline-flex items-center gap-1 rounded-full bg-{{ $color }}-50 px-2.5 py-0.5 text-[10px] font-semibold text-{{ $color }}-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-{{ $color }}-500"></span>
                                {{ ucfirst($mov->tipo) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900">{{ $mov->product?->nombre ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-400">{{ $mov->product?->sku ?? '' }}</div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-center text-sm font-semibold text-{{ $color }}-600">
                            {{ number_format($mov->cantidad) }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-gray-600">{{ number_format($mov->stock_anterior) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-center text-sm font-semibold text-gray-900">{{ number_format($mov->stock_nuevo) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $mov->referencia ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $mov->user?->name ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-center">
                            <flux:button wire:click="showDetail({{ $mov->id }})" variant="ghost" size="sm" class="!text-gray-400 hover:!text-cyan-600">
                                <iconify-icon icon="heroicons:eye" class="h-4 w-4"></iconify-icon>
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                                    <iconify-icon icon="heroicons:arrow-path" class="h-6 w-6 text-gray-400"></iconify-icon>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-900">No hay movimientos</p>
                                <p class="text-xs text-gray-500">Registra tu primer movimiento de inventario</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $movements->links() }}</div>

    {{-- Create Modal --}}
    <flux:modal name="modal-create-movimiento" class="min-w-[40rem]">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Nuevo Movimiento de Inventario</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Completa la información del movimiento</p>
                </div>
                <flux:button variant="ghost" size="sm" wire:click="closeModal" x-on:click="Flux.modal('modal-create-movimiento').close()">
                    <iconify-icon icon="heroicons:x-mark" class="h-5 w-5"></iconify-icon>
                </flux:button>
            </div>

            <div class="space-y-4">
                {{-- Tipo selector --}}
                <div>
                    <label class="mb-2 block text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo de Movimiento *</label>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach (['entrada' => ['Entrada', 'emerald'], 'salida' => ['Salida', 'red'], 'ajuste' => ['Ajuste', 'amber'], 'transferencia' => ['Transfer.', 'blue']] as $key => [$label, $clr])
                            <button type="button" wire:click="$set('tipo', '{{ $key }}')"
                                class="rounded-xl border-2 px-3 py-2.5 text-sm font-semibold transition {{ $tipo === $key ? "border-{$clr}-500 bg-{$clr}-50 text-{$clr}-700" : 'border-gray-200 text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Product search --}}
                <div class="relative">
                    <label class="mb-1 block text-xs font-semibold text-gray-500 uppercase tracking-wider">Producto *</label>
                    <flux:input wire:model.live.debounce.300ms="productSearch" wire:focus="$set('showProductDropdown', true)"
                        placeholder="Buscar por nombre o SKU..." icon="magnifying-glass" />
                    @if ($showProductDropdown && count($searchResults) > 0)
                        <div class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                            @foreach ($searchResults as $p)
                                <button type="button" wire:click="selectProduct({{ $p->id }})"
                                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-gray-50 transition-colors">
                                    <span class="font-medium text-gray-900">{{ $p->nombre }}</span>
                                    <span class="text-gray-400">({{ $p->sku }})</span>
                                    <span class="ml-auto text-xs text-gray-500">Stock: {{ $p->stock }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Cantidad + Costo --}}
                <div class="grid grid-cols-2 gap-4">
                    <flux:input type="number" wire:model.live="cantidad" min="1"
                        label="{{ $tipo === 'ajuste' ? 'Stock Corregido *' : 'Cantidad *' }}" />
                    <flux:input type="number" wire:model="costo_unitario" min="0" step="0.01" label="Costo Unitario" />
                </div>

                {{-- Transfer fields --}}
                @if ($tipo === 'transferencia')
                    <div class="grid grid-cols-2 gap-4 rounded-xl border border-blue-200 bg-blue-50/50 p-4">
                        <flux:select wire:model="sucursal_origen_id" label="Sucursal Origen *" placeholder="Seleccionar...">
                            @foreach ($sucursales as $s)
                                <flux:select.option value="{{ $s->id }}">{{ $s->nombre }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:select wire:model="sucursal_destino_id" label="Sucursal Destino *" placeholder="Seleccionar...">
                            @foreach ($sucursales as $s)
                                <flux:select.option value="{{ $s->id }}">{{ $s->nombre }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif

                {{-- Stock preview --}}
                @if ($product_id)
                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="mb-2 text-xs font-semibold uppercase text-gray-500 tracking-wider">Vista previa de stock</p>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600">Stock Anterior: <strong class="text-gray-900">{{ number_format($stockAnterior) }}</strong></span>
                            <iconify-icon icon="heroicons:arrow-right" class="h-4 w-4 text-gray-400"></iconify-icon>
                            <span class="text-sm text-gray-600">Stock Nuevo: <strong class="{{ $stockNuevo > $stockAnterior ? 'text-emerald-600' : ($stockNuevo < $stockAnterior ? 'text-red-600' : 'text-gray-900') }}">{{ number_format($stockNuevo) }}</strong></span>
                        </div>
                    </div>
                @endif

                {{-- Referencia + Motivo --}}
                <flux:input wire:model="referencia" label="Referencia" placeholder="Ej: OC-20260625-001" />
                <flux:textarea wire:model="motivo" label="Motivo {{ $tipo === 'ajuste' ? '*' : '' }}" placeholder="Motivo del movimiento..." rows="2" />
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <flux:button x-on:click="Flux.modal('modal-create-movimiento').close()">Cancelar</flux:button>
                <flux:button wire:click="save" variant="primary">
                    <iconify-icon icon="heroicons:check-circle" class="h-4 w-4"></iconify-icon>
                    Registrar Movimiento
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Detail Modal --}}
    <flux:modal name="modal-detail-movimiento" class="min-w-[28rem]">
        @if ($detailMovement)
            <div class="p-6">
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">Detalle del Movimiento</h2>
                    <flux:button variant="ghost" size="sm" x-on:click="Flux.modal('modal-detail-movimiento').close()">
                        <iconify-icon icon="heroicons:x-mark" class="h-5 w-5"></iconify-icon>
                    </flux:button>
                </div>
                @php $dc = $detailMovement->tipo_color; @endphp
                <div class="space-y-3">
                    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2.5">
                        <span class="text-sm text-gray-500">Tipo</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-{{ $dc }}-50 px-2.5 py-0.5 text-xs font-semibold text-{{ $dc }}-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-{{ $dc }}-500"></span>
                            {{ ucfirst($detailMovement->tipo) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-2">
                        <span class="text-sm text-gray-500">Producto</span>
                        <span class="text-sm font-medium text-gray-900">{{ $detailMovement->product?->nombre }} ({{ $detailMovement->product?->sku }})</span>
                    </div>
                    @if ($detailMovement->variant)
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2.5">
                            <span class="text-sm text-gray-500">Variante</span>
                            <span class="text-sm text-gray-900">{{ $detailMovement->variant->combinacion }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between px-4 py-2">
                        <span class="text-sm text-gray-500">Cantidad</span>
                        <span class="text-sm font-bold text-{{ $dc }}-600">{{ number_format($detailMovement->cantidad) }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2.5">
                        <span class="text-sm text-gray-500">Stock Anterior → Nuevo</span>
                        <span class="text-sm text-gray-900">{{ number_format($detailMovement->stock_anterior) }} → {{ number_format($detailMovement->stock_nuevo) }}</span>
                    </div>
                    @if ($detailMovement->costo_unitario)
                        <div class="flex items-center justify-between px-4 py-2">
                            <span class="text-sm text-gray-500">Costo Unitario</span>
                            <span class="text-sm text-gray-900">${{ number_format($detailMovement->costo_unitario, 2) }}</span>
                        </div>
                    @endif
                    @if ($detailMovement->sucursalOrigen)
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2.5">
                            <span class="text-sm text-gray-500">Origen</span>
                            <span class="text-sm text-gray-900">{{ $detailMovement->sucursalOrigen->nombre }}</span>
                        </div>
                    @endif
                    @if ($detailMovement->sucursalDestino)
                        <div class="flex items-center justify-between px-4 py-2">
                            <span class="text-sm text-gray-500">Destino</span>
                            <span class="text-sm text-gray-900">{{ $detailMovement->sucursalDestino->nombre }}</span>
                        </div>
                    @endif
                    @if ($detailMovement->referencia)
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2.5">
                            <span class="text-sm text-gray-500">Referencia</span>
                            <span class="text-sm font-mono text-gray-900">{{ $detailMovement->referencia }}</span>
                        </div>
                    @endif
                    @if ($detailMovement->motivo)
                        <div class="flex items-center justify-between px-4 py-2">
                            <span class="text-sm text-gray-500">Motivo</span>
                            <span class="text-sm text-gray-900">{{ $detailMovement->motivo }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2.5">
                        <span class="text-sm text-gray-500">Usuario</span>
                        <span class="text-sm text-gray-900">{{ $detailMovement->user?->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-2">
                        <span class="text-sm text-gray-500">Fecha</span>
                        <span class="text-sm text-gray-900">{{ $detailMovement->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
                <div class="mt-6 text-right">
                    <flux:button x-on:click="Flux.modal('modal-detail-movimiento').close()">Cerrar</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
