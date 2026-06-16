<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Nuevo Envío</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Crea un envío para una orden existente.</p>
        </div>
        <a href="{{ route('admin.envios') }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
            
            <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon> Volver
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Order selection --}}
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Orden Asociada</h2>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="orderSearch" wire:focus="$set('showOrderDropdown', true)"
                        placeholder="Buscar por número de orden..."
                        class="w-full rounded-lg border-gray-300 pl-10 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                     <iconify-icon icon="heroicons:magnifying-glass" class="absolute left-3 top-2.5 h-5 w-5 text-gray-400"></iconify-icon>
                    @if ($showOrderDropdown && count($searchResults) > 0)
                        <div class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700">
                            @foreach ($searchResults as $o)
                                <button type="button" wire:click="selectOrder({{ $o->id }})" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <span class="font-semibold text-cyan-600">{{ $o->numero }}</span>
                                    @if ($o->customer)
                                        <span class="text-gray-500">- {{ $o->customer->nombre ?? $o->customer->email }}</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
                @if ($selectedOrder)
                    <div class="mt-3 rounded-lg bg-gray-50 p-3 dark:bg-gray-700/50">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            <strong>Cliente:</strong> {{ $selectedOrder->customer?->nombre ?? 'N/A' }} |
                            <strong>Total:</strong> ${{ number_format($selectedOrder->total, 2) }}
                        </p>
                    </div>
                @endif
            </div>

            {{-- Carrier info --}}
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Transportadora</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre Transportadora</label>
                        <input type="text" wire:model="carrier_name" placeholder="Ej: DHL, FedEx, MRW..."
                            class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Tracking</label>
                        <input type="text" wire:model="tracking_number" placeholder="Número de seguimiento..."
                            class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Envío</label>
                        <input type="date" wire:model="fecha_envio"
                            class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Entrega Esperada</label>
                        <input type="date" wire:model="fecha_entrega_esperada"
                            class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Peso (kg)</label>
                        <input type="number" wire:model="peso" min="0" step="0.001"
                            class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Costo de Envío</label>
                        <input type="number" wire:model="costo_envio" min="0" step="0.01"
                            class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Dirección de Destino</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                        <input type="text" wire:model="direccion_destino"
                            class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Ciudad</label>
                        <input type="text" wire:model="ciudad_destino"
                            class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Estado/Provincia</label>
                        <input type="text" wire:model="estado_destino"
                            class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Código Postal</label>
                        <input type="text" wire:model="codigo_postal_destino"
                            class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Notas</label>
                <textarea wire:model="notas" rows="3" placeholder="Notas del envío..."
                    class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
            </div>
        </div>

        <div>
            <div class="sticky top-6 rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Acciones</h2>
                <div class="space-y-2">
                    <button wire:click="save('preparando')" disabled="{{ !$order_id }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-600 dark:text-gray-300">
                        Crear en Preparando
                    </button>
                    <button wire:click="save('enviado')" disabled="{{ !$order_id }}"
                        class="w-full rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Crear y Marcar Enviado
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
