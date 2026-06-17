<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pais;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.app')] #[Title('Checkout - Laratail Store')] class extends Component {
    public int $currentStep = 1;

    // Step 1: Shipping
    #[Validate('required|min:2|max:100')]
    public string $nombre = '';
    #[Validate('required|min:5|max:255')]
    public string $direccion = '';
    #[Validate('required|min:2|max:100')]
    public string $ciudad = '';
    #[Validate('nullable|max:50')]
    public string $estado_region = '';
    #[Validate('nullable|max:20')]
    public string $codigo_postal = '';
    #[Validate('required|integer|exists:pais,id')]
    public int $pais_id = 0;
    #[Validate('required|min:6|max:30')]
    public string $telefono = '';
    public ?int $selectedAddressId = null;

    // Coordenadas
    public ?float $latitud = null;
    public ?float $longitud = null;

    // Step 2: Payment
    public string $paymentMethod = 'contra_entrega';
    public string $notas_cliente = '';

    // Computed data
    public function with(): array
    {
        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreate();
        $subtotal = $cart->items->sum(fn($item) => $item->cantidad * $item->precio);
        $descuento = $cart->coupon ? $cart->coupon->calcularDescuento($subtotal) : 0;
        $total = max(0, $subtotal - $descuento);

        $addresses = collect();
        if (Auth::check()) {
            $customer = Customer::where('user_id', Auth::id())->first();
            if ($customer) {
                $addresses = $customer->addresses()->orderBy('predeterminada', 'desc')->get();
            }
        }

        return [
            'cart' => $cart,
            'items' => $cart->items->load('product'),
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'total' => $total,
            'paises' => Pais::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'addresses' => $addresses,
        ];
    }

    public function mount(): void
    {
        // Redirect if cart is empty
        $cart = app(CartService::class)->getCart();
        if (!$cart || $cart->items->isEmpty()) {
            $this->redirect('/carrito', navigate: true);
            return;
        }

        // Pre-fill from default address if logged in
        if (Auth::check()) {
            $customer = Customer::where('user_id', Auth::id())->first();
            if ($customer) {
                $defaultAddr = $customer->defaultAddress;
                if ($defaultAddr) {
                    $this->fillFromAddress($defaultAddr);
                } else {
                    $this->nombre = $customer->nombre_completo;
                    $this->telefono = $customer->telefono ?? '';
                }
            }
        }
    }

    public function fillFromAddress(CustomerAddress $address): void
    {
        $this->selectedAddressId = $address->id;
        $this->nombre = $address->nombre_destinatario;
        $this->direccion = $address->direccion;
        $this->ciudad = $address->ciudad;
        $this->estado_region = $address->estado_region ?? '';
        $this->codigo_postal = $address->codigo_postal ?? '';
        $this->pais_id = $address->pais_id;
        $this->telefono = $address->telefono_destinatario ?? '';
    }

    public function setLocation(float $lat, float $lng): void
    {
        $this->latitud = $lat;
        $this->longitud = $lng;
        
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'User-Agent' => 'LaratailStore/1.0'
            ])->get("https://nominatim.openstreetmap.org/reverse", [
                'lat' => $lat,
                'lon' => $lng,
                'format' => 'json',
                'addressdetails' => 1
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $address = $data['address'] ?? [];
                
                // Usar display_name para una dirección mucho más completa
                $this->direccion = $data['display_name'] ?? trim(($address['road'] ?? $address['pedestrian'] ?? $address['suburb'] ?? '') . ' ' . ($address['house_number'] ?? ''));
                $this->ciudad = $address['city'] ?? $address['town'] ?? $address['village'] ?? '';
                $this->estado_region = $address['state'] ?? $address['county'] ?? '';
                $this->codigo_postal = $address['postcode'] ?? '';
                
                $countryCode = strtoupper($address['country_code'] ?? '');
                if ($countryCode) {
                    $pais = Pais::where('codigo_iso2', $countryCode)->first();
                    if ($pais) {
                        $this->pais_id = $pais->id;
                    }
                }
            }
        } catch (\Exception $e) {
            // Fails silently if API fails
        }
    }

    public function selectSavedAddress(int $addressId): void
    {
        $address = CustomerAddress::find($addressId);
        if ($address) {
            $this->fillFromAddress($address);
        }
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    public function nextStep(): void
    {
        if ($this->currentStep === 1) {
            $this->validate();
            $this->currentStep = 2;
        } elseif ($this->currentStep === 2) {
            $this->validate([
                'paymentMethod' => 'required|in:contra_entrega,transferencia',
            ]);
            $this->currentStep = 3;
        }
    }

    public function confirmOrder(): void
    {
        $cart = app(CartService::class)->getOrCreate();
        $items = $cart->items->load('product');

        if ($items->isEmpty()) {
            $this->addError('order', 'El carrito está vacío.');
            return;
        }

        $this->validate();

        $subtotal = $items->sum(fn($item) => $item->cantidad * $item->precio);
        $descuento = $cart->coupon ? $cart->coupon->calcularDescuento($subtotal) : 0;
        $total = max(0, $subtotal - $descuento);

        DB::beginTransaction();
        try {
            $customer = null;
            if (Auth::check()) {
                $customer = Customer::where('user_id', Auth::id())->first();
            }

            $pais = Pais::find($this->pais_id);

            $order = Order::create([
                'customer_id' => $customer->id,
                'user_id' => Auth::id(),
                'tipo' => 'venta',
                'estado' => 'pendiente',
                'estado_pago' => $this->paymentMethod === 'transferencia' ? 'pendiente' : 'pendiente',
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'impuesto' => 0,
                'envio' => 0,
                'total' => $total,
                'coupon_id' => $cart->coupon_id,
                'codigo_cupon' => $cart->coupon?->codigo,
                'direccion_envio' => $this->direccion,
                'ciudad_envio' => $this->ciudad,
                'estado_envio' => $this->estado_region,
                'codigo_postal_envio' => $this->codigo_postal,
                'pais_envio_id' => $this->pais_id,
                'latitud' => $this->latitud,
                'longitud' => $this->longitud,
                'metodo_envio' => 'standard',
                'metodo_pago' => $this->paymentMethod,
                'notas_cliente' => $this->notas_cliente,
                'empresa_id' => 1,
                'sucursal_id' => 1,
            ]);

            // Create order items
            foreach ($items as $item) {
                $product = $item->product;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'nombre_producto' => $product ? $product->nombre : 'Producto Desconocido',
                    'sku' => $product ? $product->sku : null,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio,
                    'descuento' => 0,
                    'impuesto' => 0,
                    'subtotal' => $item->cantidad * $item->precio,
                ]);

                // Reduce stock
                if ($product && $product->rastrear_inventario) {
                    $stockAnterior = $product->stock;
                    $product->stock = max(0, $stockAnterior - $item->cantidad);
                    $product->save();

                    \App\Models\InventoryMovement::create([
                        'product_id' => $product->id,
                        'product_variant_id' => $item->product_variant_id,
                        'tipo' => 'salida',
                        'cantidad' => $item->cantidad,
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $product->stock,
                        'referencia' => 'ORD-' . $order->id,
                        'motivo' => 'Venta online',
                        'user_id' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            // Clear cart
            app(CartService::class)->clearCart();
            $this->dispatch('cart-updated');

            $this->redirect('/checkout/confirmacion/' . $order->id, navigate: true);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating order: ' . $e->getMessage());
            DB::rollBack();
            $this->addError('order', 'Ocurrió un error al procesar tu pedido. Intenta de nuevo.');
        }
    }
};
?>

<div>
    <!-- Breadcrumb -->
    <div class="bg-white border-b border-zinc-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <nav class="flex items-center gap-2 text-base">
                <a href="/carrito" wire:navigate class="text-zinc-500 hover:text-zinc-900 transition-colors">Carrito</a>
                <svg class="w-5 h-5 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="text-zinc-900 font-medium">Checkout</span>
            </nav>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16">
        <h1 class="text-4xl font-extrabold text-zinc-900 mb-10">Finalizar Compra</h1>

        <!-- Step Indicator -->
        <div class="flex items-center justify-center mb-12">
            @foreach([1 => 'Dirección', 2 => 'Pago', 3 => 'Confirmar'] as $num => $label)
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full text-base font-bold transition-colors
                        {{ $currentStep > $num ? 'bg-green-500 text-white' : ($currentStep === $num ? 'bg-indigo-600 text-white' : 'bg-zinc-100 text-zinc-400') }}">
                        @if($currentStep > $num)
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @else
                            {{ $num }}
                        @endif
                    </div>
                    <span class="ml-3 text-base font-medium {{ $currentStep >= $num ? 'text-zinc-900' : 'text-zinc-400' }}">{{ $label }}</span>
                    @if($num < 3)
                        <div class="w-20 sm:w-32 h-0.5 mx-4 {{ $currentStep > $num ? 'bg-green-500' : 'bg-zinc-200' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>

        @error('order')
            <div class="mb-8 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl text-base">{{ $message }}</div>
        @enderror

        <div class="flex flex-col lg:flex-row gap-10">
            <!-- Steps Content -->
            <div class="flex-1">
                {{-- Step 1: Shipping Address --}}
                @if($currentStep === 1)
                <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-zinc-900">Dirección de Envío</h2>
                        <flux:button 
                            variant="subtle" 
                            size="sm"
                            x-data="{ loading: false }"
                            @click="
                                if (navigator.geolocation) {
                                    loading = true;
                                    navigator.geolocation.getCurrentPosition(
                                        (position) => {
                                            $wire.setLocation(position.coords.latitude, position.coords.longitude).then(() => {
                                                loading = false;
                                            });
                                        },
                                        (error) => {
                                            alert('No se pudo obtener la ubicación. Verifica los permisos de tu navegador.');
                                            loading = false;
                                        }
                                    );
                                } else {
                                    alert('La geolocalización no está soportada en este navegador.');
                                }
                            "
                        >
                            <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <svg x-show="loading" style="display: none;" class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span x-show="!loading">Ubicación actual</span>
                            <span x-show="loading" style="display: none;">Buscando...</span>
                        </flux:button>
                    </div>

                    {{-- Saved addresses --}}
                    @if($addresses->count() > 0)
                    <div class="mb-6">
                        <p class="text-sm font-medium text-zinc-700 mb-3">Usar una dirección guardada:</p>
                        <div class="space-y-2">
                            @foreach($addresses as $addr)
                            <button wire:click="selectSavedAddress({{ $addr->id }})" class="w-full text-left p-4 rounded-xl border transition-colors {{ $selectedAddressId === $addr->id ? 'border-indigo-500 bg-indigo-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                                <p class="font-medium text-sm text-zinc-900">{{ $addr->nombre_destinatario }}</p>
                                <p class="text-xs text-zinc-500 mt-0.5">{{ $addr->direccion_completa }}</p>
                                @if($addr->predeterminada)
                                    <span class="text-xs text-indigo-600 font-medium">Predeterminada</span>
                                @endif
                            </button>
                            @endforeach
                        </div>
                        <p class="text-xs text-zinc-400 mt-3">O ingresa una dirección nueva:</p>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <flux:input wire:model="nombre" label="Nombre completo *" placeholder="Juan Pérez" />
                        </div>
                        <div class="sm:col-span-2">
                            <flux:input wire:model="direccion" label="Dirección *" placeholder="Calle 123, Apt 4" />
                        </div>
                        <div>
                            <flux:input wire:model="ciudad" label="Ciudad *" />
                        </div>
                        <div>
                            <flux:input wire:model="estado_region" label="Estado / Región" />
                        </div>
                        <div>
                            <flux:input wire:model="codigo_postal" label="Código Postal" />
                        </div>
                        <div>
                            <flux:select wire:model="pais_id" label="País *" placeholder="Seleccionar...">
                                @foreach($paises as $pais)
                                    <flux:select.option value="{{ $pais->id }}">{{ $pais->nombre }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        <div class="sm:col-span-2">
                            <flux:input wire:model="telefono" type="tel" label="Teléfono *" placeholder="+1 234 567 8900" />
                        </div>
                    </div>

                    <button wire:click="nextStep" class="mt-8 w-full bg-indigo-600 text-white py-4 rounded-xl font-semibold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">
                        Continuar al Pago
                    </button>
                </div>
                @endif

                {{-- Step 2: Payment Method --}}
                @if($currentStep === 2)
                <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 sm:p-8">
                    <h2 class="text-xl font-bold text-zinc-900 mb-6">Método de Pago</h2>

                    <div class="space-y-4">
                        <flux:radio.group wire:model="paymentMethod">
                            <flux:radio value="contra_entrega" label="Pago contra entrega" description="Paga al recibir tu pedido en efectivo o tarjeta." />
                            <flux:radio value="transferencia" label="Transferencia bancaria" description="Realiza una transferencia y envíanos el comprobante." />
                        </flux:radio.group>
                    </div>

                    <div class="mt-6">
                        <flux:textarea wire:model="notas_cliente" label="Notas del pedido (opcional)" rows="3" placeholder="Instrucciones especiales para la entrega..." />
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button wire:click="goToStep(1)" class="px-6 py-4 border border-zinc-300 rounded-xl font-medium text-zinc-700 hover:bg-zinc-50 transition-colors">
                            ← Atrás
                        </button>
                        <button wire:click="nextStep" class="flex-1 bg-indigo-600 text-white py-4 rounded-xl font-semibold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">
                            Revisar Pedido
                        </button>
                    </div>
                </div>
                @endif

                {{-- Step 3: Review & Confirm --}}
                @if($currentStep === 3)
                <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 sm:p-8">
                    <h2 class="text-xl font-bold text-zinc-900 mb-6">Revisar y Confirmar</h2>

                    <!-- Shipping Recap -->
                    <div class="bg-zinc-50 rounded-xl p-4 mb-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs text-zinc-500 uppercase font-medium">Envío a</p>
                                <p class="text-sm font-semibold text-zinc-900 mt-1">{{ $nombre }}</p>
                                <p class="text-sm text-zinc-600">{{ $direccion }}</p>
                                <p class="text-sm text-zinc-600">{{ $ciudad }}{{ $estado_region ? ', '.$estado_region : '' }} {{ $codigo_postal }}</p>
                                <p class="text-sm text-zinc-600">Tel: {{ $telefono }}</p>
                            </div>
                            <button wire:click="goToStep(1)" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Editar</button>
                        </div>
                    </div>

                    <!-- Payment Recap -->
                    <div class="bg-zinc-50 rounded-xl p-4 mb-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-xs text-zinc-500 uppercase font-medium">Método de pago</p>
                                <p class="text-sm font-semibold text-zinc-900 mt-1">
                                    {{ $paymentMethod === 'contra_entrega' ? 'Pago contra entrega' : 'Transferencia bancaria' }}
                                </p>
                            </div>
                            <button wire:click="goToStep(2)" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Editar</button>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="border border-zinc-100 rounded-xl divide-y divide-zinc-100 mb-6">
                        @foreach($items as $item)
                        <div class="flex items-center gap-4 p-4">
                            <img src="{{ optional($item->product)->imagen_principal ?? 'https://via.placeholder.com/60' }}" class="w-14 h-14 rounded-lg object-cover bg-zinc-100" alt="">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 truncate">{{ optional($item->product)->nombre ?? 'Producto' }}</p>
                                <p class="text-xs text-zinc-500">Cantidad: {{ $item->cantidad }}</p>
                            </div>
                            <p class="text-sm font-bold text-zinc-900">${{ number_format($item->cantidad * $item->precio, 2) }}</p>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button wire:click="goToStep(2)" class="px-6 py-4 border border-zinc-300 rounded-xl font-medium text-zinc-700 hover:bg-zinc-50 transition-colors">
                            ← Atrás
                        </button>
                        <button wire:click="confirmOrder" wire:loading.attr="disabled" class="flex-1 bg-indigo-600 text-white py-4 rounded-xl font-semibold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <svg wire:loading class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span wire:loading.remove>Confirmar Pedido</span>
                            <span wire:loading>Procesando...</span>
                        </button>
                    </div>
                </div>
                @endif
            </div>

            <!-- Order Summary Sidebar -->
            <div class="w-full lg:w-96 flex-shrink-0">
                <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-8 lg:sticky lg:top-24">
                    <h3 class="text-lg font-bold text-zinc-900 mb-6 uppercase tracking-wide">Tu Pedido</h3>
                    <div class="space-y-4 text-base">
                        @foreach($items->take(4) as $item)
                        <div class="flex justify-between text-zinc-600">
                            <span class="truncate pr-2">{{ optional($item->product)->nombre ?? 'Producto' }} × {{ $item->cantidad }}</span>
                            <span class="font-medium text-zinc-900 whitespace-nowrap">${{ number_format($item->cantidad * $item->precio, 2) }}</span>
                        </div>
                        @endforeach
                        @if($items->count() > 4)
                            <p class="text-sm text-zinc-400 mt-2">+ {{ $items->count() - 4 }} artículos más</p>
                        @endif
                    </div>
                    <hr class="my-6 border-zinc-100">
                    <div class="space-y-4 text-base">
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Subtotal</span>
                            <span class="font-medium text-zinc-900">${{ number_format($subtotal, 2) }}</span>
                        </div>
                        @if($descuento > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Descuento</span>
                            <span class="font-medium">-${{ number_format($descuento, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Envío</span>
                            <span class="font-medium text-zinc-500">Gratis</span>
                        </div>
                    </div>
                    <hr class="my-6 border-zinc-100">
                    <div class="flex justify-between text-2xl font-bold text-zinc-900">
                        <span>Total</span>
                        <span class="text-indigo-600">${{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
