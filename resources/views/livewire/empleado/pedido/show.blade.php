<div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
    @if(!$token_valido)
        <div class="max-w-md mx-auto">
            <flux:card class="text-center py-8">
                <div class="flex justify-center mb-4">
                    <div class="p-3 bg-red-100 dark:bg-red-950/30 rounded-full text-red-600 dark:text-red-400">
                        <iconify-icon icon="heroicons:x-circle" class="h-10 w-10"></iconify-icon>
                    </div>
                </div>
                <flux:heading size="lg" class="mb-2">Enlace no disponible</flux:heading>
                <flux:subheading>El enlace de acceso es inválido o ha expirado. Por favor, solicita un nuevo enlace al administrador.</flux:subheading>
            </flux:card>
        </div>
    @else
        <!-- Notifications -->
        <div class="mb-6">
            @if(session()->has('error'))
                <flux:card class="!bg-red-50 dark:!bg-red-950/20 border-l-4 border-red-500 p-4 mb-4">
                    <div class="flex gap-3">
                        <iconify-icon icon="heroicons:exclamation-triangle" class="h-5 w-5 text-red-500"></iconify-icon>
                        <span class="text-sm font-medium text-red-800 dark:text-red-300">{{ session('error') }}</span>
                    </div>
                </flux:card>
            @endif

            @if(session()->has('message'))
                <flux:card class="!bg-emerald-50 dark:!bg-emerald-950/20 border-l-4 border-emerald-500 p-4 mb-4">
                    <div class="flex gap-3">
                        <iconify-icon icon="heroicons:check-circle" class="h-5 w-5 text-emerald-500"></iconify-icon>
                        <span class="text-sm font-medium text-emerald-800 dark:text-emerald-300">{{ session('message') }}</span>
                    </div>
                </flux:card>
            @endif
        </div>

        <!-- Header -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <flux:heading size="xl">Pedido #{{ $order->numero }}</flux:heading>
                    
                    @php
                        $badgeColors = [
                            'asignado' => 'blue',
                            'procesando' => 'amber',
                            'enviado' => 'indigo',
                            'entregado' => 'emerald',
                            'cancelado' => 'red',
                            'devuelto' => 'zinc'
                        ];
                        $color = $badgeColors[$order->estado] ?? 'zinc';
                    @endphp
                    <flux:badge color="{{ $color }}" size="lg" class="capitalize">
                        {{ $order->estado_label }}
                    </flux:badge>
                </div>
                <flux:subheading class="mt-1">
                    Registrado el {{ $order->created_at->format('d/m/Y h:i A') }}
                </flux:subheading>
            </div>
            
            <div class="flex items-center gap-3">
                <flux:button href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->customer->whatsapp ?? $order->customer->telefono ?? '') }}" target="_blank" variant="subtle" class="flex items-center gap-2">
                    <iconify-icon icon="logos:whatsapp-icon" class="h-4 w-4"></iconify-icon>
                    Contactar Cliente
                </flux:button>
            </div>
        </div>

        <!-- Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Side: Order Items and Actions (2 Columns on Desktop) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Card: Products -->
                <flux:card class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="lg">Productos del Pedido</flux:heading>
                        <flux:badge size="sm" color="zinc">{{ $order->items->count() }} ítems</flux:badge>
                    </div>
                    
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach($order->items as $item)
                            <div class="py-4 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    @if($item->product && $item->product->imagen_principal_url)
                                        <img src="{{ $item->product->imagen_principal_url }}" alt="{{ $item->nombre_producto }}" class="h-10 w-10 rounded-lg object-cover">
                                    @else
                                        <div class="h-10 w-10 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-500">
                                            <iconify-icon icon="heroicons:shopping-bag" class="h-5 w-5"></iconify-icon>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-zinc-900 dark:text-zinc-100 text-sm">
                                            {{ $item->nombre_producto }}
                                        </p>
                                        <p class="text-xs text-zinc-500">
                                            Cant: {{ $item->cantidad }} &times; ${{ number_format($item->precio_unitario, 2) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                    ${{ number_format($item->subtotal, 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <flux:separator class="my-4" />

                    <div class="bg-zinc-50 dark:bg-zinc-900/50 p-4 rounded-xl flex justify-between items-center">
                        <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Total a cobrar:</span>
                        <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                            ${{ number_format($order->total, 2) }}
                        </span>
                    </div>
                </flux:card>

                <!-- Card: Update Status -->
                <flux:card class="p-6">
                    <flux:heading size="lg" class="mb-4">Actualizar Estado del Envío</flux:heading>
                    
                    <form wire:submit.prevent="actualizarEstado" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <flux:select wire:model="estado" label="Estado del Pedido">
                                    <option value="asignado">Asignado</option>
                                    <option value="procesando">Procesando (En preparación)</option>
                                    <option value="enviado">Enviado (En camino)</option>
                                    <option value="entregado">Entregado</option>
                                    <option value="devuelto">Devuelto</option>
                                    <option value="cancelado">Cancelado</option>
                                </flux:select>
                            </div>
                            
                            <div>
                                <flux:input 
                                    type="text" 
                                    wire:model="numero_seguimiento" 
                                    label="Número de Seguimiento (opcional)" 
                                    placeholder="Ej: Tracking #12345"
                                />
                                @error('numero_seguimiento') 
                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <flux:button 
                                type="submit" 
                                variant="primary" 
                                wire:loading.attr="disabled"
                                class="w-full sm:w-auto"
                            >
                                <span wire:loading.remove>Guardar Estado</span>
                                <span wire:loading class="flex items-center gap-2">
                                    <iconify-icon icon="line-md:loading-twotone-loop" class="h-4 w-4"></iconify-icon>
                                    Actualizando...
                                </span>
                            </flux:button>
                        </div>
                    </form>
                </flux:card>

            </div>

            <!-- Right Side: Details & Location (1 Column on Desktop) -->
            <div class="space-y-6">
                
                <!-- Card: Customer -->
                <flux:card class="p-6">
                    <flux:heading size="lg" class="mb-4">Cliente</flux:heading>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 rounded-full bg-indigo-50 dark:bg-indigo-950/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <iconify-icon icon="heroicons:user" class="h-5 w-5"></iconify-icon>
                            </div>
                            <div>
                                <p class="text-xs text-zinc-500">Nombre completo</p>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $order->customer->nombre }} {{ $order->customer->apellido }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 rounded-full bg-indigo-50 dark:bg-indigo-950/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <iconify-icon icon="heroicons:phone" class="h-5 w-5"></iconify-icon>
                            </div>
                            <div>
                                <p class="text-xs text-zinc-500">Teléfono</p>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $order->customer->telefono }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 rounded-full bg-indigo-50 dark:bg-indigo-950/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <iconify-icon icon="heroicons:envelope" class="h-5 w-5"></iconify-icon>
                            </div>
                            <div>
                                <p class="text-xs text-zinc-500">Email</p>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 break-all">
                                    {{ $order->customer->email }}
                                </p>
                            </div>
                        </div>
                    </div>
                </flux:card>

                <!-- Card: Delivery & Navigation -->
                <flux:card class="p-6">
                    <flux:heading size="lg" class="mb-4">Dirección de Entrega</flux:heading>
                    
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="mt-0.5 text-zinc-400">
                                <iconify-icon icon="heroicons:map-pin" class="h-5 w-5"></iconify-icon>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-950 dark:text-zinc-50">
                                    {{ $order->direccion_envio }}
                                </p>
                                <p class="text-xs text-zinc-500 mt-0.5">
                                    {{ $order->ciudad_envio }}, {{ $order->estado_envio }}
                                </p>
                            </div>
                        </div>

                        @if($this->ubicacion_maps)
                            <div class="pt-2 flex flex-col gap-2">
                                <flux:button href="{{ $this->ubicacion_maps }}" target="_blank" class="w-full justify-center flex items-center gap-2">
                                    <iconify-icon icon="logos:google-maps" class="h-4 w-4"></iconify-icon>
                                    Navegar con Google Maps
                                </flux:button>
                                
                                @if($order->latitud && $order->longitud)
                                    <flux:button href="https://waze.com/ul?ll={{ $order->latitud }},{{ $order->longitud }}&navigate=yes" target="_blank" class="w-full justify-center flex items-center gap-2">
                                        <iconify-icon icon="fa6-brands:waze" class="h-4 w-4 text-[#33ccff]"></iconify-icon>
                                        Navegar con Waze
                                    </flux:button>
                                @endif
                            </div>
                        @endif
                    </div>
                </flux:card>

                <!-- Card: Payment Info -->
                <flux:card class="p-6">
                    <flux:heading size="lg" class="mb-4">Método de Pago</flux:heading>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-zinc-500">Forma de Pago</p>
                            <p class="text-sm font-semibold text-zinc-950 dark:text-zinc-50 capitalize">
                                {{ str_replace('_', ' ', $order->metodo_pago) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-zinc-500">Estado de Pago</p>
                            <div class="mt-1">
                                @php
                                    $paymentBadgeColors = [
                                        'pagado' => 'emerald',
                                        'pendiente' => 'amber',
                                        'fallido' => 'red'
                                    ];
                                    $paymentColor = $paymentBadgeColors[$order->estado_pago] ?? 'zinc';
                                @endphp
                                <flux:badge color="{{ $paymentColor }}" size="sm">
                                    {{ $order->estado_pago_label }}
                                </flux:badge>
                            </div>
                        </div>

                        @if($order->referencia_pago)
                            <div>
                                <p class="text-xs text-zinc-500">Referencia de Transacción</p>
                                <p class="text-sm font-medium text-zinc-950 dark:text-zinc-50">
                                    {{ $order->referencia_pago }}
                                </p>
                            </div>
                        @endif
                    </div>
                </flux:card>

            </div>
            
        </div>
    @endif
</div>