<?php
use Livewire\Volt\Component;
use App\Models\Category;

new class extends Component {
    public function with(): array
    {
        return [
            'categories' => Category::whereNull('parent_id')
                ->where('status', true)
                ->withCount(['products' => fn($q) => $q->where('status', true)])
                ->orderBy('orden')
                ->take(12)
                ->get(),
        ];
    }
};
?>

<div>
@if($categories->count() > 0)
<section class="py-16 md:py-20 bg-white" id="categories-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-12">
            <span class="inline-block text-indigo-600 font-semibold text-sm tracking-widest uppercase mb-3">Explora</span>
            <h2 class="text-3xl md:text-4xl font-bold text-zinc-900 tracking-tight">Categorías Populares</h2>
            <p class="mt-4 text-lg text-zinc-500 max-w-2xl mx-auto">Navega por nuestras colecciones cuidadosamente curadas para encontrar exactamente lo que necesitas.</p>
        </div>

        <!-- Categories Carousel Wrapper (Alpine + Swiper) -->
        <div x-data="{
            init() {
                new Swiper(this.$refs.container, {
                    slidesPerView: 1.3,
                    spaceBetween: 16,
                    loop: false,
                    autoplay: {
                        delay: 3500,
                        disableOnInteraction: false,
                    },
                    pagination: {
                        el: this.$refs.pagination,
                        clickable: true,
                    },
                    navigation: {
                        nextEl: this.$refs.next,
                        prevEl: this.$refs.prev,
                    },
                    breakpoints: {
                        480: {
                            slidesPerView: 2,
                            spaceBetween: 16,
                        },
                        768: {
                            slidesPerView: 3,
                            spaceBetween: 20,
                        },
                        1024: {
                            slidesPerView: 4,
                            spaceBetween: 24,
                        }
                    }
                });
            }
        }" class="relative px-4 sm:px-12">
            <div x-ref="container" class="swiper categories-swiper overflow-hidden">
                <div class="swiper-wrapper py-6">
                    @foreach($categories as $category)
                        @php
                            $imageUrl = $category->imagen 
                                ? (filter_var($category->imagen, FILTER_VALIDATE_URL) ? $category->imagen : asset('storage/' . $category->imagen))
                                : null;
                        @endphp
                        <div class="swiper-slide h-auto">
                            <a href="{{ route('store.catalog.category', $category->slug) }}" wire:navigate
                               class="group relative flex flex-col items-center p-5 rounded-[32px] bg-white border border-zinc-100 hover:shadow-xl hover:shadow-indigo-100/50 hover:border-indigo-100 transition-all duration-300 h-full">
                                <div class="relative w-full aspect-square mb-5 rounded-2xl bg-zinc-50 overflow-hidden shadow-inner flex items-center justify-center">
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $category->nombre }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                    @else
                                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-purple-50 flex items-center justify-center">
                                            <svg class="w-16 h-16 text-indigo-400/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                        </div>
                                    @endif
                                    
                                    <span class="absolute top-4 right-4 bg-white/95 backdrop-blur px-3 py-1.5 rounded-xl text-[10px] font-bold text-indigo-600 shadow-sm">
                                        {{ $category->products_count }} {{ $category->products_count === 1 ? 'Producto' : 'Productos' }}
                                    </span>
                                </div>
                                <h3 class="text-base md:text-lg font-extrabold text-zinc-800 group-hover:text-indigo-600 transition-colors text-center px-2">{{ $category->nombre }}</h3>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            
            {{-- Indicadores de paginación --}}
            <div x-ref="pagination" class="swiper-pagination !relative !bottom-0 mt-4"></div>
            
            {{-- Flechas de navegación del carrusel --}}
            <button x-ref="prev" class="swiper-button-prev !text-indigo-600 !w-12 !h-12 after:!text-sm rounded-full bg-white shadow-lg border border-zinc-100 hover:bg-zinc-50 transition !-left-2 lg:!-left-5"></button>
            <button x-ref="next" class="swiper-button-next !text-indigo-600 !w-12 !h-12 after:!text-sm rounded-full bg-white shadow-lg border border-zinc-100 hover:bg-zinc-50 transition !-right-2 lg:!-right-5"></button>
        </div>
    </div>
</section>
@endif
</div>
