<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Envío: {{ $shipment->numero }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Orden: <span class="font-semibold text-cyan-600">{{ $shipment->order?->numero ?? '-' }}</span>
                @if ($shipment->order?->customer) | Cliente: {{ $shipment->order->customer->nombre ?? $shipment->order->customer->email }} @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.envios') }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
                <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon> Volver
            </a>
            <a href="{{ route('admin.envios.guia-despacho', $shipment->id) }}" class="inline-flex items-center gap-1 rounded-lg border border-cyan-300 px-4 py-2 text-sm font-medium text-cyan-700 hover:bg-cyan-50 dark:border-cyan-700 dark:text-cyan-400">
                <iconify-icon icon="heroicons:document-text" class="h-4 w-4"></iconify-icon> Guía Despacho
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
                    <button wire:click="cambiarEstado('enviado')" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700">Marcar Enviado</button>
                @elseif ($estado === 'enviado')
                    <button wire:click="cambiarEstado('en_transito')" class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-700">En Tránsito</button>
                @elseif ($estado === 'en_transito')
                    <button wire:click="cambiarEstado('entregado')" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">Entregado</button>
                @endif
                @if (!in_array($estado, ['entregado', 'devuelto']))
                    <button wire:click="cambiarEstado('devuelto')" wire:confirm="¿Marcar como devuelto?"
                        class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Devuelto</button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Carrier --}}
        <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Transportadora</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Transportadora</label>
                    <input type="text" wire:model="carrier_name" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tracking</label>
                    <input type="text" wire:model="tracking_number" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha Envío</label>
                    <input type="date" wire:model="fecha_envio" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Entrega Esperada</label>
                    <input type="date" wire:model="fecha_entrega_esperada" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Peso (kg)</label>
                    <input type="number" wire:model="peso" min="0" step="0.001" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Costo Envío</label>
                    <input type="number" wire:model="costo_envio" min="0" step="0.01" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Dirección de Destino</h2>
            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                    <input type="text" wire:model="direccion_destino" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Ciudad</label>
                        <input type="text" wire:model="ciudad_destino" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                        <input type="text" wire:model="estado_destino" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Código Postal</label>
                    <input type="text" wire:model="codigo_postal_destino" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
            </div>
        </div>

        <div class="sm:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Notas</label>
                <textarea wire:model="notas" rows="3" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
            </div>
        </div>
    </div>

    <div class="mt-6 flex justify-end">
        <button wire:click="save" class="rounded-lg bg-cyan-600 px-6 py-2 text-sm font-medium text-white hover:bg-cyan-700">
            Guardar Cambios
        </button>
    </div>
</div>
