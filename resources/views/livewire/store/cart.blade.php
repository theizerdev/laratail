<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use App\Services\CartService;
use App\Models\CartItem;
use App\Models\Coupon;

new #[Layout('layouts.app')] #[Title('Carrito - Abastos Los Trinis')] class extends Component {
    public string $couponCode = '';
    public ?string $couponError = null;
    public ?string $couponSuccess = null;

    public function with(): array
    {
        $cart = app(CartService::class)->getOrCreate();
        $items = $cart->items->load('product', 'variant');
        
        $usdSubtotal = 0.0;
        $vesSubtotal = 0.0;
        foreach ($items as $item) {
            $usdSubtotal += $item->cantidad * (float)$item->precio;
            $vesSubtotal += $item->cantidad * (float)format_cart_item_price($item, true);
        }
        
        $usdDescuento = $cart->coupon ? $cart->coupon->calcularDescuento($usdSubtotal) : 0.0;
        $vesDescuento = $cart->coupon ? $cart->coupon->calcularDescuento($vesSubtotal) : 0.0;
        
        $usdTotal = max(0.0, $usdSubtotal - $usdDescuento);
        $vesTotal = max(0.0, $vesSubtotal - $vesDescuento);
        
        $totalWeight = $items->sum(fn($item) => ($item->product?->peso ?? 1) * $item->cantidad);
        
        // Shipping cost
        $shippingEstimate = session('shipping_estimate');
        $usdShippingCost = 0.0;
        $vesShippingCost = 0.0;
        if ($shippingEstimate && $usdSubtotal < ($shippingEstimate['gratis_desde'] ?? 999999)) {
            $usdShippingCost = (float) $shippingEstimate['costo'];
            
            $rate = \App\Models\ExchangeRate::getLatestRate('USD') ?? 1.0;
            $vesShippingCost = $usdShippingCost * $rate;
        }
        
        $usdTotalConEnvio = $usdTotal + $usdShippingCost;
        $vesTotalConEnvio = $vesTotal + $vesShippingCost;
        
        return [
            'cart' => $cart,
            'items' => $items,
            
            'usdSubtotal' => $usdSubtotal,
            'vesSubtotal' => $vesSubtotal,
            'usdDescuento' => $usdDescuento,
            'vesDescuento' => $vesDescuento,
            'usdShippingCost' => $usdShippingCost,
            'vesShippingCost' => $vesShippingCost,
            'usdTotalConEnvio' => $usdTotalConEnvio,
            'vesTotalConEnvio' => $vesTotalConEnvio,
            
            'totalWeight' => $totalWeight,
            'shippingEstimate' => $shippingEstimate,
        ];
    }

    public function updateQty(int $itemId, int $qty): void
    {
        $item = CartItem::find($itemId);
        if (!$item) return;
        if ($qty <= 0) {
            $this->removeItem($itemId);
            return;
        }
        app(CartService::class)->updateItemQty($item, $qty);
        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: 'Cantidad de producto actualizada.', type: 'success');
    }

    public function removeItem(int $itemId): void
    {
        $item = CartItem::find($itemId);
        if (!$item) return;
        app(CartService::class)->removeItem($item);
        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: 'Producto eliminado del carrito.', type: 'info');
    }

    public function applyCoupon(): void
    {
        $this->couponError = null;
        $this->couponSuccess = null;

        if (empty(trim($this->couponCode))) {
            $this->couponError = 'Ingresa un código de cupón.';
            return;
        }

        $success = app(CartService::class)->applyCoupon($this->couponCode);
        if ($success) {
            $this->couponSuccess = '¡Cupón aplicado correctamente!';
            $this->dispatch('notify', message: '¡Cupón aplicado correctamente!', type: 'success');
            $this->couponCode = '';
        } else {
            $this->couponError = 'Cupón inválido, expirado o no cumple los requisitos mínimos.';
            $this->dispatch('notify', message: 'El cupón no es válido o no cumple los requisitos.', type: 'error');
        }
    }

    public function removeCoupon(): void
    {
        app(CartService::class)->removeCoupon();
        $this->couponSuccess = null;
        $this->dispatch('notify', message: 'Cupón removido del carrito.', type: 'info');
    }

    public function proceedToCheckout(): void
    {
        $cart = app(CartService::class)->getOrCreate();
        if ($cart->items->isEmpty()) return;
        $this->redirect('/checkout', navigate: true);
    }
};
?>

