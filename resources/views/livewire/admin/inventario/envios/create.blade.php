<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Nuevo Envío</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Crea un envío para una orden existente.</p>
        </div>
        <a href="{{ route('admin.envios') }}" wire:navigate>
            <flux:button variant="ghost" class="!text-gray-600">
                <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon> Volver
            </flux:button>
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Order selection --}}
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Orden Asociada</h2>
                <div class="relative">
                    <flux:input wire:model.live.debounce.300ms="orderSearch" wire:focus="$set('showOrderDropdown', true)" placeholder="Buscar por número de orden..." icon="magnifying-glass" />
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
                <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Asignación de Transporte</h2>
                </div>

                {{-- Tabs selector --}}
                <div class="mb-6 rounded-lg bg-gray-100 p-1 dark:bg-gray-700/50 flex">
                    <button type="button" wire:click="$set('tipo_transporte', 'carrier')"
                        class="flex-1 py-2 text-center text-sm font-medium rounded-md transition-all duration-150 {{ $tipo_transporte === 'carrier' ? 'bg-white text-gray-900 shadow dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}">
                        <iconify-icon icon="heroicons:truck" class="inline-block mr-1.5 h-4 w-4 align-text-bottom"></iconify-icon>
                        Transportadora Externa
                    </button>
                    <button type="button" wire:click="$set('tipo_transporte', 'empleado')"
                        class="flex-1 py-2 text-center text-sm font-medium rounded-md transition-all duration-150 {{ $tipo_transporte === 'empleado' ? 'bg-white text-gray-900 shadow dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}">
                        <iconify-icon icon="heroicons:user" class="inline-block mr-1.5 h-4 w-4 align-text-bottom"></iconify-icon>
                        Empleado Interno
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @if ($tipo_transporte === 'carrier')
                        <div>
                            <flux:input wire:model="carrier_name" label="Nombre Transportadora" placeholder="Ej: DHL, FedEx, MRW..." />
                        </div>
                        <div>
                            <flux:input wire:model="tracking_number" label="Número de Tracking" placeholder="Número de seguimiento..." />
                        </div>
                    @else
                        <div class="sm:col-span-2">
                            <flux:select wire:model="empleado_id" label="Empleado Responsable" placeholder="Selecciona un empleado...">
                                @foreach ($empleados as $emp)
                                    <flux:select.option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->cargo ?? 'Sin cargo' }})</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                    @endif

                    <div>
                        <flux:input type="date" wire:model="fecha_envio" label="Fecha de Envío" />
                    </div>
                    <div>
                        <flux:input type="date" wire:model="fecha_entrega_esperada" label="Entrega Esperada" />
                    </div>
                    <div>
                        <flux:input type="number" wire:model="peso" min="0" step="0.001" label="Peso (kg)" />
                    </div>
                    <div>
                        <flux:input type="number" wire:model="costo_envio" min="0" step="0.01" label="Costo de Envío" />
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Dirección de Destino</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:input wire:model="direccion_destino" label="Dirección" />
                    </div>
                    <div>
                        <flux:input wire:model="ciudad_destino" label="Ciudad" />
                    </div>
                    <div>
                        <flux:input wire:model="estado_destino" label="Estado/Provincia" />
                    </div>
                    <div>
                        <flux:input wire:model="codigo_postal_destino" label="Código Postal" />
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <flux:textarea wire:model="notas" rows="3" label="Notas" placeholder="Notas del envío..." />
            </div>
        </div>

        <div>
            <div class="sticky top-6 rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Acciones</h2>
                <div class="space-y-2">
                    <flux:button class="w-full" wire:click="save('preparando')" :disabled="!$order_id">
                        Crear en Preparando
                    </flux:button>
                    <flux:button variant="primary" class="w-full !bg-cyan-600 hover:!bg-cyan-700" wire:click="save('enviado')" :disabled="!$order_id">
                        Crear y Marcar Enviado
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
</div>
