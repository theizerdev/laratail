<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Product;
use App\Services\CartService;

new #[Layout('layouts.app')] class extends Component {
    public Product $product;
    public $quantity = 1;
    public bool $addedToCart = false;

    public function mount(Product $product)
    {
        $this->product = $product->load(['category', 'images', 'variants.attributeValues']);
    }

    public function incrementQuantity()
    {
        $stock = $this->product->stock_total;
        if ($this->quantity < $stock) {
            $this->quantity++;
        }
    }

    public function decrementQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart()
    {
        $cartService = app(CartService::class);
        $cartService->addItem($this->product, $this->quantity);

        // Dispatch event so navbar updates
        $this->dispatch('cart-updated');
        $this->addedToCart = true;

        // Reset flag after 3 seconds
        $this->js('setTimeout(() => { $wire.addedToCart = false }, 3000)');
    }
};
?>

<div class="bg-white">
    <!-- Breadcrumb -->
    <div class="border-b border-zinc-200">
        <nav aria-label="Breadcrumb" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <ol role="list" class="flex items-center space-x-4 py-4">
                <li>
                    <div class="flex items-center">
                        <a href="/" class="text-sm font-medium text-zinc-500 hover:text-zinc-900">Inicio</a>
                    </div>
                </li>
                @if($product->category)
                <li>
                    <div class="flex items-center">
                        <svg class="h-5 w-5 flex-shrink-0 text-zinc-300" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" /></svg>
                        <a href="#" class="ml-4 text-sm font-medium text-zinc-500 hover:text-zinc-900">{{ $product->category->nombre }}</a>
                    </div>
                </li>
                @endif
                <li>
                    <div class="flex items-center">
                        <svg class="h-5 w-5 flex-shrink-0 text-zinc-300" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" /></svg>
                        <a href="#" class="ml-4 text-sm font-medium text-zinc-900" aria-current="page">{{ $product->nombre }}</a>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Product Details -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        @if (session()->has('message') || $addedToCart)
            <div class="mb-8 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative" role="alert">
                <span class="block sm:inline">¡Producto agregado al carrito exitosamente!</span>
            </div>
        @endif

        <div class="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16">
            <!-- Product Gallery -->
            <div class="flex flex-col-reverse">
                <!-- Thumbnail selector -->
                @if($product->images->count() > 0)
                <div class="hidden mt-6 w-full max-w-2xl mx-auto sm:block lg:max-w-none">
                    <div class="grid grid-cols-4 gap-6" aria-orientation="horizontal" role="tablist">
                        <!-- Thumbs would go here, currently using primary image -->
                        <button class="relative h-24 bg-white rounded-md flex items-center justify-center text-sm font-medium uppercase text-zinc-900 cursor-pointer hover:bg-zinc-50 focus:outline-none focus:ring focus:ring-offset-4 focus:ring-opacity-50" role="tab" type="button">
                            <span class="absolute inset-0 rounded-md overflow-hidden">
                                <img src="{{ $product->imagen_principal }}" alt="" class="w-full h-full object-center object-cover">
                            </span>
                            <span class="ring-indigo-500 absolute inset-0 rounded-md ring-2 ring-offset-2 pointer-events-none" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                @endif

                <!-- Main Image -->
                <div class="w-full aspect-[4/5] sm:rounded-2xl overflow-hidden bg-zinc-100">
                    <img src="{{ $product->imagen_principal }}" alt="{{ $product->nombre }}" class="w-full h-full object-center object-cover sm:rounded-2xl">
                </div>
            </div>

            <!-- Product Info -->
            <div class="mt-10 px-4 sm:px-0 sm:mt-16 lg:mt-0">
                <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900">{{ $product->nombre }}</h1>
                <p class="mt-2 text-sm text-zinc-500">SKU: {{ $product->sku }}</p>

                <div class="mt-4 flex items-center gap-4">
                    @if($product->tiene_descuento)
                        <p class="text-3xl font-bold text-red-600">${{ number_format($product->precio_oferta, 2) }}</p>
                        <p class="text-xl text-zinc-400 line-through">${{ number_format($product->precio, 2) }}</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Ahorras un {{ $product->porcentaje_descuento }}%
                        </span>
                    @else
                        <p class="text-3xl font-bold text-zinc-900">${{ number_format($product->precio, 2) }}</p>
                    @endif
                </div>

                <div class="mt-6">
                    <h3 class="sr-only">Descripción</h3>
                    <div class="text-base text-zinc-700 space-y-6">
                        <p>{{ $product->descripcion_corta }}</p>
                        <p class="text-zinc-500">{{ $product->descripcion }}</p>
                    </div>
                </div>

                <form class="mt-8" wire:submit="addToCart">
                    <!-- Cantidad -->
                    <div class="mt-8">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm text-zinc-900 font-medium">Cantidad</h3>
                            @if($product->stock <= 5)
                                <p class="text-sm text-red-500 font-medium">¡Solo quedan {{ $product->stock }} disponibles!</p>
                            @else
                                <p class="text-sm text-green-600 font-medium">Stock disponible</p>
                            @endif
                        </div>
                        <div class="mt-4 flex items-center border border-zinc-300 rounded-lg w-fit">
                            <button type="button" wire:click="decrementQuantity" class="px-4 py-2 text-zinc-500 hover:text-zinc-900 transition-colors">-</button>
                            <input type="number" wire:model.live="quantity" class="w-16 text-center border-0 focus:ring-0 text-zinc-900 font-medium" readonly>
                            <button type="button" wire:click="incrementQuantity" class="px-4 py-2 text-zinc-500 hover:text-zinc-900 transition-colors">+</button>
                        </div>
                    </div>

                    <div class="mt-10 flex sm:flex-col1">
                        <button type="submit" class="max-w-xs flex-1 bg-indigo-600 border border-transparent rounded-xl py-4 px-8 flex items-center justify-center text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-zinc-50 focus:ring-indigo-500 sm:w-full transition-colors shadow-lg shadow-indigo-200">
                            Agregar al Carrito
                        </button>
                        
                        <button type="button" class="ml-4 py-3 px-3 rounded-xl flex items-center justify-center text-zinc-400 hover:bg-zinc-100 hover:text-zinc-500 transition-colors">
                            <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                            <span class="sr-only">Añadir a favoritos</span>
                        </button>
                    </div>
                </form>

                <!-- Policies -->
                <section aria-labelledby="policies-heading" class="mt-10">
                    <h2 id="policies-heading" class="sr-only">Nuestras Políticas</h2>
                    <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                        <div class="bg-zinc-50 border border-zinc-200 rounded-xl p-6 text-center">
                            <dt>
                                <svg class="mx-auto h-6 w-6 flex-shrink-0 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span class="mt-4 text-sm font-medium text-zinc-900">Envíos Nacionales</span>
                            </dt>
                            <dd class="mt-1 text-sm text-zinc-500">Envío gratis en compras mayores a $100</dd>
                        </div>
                        <div class="bg-zinc-50 border border-zinc-200 rounded-xl p-6 text-center">
                            <dt>
                                <svg class="mx-auto h-6 w-6 flex-shrink-0 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                <span class="mt-4 text-sm font-medium text-zinc-900">Devoluciones Fáciles</span>
                            </dt>
                            <dd class="mt-1 text-sm text-zinc-500">Tienes 30 días para cualquier cambio</dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>
    </div>
</div>