<div>
    <!-- Breadcrumb -->
    <div class="bg-white border-b border-zinc-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center gap-2 text-sm">
                <a href="/" wire:navigate class="text-zinc-500 hover:text-zinc-900 transition-colors">Inicio</a>
                <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="text-zinc-900 font-medium">Carrito de Compras</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
        <h1 class="text-3xl font-bold text-zinc-900 mb-8">Carrito de Compras</h1>

        @if($items->isEmpty())
            <!-- Empty Cart -->
            <div class="text-center py-16">
                <svg class="w-24 h-24 mx-auto text-zinc-200 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <h2 class="text-2xl font-bold text-zinc-700 mb-2">Tu carrito está vacío</h2>
                <p class="text-zinc-500 mb-8">Parece que aún no has añadido productos. ¡Explora nuestro catálogo!</p>
                <a href="/catalogo" wire:navigate class="inline-flex items-center px-8 py-4 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">
                    Explorar Catálogo
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </a>
            </div>
        @else
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Cart Items -->
                <div class="flex-1">
                    <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm overflow-hidden">
                        <div class="divide-y divide-zinc-100">
                            @foreach($items as $item)
                            <div class="p-4 sm:p-6 flex items-start gap-4 sm:gap-6">
                                <!-- Image -->
                                <a href="{{ route('store.product.detail', optional($item->product)->slug ?? '#') }}" wire:navigate class="flex-shrink-0">
                                    <img src="{{ optional($item->product)->imagen_principal_url ?? 'https://placehold.co/120' }}" alt="{{ optional($item->product)->nombre ?? 'Producto' }}" class="w-20 h-24 sm:w-24 sm:h-28 object-cover rounded-xl bg-zinc-100">
                                </a>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-zinc-900 text-sm sm:text-base line-clamp-2">
                                        <a href="{{ route('store.product.detail', optional($item->product)->slug ?? '#') }}" wire:navigate class="hover:text-indigo-600 transition-colors">
                                            {{ optional($item->product)->nombre ?? 'Producto eliminado' }}
                                        </a>
                                    </h3>
                                    @if($item->variant)
                                        <p class="text-xs text-zinc-500 mt-1">{{ $item->variant->nombre_completo ?? 'Variante' }}</p>
                                    @endif
                                    <p class="text-sm text-zinc-500 mt-1">{{ format_cart_item_price($item) }} c/u</p>

                                    <!-- Quantity Stepper -->
                                    <div class="mt-3 flex items-center gap-3">
                                        <div class="flex items-center border border-zinc-200 rounded-lg">
                                            <button wire:click="updateQty({{ $item->id }}, {{ $item->cantidad - 1 }})" class="px-3 py-1.5 text-zinc-500 hover:text-zinc-900 transition-colors text-sm">−</button>
                                            <span class="px-3 py-1.5 text-sm font-medium text-zinc-900 border-x border-zinc-200 min-w-[2.5rem] text-center">{{ $item->cantidad }}</span>
                                            <button wire:click="updateQty({{ $item->id }}, {{ $item->cantidad + 1 }})" class="px-3 py-1.5 text-zinc-500 hover:text-zinc-900 transition-colors text-sm">+</button>
                                        </div>
                                        <button wire:click="removeItem({{ $item->id }})" class="text-xs text-red-500 hover:text-red-700 transition-colors font-medium flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Eliminar
                                        </button>
                                    </div>
                                </div>

                                <!-- Subtotal -->
                                <p class="text-right font-bold text-zinc-900 whitespace-nowrap">
                                    {{ format_display_price($item->cantidad * $item->precio, $item->cantidad * format_cart_item_price($item, true)) }}
                                </p>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Continue Shopping -->
                    <a href="/catalogo" wire:navigate class="inline-flex items-center mt-6 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path></svg>
                        Continuar comprando
                    </a>
                </div>

                <!-- Order Summary -->
                <div class="w-full lg:w-96 flex-shrink-0">
                    <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 lg:sticky lg:top-24">
                        <h2 class="text-lg font-bold text-zinc-900 mb-4">Resumen del Pedido</h2>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Subtotal ({{ $items->sum('cantidad') }} artículos)</span>
                                <span class="text-zinc-900 font-medium">{{ format_display_price($usdSubtotal, $vesSubtotal) }}</span>
                            </div>

                            @if($usdDescuento > 0)
                            <div class="flex justify-between text-green-600">
                                <span>Descuento cupón</span>
                                <span class="font-medium">-{{ format_display_price($usdDescuento, $vesDescuento) }}</span>
                            </div>
                            @endif

                            <div class="flex justify-between">
                                <span class="text-zinc-500">Envío</span>
                                @if($shippingEstimate)
                                    @if($shippingCost == 0)
                                        <span class="text-green-600 font-medium text-xs">¡GRATIS!</span>
                                    @else
                                        <span class="text-zinc-900 font-medium">{{ format_display_price($usdShippingCost, $vesShippingCost) }}</span>
                                    @endif
                                @else
                                    <span class="text-zinc-500 text-xs">Se calcula al confirmar</span>
                                @endif
                            </div>

                            <hr class="border-zinc-100">

                            <div class="flex justify-between text-lg font-bold text-zinc-900 pt-2">
                                <span>Total</span>
                                <span>{{ format_display_price($usdTotalConEnvio, $vesTotalConEnvio) }}</span>
                            </div>
                        </div>

                        <!-- Coupon -->
                        <div class="mt-6">
                            @if($cart->coupon)
                                <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-4 py-2.5">
                                    <div>
                                        <p class="text-xs text-green-600 font-medium">Cupón aplicado</p>
                                        <p class="text-sm font-bold text-green-800">{{ $cart->coupon->codigo }}</p>
                                    </div>
                                    <button wire:click="removeCoupon" class="text-green-600 hover:text-green-800 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            @else
                                <div class="flex gap-2">
                                    <input type="text" wire:model="couponCode" wire:keydown.enter="applyCoupon" placeholder="Código de cupón" class="flex-1 rounded-lg border-zinc-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <button wire:click="applyCoupon" class="px-4 py-2 bg-zinc-900 text-white rounded-lg text-sm font-medium hover:bg-black transition-colors">Aplicar</button>
                                </div>
                                @if($couponError)
                                    <p class="mt-1.5 text-xs text-red-600">{{ $couponError }}</p>
                                @endif
                                @if($couponSuccess)
                                    <p class="mt-1.5 text-xs text-green-600">{{ $couponSuccess }}</p>
                                @endif
                            @endif
                        </div>

                        <!-- Shipping Estimator -->
                        @livewire('store.partials.shipping-estimator', ['totalWeight' => $totalWeight, 'subtotal' => $usdSubtotal])

                        <!-- Checkout Button -->
                        <button wire:click="proceedToCheckout" wire:loading.attr="disabled" class="mt-6 w-full bg-indigo-600 text-white py-4 rounded-xl font-semibold hover:bg-indigo-700 disabled:opacity-75 disabled:cursor-not-allowed transition-all shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                            <svg wire:loading wire:target="proceedToCheckout" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="proceedToCheckout">Proceder al Pago</span>
                            <span wire:loading wire:target="proceedToCheckout">Cargando...</span>
                            <svg wire:loading.remove wire:target="proceedToCheckout" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </button>

                        <!-- Security badges -->
                        <div class="mt-4 flex items-center justify-center gap-2 text-xs text-zinc-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Compra segura y protegida
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
