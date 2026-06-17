<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pagos</h1>
            <p class="mt-1 text-sm text-gray-500">Registra y consulta los pagos de pedidos.</p>
        </div>
        <flux:button wire:click="openCreate" variant="primary">Registrar Pago</flux:button>
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
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                <iconify-icon icon="heroicons:banknotes-solid" class="h-5 w-5 text-emerald-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Total Cobrado</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($stats['total_cobrado'], 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-amber-100">
                <iconify-icon icon="heroicons:clock-solid" class="h-5 w-5 text-amber-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Pendiente</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($stats['total_pendiente'], 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
                <iconify-icon icon="heroicons:arrow-uturn-left-solid" class="h-5 w-5 text-blue-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Reembolsado</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($stats['total_reembolsado'], 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-gray-100">
                <iconify-icon icon="heroicons:receipt-percent-solid" class="h-5 w-5 text-gray-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Total Pagos</p>
            <p class="text-xl font-bold text-gray-900">{{ $stats['total_pagos'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por referencia u orden..." class="sm:max-w-xs" />
        <flux:select wire:model.live="filterEstado">
            <flux:select.option value="all">Todos los estados</flux:select.option>
            <flux:select.option value="pendiente">Pendiente</flux:select.option>
            <flux:select.option value="completado">Completado</flux:select.option>
            <flux:select.option value="fallido">Fallido</flux:select.option>
            <flux:select.option value="reembolsado">Reembolsado</flux:select.option>
        </flux:select>
        <flux:select wire:model.live="filterMetodo">
            <flux:select.option value="all">Todos los métodos</flux:select.option>
            <flux:select.option value="efectivo">Efectivo</flux:select.option>
            <flux:select.option value="transferencia">Transferencia</flux:select.option>
            <flux:select.option value="tarjeta">Tarjeta</flux:select.option>
            <flux:select.option value="zelle">Zelle</flux:select.option>
            <flux:select.option value="binance">Binance</flux:select.option>
            <flux:select.option value="pago_movil">Pago Móvil</flux:select.option>
        </flux:select>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Orden</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Monto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Método</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Referencia</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Fecha</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($payments as $payment)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            <span class="font-semibold text-cyan-600">{{ $payment->order?->numero ?? '-' }}</span>
                            <br><span class="text-xs text-gray-500">{{ $payment->order?->customer?->nombre ?? '-' }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-bold text-gray-900">
                            ${{ number_format($payment->amount, 2) }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                            {{ $payment->metodo_pago_label }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                            {{ $payment->referencia ?? '-' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            @php $color = $payment->estado_color; @endphp
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-{{ $color }}-50 px-2.5 py-1 text-xs font-semibold text-{{ $color }}-700 ring-1 ring-inset ring-{{ $color }}-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-{{ $color }}-500"></span>
                                {{ ucfirst($payment->estado) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                            {{ $payment->fecha_pago?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                            <div class="flex items-center justify-end gap-1">
                                <flux:button wire:click="openDetail({{ $payment->id }})" variant="ghost" size="sm" class="text-gray-400 hover:text-cyan-600"><iconify-icon icon="heroicons:eye" class="h-4 w-4"></iconify-icon></flux:button>
                                <flux:button wire:click="openEdit({{ $payment->id }})" variant="ghost" size="sm" class="text-gray-400 hover:text-blue-600"><iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon></flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <iconify-icon icon="heroicons:receipt-percent" class="h-10 w-10 text-gray-300"></iconify-icon>
                                <p class="text-sm text-gray-500">No hay pagos registrados.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>

    {{-- Create/Edit Modal --}}
    <flux:modal name="modal-pago" class="max-w-lg">
        <div class="p-1">
            <h2 class="mb-5 text-lg font-bold text-gray-900">
                {{ $editingId ? 'Editar Pago' : 'Registrar Pago' }}
            </h2>

            {{-- Cash register status --}}
            @if (!$editingId)
                @if ($cajaAbierta)
                    <div class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-2.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">
                        <iconify-icon icon="heroicons:check-circle-solid" class="h-4 w-4"></iconify-icon>
                        Caja abierta #{{ $cajaAbierta->id }} — Saldo: ${{ number_format($cajaAbierta->totalActual(), 2) }}
                    </div>
                @else
                    <div class="mb-4 flex items-center gap-2 rounded-xl bg-red-50 px-3 py-2.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-200">
                        <iconify-icon icon="heroicons:exclamation-triangle-solid" class="h-4 w-4"></iconify-icon>
                        No tienes caja abierta.
                        <a href="{{ route('admin.caja') }}" wire:navigate class="ml-auto font-bold underline hover:text-red-800">Abrir Caja</a>
                    </div>
                @endif
            @endif

            <div class="space-y-4">
                {{-- Order selector --}}
                <div class="relative">
                    <flux:label>Orden *</flux:label>
                    <flux:input wire:model.live.debounce.300ms="orderSearch" wire:focus="$set('showOrderDropdown', true)"
                        placeholder="Buscar orden por número..." :disabled="$editingId" />
                    @if ($showOrderDropdown)
                        <div class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                            @forelse (App\Models\Order::where('numero', 'like', '%' . $orderSearch . '%')->latest()->take(5)->get() as $ord)
                                <button type="button" wire:click="selectOrder({{ $ord->id }})"
                                    class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-100 transition-colors">
                                    <span class="font-medium">{{ $ord->numero }}</span> — ${{ number_format($ord->total, 2) }}
                                    <span class="text-xs text-gray-500">({{ $ord->customer?->nombre ?? 'S/C' }})</span>
                                </button>
                            @empty
                                <p class="px-3 py-2 text-sm text-gray-500">No se encontraron órdenes.</p>
                            @endforelse
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:label>Monto *</flux:label>
                        <flux:input wire:model="amount" type="number" step="0.01" min="0" />
                    </div>
                    <div>
                        <flux:label>Método de pago *</flux:label>
                        <flux:select wire:model="metodo_pago">
                            <flux:select.option value="efectivo">Efectivo</flux:select.option>
                            <flux:select.option value="transferencia">Transferencia</flux:select.option>
                            <flux:select.option value="tarjeta">Tarjeta</flux:select.option>
                            <flux:select.option value="zelle">Zelle</flux:select.option>
                            <flux:select.option value="binance">Binance</flux:select.option>
                            <flux:select.option value="pago_movil">Pago Móvil</flux:select.option>
                        </flux:select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:label>Referencia</flux:label>
                        <flux:input wire:model="referencia" placeholder="N° referencia" />
                    </div>
                    <div>
                        <flux:label>Estado</flux:label>
                        <flux:select wire:model="estado">
                            <flux:select.option value="pendiente">Pendiente</flux:select.option>
                            <flux:select.option value="completado">Completado</flux:select.option>
                            <flux:select.option value="fallido">Fallido</flux:select.option>
                            <flux:select.option value="reembolsado">Reembolsado</flux:select.option>
                        </flux:select>
                    </div>
                </div>

                <div>
                    <flux:label>Fecha de pago *</flux:label>
                    <flux:input wire:model="fecha_pago" type="datetime-local" />
                </div>

                <div>
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notas" rows="2" placeholder="Notas adicionales..." />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <flux:button x-on:click="Flux.modal('modal-pago').close()" wire:click="closeModal" variant="outline">Cancelar</flux:button>
                <flux:button wire:click="save" variant="primary">{{ $editingId ? 'Actualizar' : 'Guardar' }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Detail Modal --}}
    <flux:modal name="modal-detalle" class="max-w-md">
        <div class="p-1">
            <h2 class="mb-5 text-lg font-bold text-gray-900">Detalle del Pago</h2>
            @if ($detailPayment)
                <div class="space-y-3 rounded-xl bg-gray-50 p-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Orden</span>
                        <span class="font-semibold text-cyan-600">{{ $detailPayment->order?->numero }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Cliente</span>
                        <span class="font-medium">{{ $detailPayment->order?->customer?->nombre ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Monto</span>
                        <span class="font-bold text-gray-900">${{ number_format($detailPayment->amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Método</span>
                        <span>{{ $detailPayment->metodo_pago_label }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Referencia</span>
                        <span>{{ $detailPayment->referencia ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Estado</span>
                        @php $color = $detailPayment->estado_color; @endphp
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-{{ $color }}-50 px-2.5 py-1 text-xs font-semibold text-{{ $color }}-700 ring-1 ring-inset ring-{{ $color }}-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-{{ $color }}-500"></span>
                            {{ ucfirst($detailPayment->estado) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Fecha</span>
                        <span>{{ $detailPayment->fecha_pago?->format('d/m/Y H:i') ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Registrado por</span>
                        <span>{{ $detailPayment->user?->name ?? '-' }}</span>
                    </div>
                    @if ($detailPayment->notas)
                        <div class="border-t border-gray-200 pt-3">
                            <p class="text-gray-500">Notas</p>
                            <p class="mt-1 text-gray-700">{{ $detailPayment->notas }}</p>
                        </div>
                    @endif
                </div>
            @endif
            <div class="mt-6 flex justify-end">
                <flux:button x-on:click="Flux.modal('modal-detalle').close()" variant="outline">Cerrar</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
