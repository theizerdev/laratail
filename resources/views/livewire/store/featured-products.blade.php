<?php
use Livewire\Volt\Component;
use App\Models\Product;
use App\Services\CartService;

new class extends Component {
    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) return;
        app(CartService::class)->addItem($product, 1);
        $this->dispatch('cart-updated');
    }

    public function with()
    {
        return [
            'products' => Product::with('category', 'brand')
                ->where('status', true)
                ->where('destacado', true)
                ->latest()
                ->take(12)
                ->get()
        ];
    }
};
?>

<section class="py-16 md:py-24 bg-zinc-50" id="featured-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
            <div class="max-w-2xl">
                <span class="inline-block text-indigo-600 font-semibold text-sm tracking-widest uppercase mb-3">Selección Especial</span>
                <h2 class="text-3xl md:text-4xl font-bold text-zinc-900 tracking-tight">Productos Destacados</h2>
                <p class="mt-4 text-lg text-zinc-500">Los favoritos de nuestros clientes, seleccionados por su calidad y estilo excepcional.</p>
            </div>
            <div class="flex items-center gap-3 mt-4 md:mt-0">
                <!-- Navigation Buttons -->
                <div class="flex items-center gap-2">
                    <button class="featured-prev w-10 h-10 rounded-full border border-zinc-300 flex items-center justify-center text-zinc-500 hover:text-indigo-600 hover:border-indigo-300 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    <button class="featured-next w-10 h-10 rounded-full border border-zinc-300 flex items-center justify-center text-zinc-500 hover:text-indigo-600 hover:border-indigo-300 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
                <a href="{{ route('store.catalog') }}" wire:navigate class="hidden md:inline-flex items-center gap-2 text-indigo-600 font-semibold hover:text-indigo-700 transition-colors group">
                    Ver todo
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </a>
            </div>
        </div>

        @if($products->count() > 0)
        <!-- Swiper Carousel -->
        <div class="swiper featuredSwiper -mx-2">
            <div class="swiper-wrapper !h-auto">
                @foreach($products as $product)
                <div class="swiper-slide !h-auto px-2">
                    <div class="group relative flex flex-col bg-white rounded-2xl border border-zinc-100 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden h-full">
                        <!-- Image Container -->
                        <div class="relative aspect-[4/5] overflow-hidden bg-zinc-100">
                            <a href="{{ route('store.product.detail', $product->slug) }}" wire:navigate>
                                <img src="{{ $product->imagen_principal_url ?? 'https://via.placeholder.com/400x500?text=Producto' }}" alt="{{ $product->nombre }}" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700" loading="lazy" />
                            </a>

                            <!-- Badges -->
                            <div class="absolute top-4 left-4 flex flex-col gap-2">
                                @if($product->nuevo)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-zinc-900 text-white shadow-sm">
                                        Nuevo
                                    </span>
                                @endif
                                @if($product->tiene_descuento)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-500 text-white shadow-sm">
                                        -{{ $product->porcentaje_descuento }}%
                                    </span>
                                @endif
                            </div>

                            <!-- Quick Add Button Overlay -->
                            <div class="absolute inset-x-0 bottom-0 p-4 opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
                                <button wire:click="addToCart({{ $product->id }})" class="w-full bg-white/90 backdrop-blur-sm text-zinc-900 font-semibold py-3 px-4 rounded-xl shadow-lg hover:bg-white hover:text-indigo-600 flex items-center justify-center gap-2 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    Agregar al carrito
                                </button>
                            </div>
                        </div>

                        <!-- Product Details -->
                        <div class="p-5 flex-1 flex flex-col">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-xs text-zinc-400 font-medium uppercase tracking-wider">{{ optional($product->category)->nombre ?? 'General' }}</p>
                                @if($product->brand)
                                    <span class="text-zinc-300">&middot;</span>
                                    <p class="text-xs text-zinc-400">{{ $product->brand->nombre }}</p>
                                @endif
                            </div>
                            <h3 class="text-base font-semibold text-zinc-900 mb-3 line-clamp-2 leading-snug">
                                <a href="{{ route('store.product.detail', $product->slug) }}" wire:navigate>
                                    <span aria-hidden="true" class="absolute inset-0"></span>
                                    {{ $product->nombre }}
                                </a>
                            </h3>
                            <div class="mt-auto flex items-center gap-2">
                                @if($product->tiene_descuento)
                                    <p class="text-xl font-bold text-red-600">${{ number_format($product->precio_oferta, 2) }}</p>
                                    <p class="text-sm text-zinc-400 line-through">${{ number_format($product->precio, 2) }}</p>
                                @else
                                    <p class="text-xl font-bold text-zinc-900">${{ number_format($product->precio, 2) }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Pagination dots -->
        <div class="featured-pagination mt-8 flex justify-center"></div>
        @else
        <div class="py-12 text-center text-zinc-500">
            <p>Aún no hay productos destacados disponibles.</p>
        </div>
        @endif

        <!-- Mobile CTA -->
        <div class="mt-10 text-center md:hidden">
            <a href="{{ route('store.catalog') }}" wire:navigate class="inline-flex items-center justify-center px-8 py-3.5 border border-zinc-300 rounded-full text-base font-medium text-zinc-700 bg-white hover:bg-zinc-50 transition-colors w-full shadow-sm">
                Ver todo el catálogo
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        initFeaturedSwiper();
    });

    // Re-init after Livewire navigation
    document.addEventListener('livewire:navigated', function() {
        initFeaturedSwiper();
    });

    function initFeaturedSwiper() {
        if (document.querySelector('.featuredSwiper')) {
            new Swiper('.featuredSwiper', {
                slidesPerView: 1,
                spaceBetween: 16,
                speed: 600,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: true,
                    pauseOnMouseEnter: true,
                },
                pagination: {
                    el: '.featured-pagination',
                    clickable: true,
                    dynamicBullets: true,
                },
                navigation: {
                    nextEl: '.featured-next',
                    prevEl: '.featured-prev',
                },
                breakpoints: {
                    640: { slidesPerView: 2, spaceBetween: 20 },
                    768: { slidesPerView: 3, spaceBetween: 24 },
                    1024: { slidesPerView: 4, spaceBetween: 24 },
                },
            });
        }
    }
</script>

<style>
    .featured-pagination .swiper-pagination-bullet {
        width: 8px;
        height: 8px;
        background: #d4d4d8;
        opacity: 1;
        transition: all 0.3s;
    }
    .featured-pagination .swiper-pagination-bullet-active {
        width: 24px;
        border-radius: 4px;
        background: #4f46e5;
    }
</style>
