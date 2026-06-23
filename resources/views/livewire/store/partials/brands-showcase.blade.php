<?php
use Livewire\Volt\Component;
use App\Models\Brand;

new class extends Component {
    public function with(): array
    {
        return [
            'brands' => Brand::where('status', true)
                ->whereHas('products', fn($q) => $q->where('status', true))
                ->withCount(['products' => fn($q) => $q->where('status', true)])
                ->orderByDesc('products_count')
                ->take(16)
                ->get(),
        ];
    }
};
?>

<div>
@if($brands->count() > 0)
<section class="py-16 md:py-20 bg-zinc-50 border-t border-zinc-100 overflow-hidden" id="brands-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-12">
            <span class="inline-block text-zinc-500 font-semibold text-sm tracking-widest uppercase mb-3">Marcas de Confianza</span>
            <h2 class="text-3xl md:text-4xl font-bold text-zinc-900 tracking-tight">Nuestras Marcas</h2>
            <p class="mt-4 text-lg text-zinc-500 max-w-2xl mx-auto">Trabajamos con las mejores marcas para garantizar la calidad que mereces.</p>
        </div>

        <!-- Brands Swiper (Alpine + Swiper) -->
        <div x-data="{
            init() {
                new Swiper(this.$refs.container, {
                    slidesPerView: 2,
                    spaceBetween: 16,
                    loop: false,
                    autoplay: {
                        delay: 4000,
                        disableOnInteraction: false,
                        pauseOnMouseEnter: true,
                    },
                    navigation: {
                        nextEl: this.$refs.next,
                        prevEl: this.$refs.prev,
                    },
                    breakpoints: {
                        640: {
                            slidesPerView: 3,
                            spaceBetween: 16,
                        },
                        768: {
                            slidesPerView: 4,
                            spaceBetween: 20,
                        },
                        1024: {
                            slidesPerView: 5,
                            spaceBetween: 24,
                        }
                    }
                });
            }
        }" class="relative px-4 sm:px-6 max-w-5xl mx-auto">
            <div x-ref="container" class="swiper brands-swiper overflow-hidden">
                <div class="swiper-wrapper py-4">
                    @foreach($brands as $brand)
                    @php
                        $logoUrl = $brand->logo 
                            ? (filter_var($brand->logo, FILTER_VALIDATE_URL) ? $brand->logo : asset('storage/' . $brand->logo))
                            : null;
                    @endphp
                    <div class="swiper-slide h-auto">
                        <div class="group flex flex-col items-center justify-center p-6 md:p-8 bg-white rounded-2xl border border-zinc-100 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-500/5 transition-all duration-300 cursor-default h-full">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="{{ $brand->nombre }}" class="h-10 md:h-12 object-contain grayscale group-hover:grayscale-0 opacity-60 group-hover:opacity-100 transition-all duration-300" />
                            @else
                                <span class="text-lg md:text-xl font-bold text-zinc-300 group-hover:text-indigo-600 transition-colors duration-300 tracking-tight">{{ $brand->nombre }}</span>
                            @endif
                            <span class="mt-2 text-xs text-zinc-400 group-hover:text-zinc-500 transition-colors">{{ $brand->products_count }} {{ $brand->products_count === 1 ? 'producto' : 'productos' }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Carousel Navigation Arrows -->
            <button x-ref="prev" class="swiper-button-prev !text-zinc-400 hover:!text-indigo-600 !w-9 !h-9 after:!text-xs rounded-full bg-white shadow border border-zinc-100 hover:bg-zinc-50 transition !-left-2 lg:!-left-5"></button>
            <button x-ref="next" class="swiper-button-next !text-zinc-400 hover:!text-indigo-600 !w-9 !h-9 after:!text-xs rounded-full bg-white shadow border border-zinc-100 hover:bg-zinc-50 transition !-right-2 lg:!-right-5"></button>
        </div>
    </div>
</section>
@endif
</div>
