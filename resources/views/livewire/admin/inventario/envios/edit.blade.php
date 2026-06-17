<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Envío: {{ $shipment->numero }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Orden: <span class="font-semibold text-cyan-600">{{ $shipment->order?->numero ?? '-' }}</span>
                @if ($shipment->order?->customer) | Cliente: {{ $shipment->order->customer->nombre ?? $shipment->order->customer->email }} @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.envios') }}" wire:navigate>
                <flux:button variant="ghost" class="!text-gray-600">
                    <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon> Volver
                </flux:button>
            </a>
            <a href="{{ route('admin.envios.guia-despacho', $shipment->id) }}" target="_blank">
                <flux:button variant="ghost" class="!text-cyan-600 hover:!bg-cyan-50">
                    <iconify-icon icon="heroicons:document-text" class="h-4 w-4"></iconify-icon> Guía Despacho
                </flux:button>
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">{{ session('success') }}</div>
    @endif

    {{-- Status bar --}}
    @php $ec = $shipment->estado_color; @endphp
    <div class="mb-6 rounded-lg border border-{{ $ec }}-200 bg-{{ $ec }}-50 p-4 dark:border-{{ $ec }}-800 dark:bg-{{ $ec }}-900/20">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center rounded-full bg-{{ $ec }}-100 px-3 py-1 text-sm font-semibold text-{{ $ec }}-800 dark:bg-{{ $ec }}-900/30 dark:text-{{ $ec }}-400">
                    {{ $shipment->estado_label }}
                </span>
                @if ($shipment->retrasado)
                    <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/50 dark:text-red-400">
                        <x-heroicons:exclamation-triangle class="mr-1 h-3 w-3" /> Retrasado
                    </span>
                @endif
                @if ($shipment->tracking_number)
                    <span class="text-sm text-gray-500 dark:text-gray-400">Tracking: <span class="font-mono">{{ $shipment->tracking_number }}</span></span>
                @endif
            </div>
            <div class="flex gap-2">
                @if ($estado === 'preparando')
                    <flux:button size="sm" wire:click="cambiarEstado('enviado')" class="!bg-blue-600 !text-white hover:!bg-blue-700">Marcar Enviado</flux:button>
                @elseif ($estado === 'enviado')
                    <flux:button size="sm" wire:click="cambiarEstado('en_transito')" class="!bg-amber-600 !text-white hover:!bg-amber-700">En Tránsito</flux:button>
                @elseif ($estado === 'en_transito')
                    <flux:button size="sm" wire:click="cambiarEstado('entregado')" class="!bg-emerald-600 !text-white hover:!bg-emerald-700">Entregado</flux:button>
                @endif
                @if (!in_array($estado, ['entregado', 'devuelto']))
                    <flux:button size="sm" variant="ghost" wire:click="cambiarEstado('devuelto')" wire:confirm="¿Marcar como devuelto?"
                        class="!text-red-600 hover:!bg-red-50">Devuelto</flux:button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Carrier --}}
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
                        <flux:input wire:model="carrier_name" label="Transportadora" />
                    </div>
                    <div>
                        <flux:input wire:model="tracking_number" label="Tracking" />
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
                    <flux:input type="date" wire:model="fecha_envio" label="Fecha Envío" />
                </div>
                <div>
                    <flux:input type="date" wire:model="fecha_entrega_esperada" label="Entrega Esperada" />
                </div>
                <div>
                    <flux:input type="number" wire:model="peso" min="0" step="0.001" label="Peso (kg)" />
                </div>
                <div>
                    <flux:input type="number" wire:model="costo_envio" min="0" step="0.01" label="Costo Envío" />
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Dirección de Destino</h2>
            <div class="space-y-4">
                <div>
                    <flux:input wire:model="direccion_destino" label="Dirección" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:input wire:model="ciudad_destino" label="Ciudad" />
                    </div>
                    <div>
                        <flux:input wire:model="estado_destino" label="Estado" />
                    </div>
                </div>
                <div>
                    <flux:input wire:model="codigo_postal_destino" label="Código Postal" />
                </div>
            </div>
        </div>

        <div class="sm:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <flux:textarea wire:model="notas" rows="3" label="Notas" />
            </div>
        </div>
    </div>

    <div class="mt-6 flex justify-end">
        <flux:button variant="primary" wire:click="save" class="!bg-cyan-600 hover:!bg-cyan-700">
            Guardar Cambios
        </flux:button>
    </div>
</div>
