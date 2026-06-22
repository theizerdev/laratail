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

    public function with(): array
    {
        return [
            'onSaleProducts' => Product::with('category', 'brand')
                ->where('status', true)
                ->whereNotNull('precio_oferta')
                ->whereColumn('precio_oferta', '<', 'precio')
                ->inRandomOrder()
                ->take(4)
                ->get(),
        ];
    }
};
?>

<div>
@if($onSaleProducts->count() > 0)
<section class="relative overflow-hidden" id="sale-section">
    <!-- Background with gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-red-600 via-rose-600 to-pink-700"></div>
    <!-- Decorative shapes -->
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
    <!-- Diagonal pattern -->
    <div class="absolute inset-0 opacity-5" style="background-image: repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(255,255,255,.1) 35px, rgba(255,255,255,.1) 70px);"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">
        <div class="flex flex-col lg:flex-row items-center gap-12">
            <!-- Left: Copy -->
            <div class="lg:w-5/12 text-center lg:text-left">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/15 text-white text-sm font-semibold mb-6 backdrop-blur-sm border border-white/20">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"></path></svg>
                    ¡Ofertas Exclusivas!
                </span>
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-tight mb-6">
                    Hasta <span class="text-yellow-300">{{ $onSaleProducts->max('porcentaje_descuento') }}%</span> de descuento
                </h2>
                <p class="text-lg text-red-100 mb-8 max-w-lg mx-auto lg:mx-0">Aprovecha nuestras ofertas por tiempo limitado en productos seleccionados. ¡No te las pierdas!</p>
                <a href="{{ route('store.catalog') }}?oferta=1" wire:navigate class="inline-flex items-center gap-2 px-8 py-4 bg-white text-red-600 font-bold rounded-full shadow-xl shadow-red-900/30 hover:shadow-red-900/50 hover:scale-105 transition-all duration-300">
                    Ver ofertas
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </a>
            </div>

            <!-- Right: Sale Products preview -->
            <div class="lg:w-7/12 grid grid-cols-2 gap-4">
                @foreach($onSaleProducts as $saleProduct)
                <a href="{{ route('store.product.detail', $saleProduct->slug) }}" wire:navigate
                   class="group relative bg-white/10 backdrop-blur-sm rounded-2xl border border-white/15 p-4 hover:bg-white/20 transition-all duration-300 hover:-translate-y-1">
                    <div class="aspect-square rounded-xl overflow-hidden bg-white/10 mb-3">
                        <img src="{{ $saleProduct->imagen_principal_url ?? 'https://via.placeholder.com/300x300?text=Oferta' }}" alt="{{ $saleProduct->nombre }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
                    </div>
                    <h4 class="text-white font-semibold text-sm line-clamp-1 mb-1">{{ $saleProduct->nombre }}</h4>
                    <div class="flex items-center gap-2">
                        <span class="text-yellow-300 font-bold">${{ number_format($saleProduct->precio_oferta, 2) }}</span>
                        <span class="text-red-200 text-sm line-through">${{ number_format($saleProduct->precio, 2) }}</span>
                    </div>
                    <span class="absolute top-3 right-3 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-yellow-400 text-yellow-900">
                        -{{ $saleProduct->porcentaje_descuento }}%
                    </span>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
</div>
