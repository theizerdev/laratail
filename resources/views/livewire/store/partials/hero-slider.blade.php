<?php
use Livewire\Volt\Component;

new class extends Component {
    public $slides = [
        [
            'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=2070&auto=format&fit=crop',
            'title' => 'Nueva Colección de Verano',
            'subtitle' => 'Descubre estilos frescos y modernos para esta temporada.',
            'cta' => 'Comprar Ahora',
            'link' => '/catalogo'
        ],
        [
            'image' => 'https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?q=80&w=2070&auto=format&fit=crop',
            'title' => 'Diseño Minimalista',
            'subtitle' => 'Elegancia en cada detalle. Encuentra tu look perfecto.',
            'cta' => 'Ver Novedades',
            'link' => '/catalogo?nuevo=1'
        ],
        [
            'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=2070&auto=format&fit=crop',
            'title' => 'Accesorios Premium',
            'subtitle' => 'El toque final que tu outfit necesita.',
            'cta' => 'Explorar',
            'link' => '/catalogo'
        ]
    ];
};
?>

<div class="relative w-full h-[70vh] min-h-[500px]">
    <!-- Swiper -->
    <div class="swiper mySwiper h-full w-full">
        <div class="swiper-wrapper">
            @foreach($slides as $slide)
            <div class="swiper-slide relative">
                <!-- Imagen de fondo -->
                <div class="absolute inset-0 w-full h-full">
                    <img src="{{ $slide['image'] }}" alt="{{ $slide['title'] }}" class="w-full h-full object-cover" />
                    <!-- Overlay oscuro para mejorar legibilidad del texto -->
                    <div class="absolute inset-0 bg-black/40"></div>
                </div>
                
                <!-- Contenido -->
                <div class="relative h-full flex items-center justify-center text-center px-4">
                    <div class="max-w-3xl space-y-6">
                        <h2 class="text-4xl md:text-6xl font-bold text-white tracking-tight leading-tight opacity-0 animate-fade-in-up">
                            {{ $slide['title'] }}
                        </h2>
                        <p class="text-lg md:text-2xl text-gray-200 font-light opacity-0 animate-fade-in-up animation-delay-200">
                            {{ $slide['subtitle'] }}
                        </p>
                        <div class="pt-4 opacity-0 animate-fade-in-up animation-delay-400">
                            <a href="{{ $slide['link'] }}" class="inline-block bg-white text-zinc-900 px-8 py-4 text-lg font-semibold rounded-full hover:bg-zinc-100 transition-transform hover:scale-105 active:scale-95 shadow-lg">
                                {{ $slide['cta'] }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Controles de navegación Swiper -->
        <div class="swiper-button-next text-white after:!text-2xl hidden md:flex"></div>
        <div class="swiper-button-prev text-white after:!text-2xl hidden md:flex"></div>
        <div class="swiper-pagination !bottom-6"></div>
    </div>

    <!-- Script de inicialización de Swiper -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initHeroSwiper();
        });

        document.addEventListener('livewire:navigated', function() {
            initHeroSwiper();
        });

        function initHeroSwiper() {
            if (document.querySelector('.mySwiper')) {
                new Swiper(".mySwiper", {
                    loop: true,
                    effect: "fade",
                    speed: 1000,
                    autoplay: {
                        delay: 5000,
                        disableOnInteraction: false,
                    },
                    pagination: {
                        el: ".swiper-pagination",
                        clickable: true,
                    },
                    navigation: {
                        nextEl: ".swiper-button-next",
                        prevEl: ".swiper-button-prev",
                    },
                });
            }
        }
    </script>
    
    <style>
        /* Animaciones para el texto del slider */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .swiper-slide-active .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        .animation-delay-200 {
            animation-delay: 0.2s !important;
        }
        
        .animation-delay-400 {
            animation-delay: 0.4s !important;
        }

        .swiper-pagination-bullet {
            background-color: white !important;
            opacity: 0.5;
        }
        .swiper-pagination-bullet-active {
            opacity: 1;
        }
    </style>
</div>
