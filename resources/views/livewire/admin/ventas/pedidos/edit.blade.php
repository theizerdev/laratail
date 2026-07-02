<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Editar Pedido {{ $order->numero }}</h2>
            <p class="mt-1 text-sm text-gray-500">Modifica la información del pedido.</p>
        </div>
        <a href="{{ route('admin.pedidos') }}" wire:navigate>
            <flux:button variant="ghost"><iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon>Volver</flux:button>
        </a>
    </div>

    {{-- Alerts --}}
    @if (session()->has('error'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            <iconify-icon icon="heroicons:exclamation-circle" class="h-5 w-5"></iconify-icon>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Cliente --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-600">
                        <iconify-icon icon="heroicons:user-solid" class="h-4 w-4"></iconify-icon>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800">Cliente</h3>
                    @if($customer_id)
                        <span class="ml-auto inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                            <iconify-icon icon="heroicons:check-circle-solid" class="h-3 w-3"></iconify-icon>
                            {{ \App\Models\Customer::find($customer_id)?->nombre_completo }}
                        </span>
                    @endif
                </div>
                <div class="relative">
                    <flux:input wire:model.live.debounce.300ms="customerSearch" placeholder="Buscar por nombre..." icon="magnifying-glass" />
                    @if($customerSearch && count($customers) > 0)
                        <div class="absolute z-20 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-lg max-h-40 overflow-y-auto">
                            @foreach($customers as $c)
                                <button wire:click="$set('customer_id', {{ $c->id }}); $set('customerSearch', '')" class="flex items-center gap-3 w-full px-4 py-2.5 text-left text-sm hover:bg-blue-50 transition-colors">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">{{ strtoupper(substr($c->nombre, 0, 1)) }}</div>
                                    <div>
                                        <div class="font-medium text-gray-800">{{ $c->nombre_completo }}</div>
                                        <div class="text-xs text-gray-400">{{ $c->email ?? 'Sin email' }}</div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Product Cart --}}
            @include('livewire.admin.ventas.partials._product_cart')

            {{-- Notas --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600">
                        <iconify-icon icon="heroicons:document-text-solid" class="h-4 w-4"></iconify-icon>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800">Notas</h3>
                </div>
                <flux:textarea wire:model="notas_cliente" label="Notas del cliente" rows="2" placeholder="Instrucciones del cliente..." />
                <div class="mt-3">
                    <flux:textarea wire:model="notas_internas" label="Notas internas" rows="2" placeholder="Notas internas del equipo..." />
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Resumen --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-sm font-bold text-gray-800">Resumen del Pedido</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal ({{ count($items) }} items)</span>
                        <span class="font-semibold text-gray-800">${{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Descuento</span>
                        <span class="font-semibold text-red-500">-${{ number_format($descuento, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Envío</span>
                        <input type="number" wire:model.live="envio" min="0" step="0.01" class="w-24 h-8 rounded-lg border border-gray-200 text-right text-sm font-medium focus:border-amber-300 focus:ring-1 focus:ring-amber-100 focus:outline-none" />
                    </div>
                    <div class="border-t border-dashed border-gray-200 pt-3">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-bold text-gray-900">Total</span>
                            <span class="text-xl font-bold text-amber-600">${{ number_format($total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Estado --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600">
                        <iconify-icon icon="heroicons:flag-solid" class="h-4 w-4"></iconify-icon>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800">Estado</h3>
                </div>
                <flux:select wire:model="estado" label="Estado">
                    @foreach(['borrador','pendiente','confirmado','procesando','enviado','entregado','cancelado','devuelto'] as $e)
                        <option value="{{ $e }}">{{ ucfirst($e) }}</option>
                    @endforeach
                </flux:select>
                <div class="mt-3">
                    <flux:select wire:model="estado_pago" label="Pago">
                        <option value="pendiente">Pendiente</option>
                        <option value="parcial">Parcial</option>
                        <option value="pagado">Pagado</option>
                        <option value="reembolsado">Reembolsado</option>
                    </flux:select>
                </div>
                <div class="mt-3">
                    <flux:select wire:model="asignado_a" label="Asignar empleado">
                        <option value="">Sin asignar</option>
                        @foreach($empleados as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

            {{-- Cupón --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-purple-100 text-purple-600">
                        <iconify-icon icon="heroicons:ticket-solid" class="h-4 w-4"></iconify-icon>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800">Cupón</h3>
                </div>
                <div class="flex gap-2">
                    <flux:input wire:model="codigo_cupon" placeholder="Código del cupón" />
                    <flux:button wire:click="aplicarCupon" variant="ghost" class="!text-amber-600 flex-shrink-0">Aplicar</flux:button>
                </div>
                @if(session()->has('cupon_msg'))
                    <p class="mt-2 text-xs text-emerald-600 font-medium">{{ session('cupon_msg') }}</p>
                @endif
                @if(session()->has('cupon_error'))
                    <p class="mt-2 text-xs text-red-500 font-medium">{{ session('cupon_error') }}</p>
                @endif
            </div>

            {{-- Submit --}}
            <flux:button wire:click="save" class="w-full !bg-amber-500 hover:!bg-amber-600 !text-white !text-base !py-3 rounded-xl shadow-lg shadow-amber-200">
                <iconify-icon icon="heroicons:check-circle" class="h-5 w-5"></iconify-icon>
                Actualizar Pedido
            </flux:button>
        </div>
    </div>
</div>