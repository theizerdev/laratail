<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pagos</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Registra y consulta los pagos de pedidos.</p>
        </div>
        <button wire:click="openCreate" class="inline-flex items-center gap-1 rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700">
             <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon> Registrar Pago
        </button>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Cobrado</p>
            <p class="text-2xl font-bold text-emerald-600">${{ number_format($stats['total_cobrado'], 2) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Pendiente</p>
            <p class="text-2xl font-bold text-amber-600">${{ number_format($stats['total_pendiente'], 2) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Reembolsado</p>
            <p class="text-2xl font-bold text-blue-600">${{ number_format($stats['total_reembolsado'], 2) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Pagos</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_pagos'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por referencia u orden..."
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white sm:max-w-xs" />
        <select wire:model.live="filterEstado" class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
            <option value="all">Todos los estados</option>
            <option value="pendiente">Pendiente</option>
            <option value="completado">Completado</option>
            <option value="fallido">Fallido</option>
            <option value="reembolsado">Reembolsado</option>
        </select>
        <select wire:model.live="filterMetodo" class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
            <option value="all">Todos los métodos</option>
            <option value="efectivo">Efectivo</option>
            <option value="transferencia">Transferencia</option>
            <option value="tarjeta">Tarjeta</option>
            <option value="zelle">Zelle</option>
            <option value="binance">Binance</option>
            <option value="pago_movil">Pago Móvil</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Orden</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Monto</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Método</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Referencia</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Fecha</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($payments as $payment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            <span class="font-medium text-cyan-600 dark:text-cyan-400">{{ $payment->order?->numero ?? '-' }}</span>
                            <br><span class="text-xs text-gray-500">{{ $payment->order?->customer?->nombre ?? '-' }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">
                            ${{ number_format($payment->amount, 2) }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                            {{ $payment->metodo_pago_label }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                            {{ $payment->referencia ?? '-' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            <span class="inline-flex rounded-full bg-{{ $payment->estado_color }}-100 px-2 py-0.5 text-xs font-medium text-{{ $payment->estado_color }}-700 dark:bg-{{ $payment->estado_color }}-900/30 dark:text-{{ $payment->estado_color }}-400">
                                {{ ucfirst($payment->estado) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                            {{ $payment->fecha_pago?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                            <button wire:click="openDetail({{ $payment->id }})" class="text-gray-500 hover:text-cyan-600 dark:hover:text-cyan-400">
                                 <iconify-icon icon="heroicons:eye" class="h-4 w-4"></iconify-icon>
                            </button>
                            <button wire:click="openEdit({{ $payment->id }})" class="ml-2 text-gray-500 hover:text-blue-600 dark:hover:text-blue-400">
                                 <iconify-icon icon="heroicons:pencil" class="h-4 w-4"></iconify-icon>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay pagos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>

    {{-- Create/Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click.self="$set('showModal', false)">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">
                    {{ $editingId ? 'Editar Pago' : 'Registrar Pago' }}
                </h2>

                <div class="space-y-4">
                    {{-- Order selector --}}
                    <div class="relative">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Orden *</label>
                        <input type="text" wire:model.live.debounce.300ms="orderSearch" wire:focus="$set('showOrderDropdown', true)"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            placeholder="Buscar orden por número..." {{ $editingId ? 'disabled' : '' }} />
                        @if ($showOrderDropdown)
                            <div class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700">
                                @forelse (App\Models\Order::where('numero', 'like', '%' . $orderSearch . '%')->latest()->take(5)->get() as $ord)
                                    <button type="button" wire:click="selectOrder({{ $ord->id }})"
                                        class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-white">
                                        {{ $ord->numero }} — ${{ number_format($ord->total, 2) }}
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
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Monto *</label>
                            <input type="number" wire:model="amount" step="0.01" min="0"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Método de Pago *</label>
                            <select wire:model="metodo_pago"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="zelle">Zelle</option>
                                <option value="binance">Binance</option>
                                <option value="pago_movil">Pago Móvil</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Referencia</label>
                            <input type="text" wire:model="referencia"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="N° referencia" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                            <select wire:model="estado"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="pendiente">Pendiente</option>
                                <option value="completado">Completado</option>
                                <option value="fallido">Fallido</option>
                                <option value="reembolsado">Reembolsado</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Pago *</label>
                        <input type="datetime-local" wire:model="fecha_pago"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Notas</label>
                        <textarea wire:model="notas" rows="2"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            placeholder="Notas adicionales..."></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="$set('showModal', false)" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
                        Cancelar
                    </button>
                    <button wire:click="save" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700">
                        {{ $editingId ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Detail Modal --}}
    @if ($showDetailModal && $detailPayment)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click.self="$set('showDetailModal', false)">
            <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Detalle del Pago</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Orden:</span><span class="font-medium text-cyan-600 dark:text-cyan-400">{{ $detailPayment->order?->numero }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Cliente:</span><span class="dark:text-white">{{ $detailPayment->order?->customer?->nombre ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Monto:</span><span class="font-bold text-gray-900 dark:text-white">${{ number_format($detailPayment->amount, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Método:</span><span class="dark:text-white">{{ $detailPayment->metodo_pago_label }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Referencia:</span><span class="dark:text-white">{{ $detailPayment->referencia ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Estado:</span>
                        <span class="rounded-full bg-{{ $detailPayment->estado_color }}-100 px-2 py-0.5 text-xs font-medium text-{{ $detailPayment->estado_color }}-700">{{ ucfirst($detailPayment->estado) }}</span>
                    </div>
                    <div class="flex justify-between"><span class="text-gray-500">Fecha:</span><span class="dark:text-white">{{ $detailPayment->fecha_pago?->format('d/m/Y H:i') ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Registrado por:</span><span class="dark:text-white">{{ $detailPayment->user?->name ?? '-' }}</span></div>
                    @if ($detailPayment->notas)
                        <div class="border-t border-gray-200 pt-3 dark:border-gray-700">
                            <p class="text-gray-500">Notas:</p>
                            <p class="dark:text-white">{{ $detailPayment->notas }}</p>
                        </div>
                    @endif
                </div>
                <div class="mt-6 flex justify-end">
                    <button wire:click="$set('showDetailModal', false)" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">Cerrar</button>
                </div>
            </div>
        </div>
    @endif
</div>
