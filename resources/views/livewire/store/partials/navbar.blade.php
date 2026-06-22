<?php
use Livewire\Volt\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public bool $showSearch = false;

    #[Computed]
    public function cartData()
    {
        $cartService = app(CartService::class);
        $cart = $cartService->getCart();
        
        return [
            'count' => $cartService->getCartCount(),
            'items' => $cart ? $cart->items : [],
            'total' => $cart ? $cart->total : 0,
        ];
    }

    #[On('cart-updated')]
    public function refreshCart(): void
    {
        unset($this->cartData); // Clears computed property cache to trigger re-calculation
    }
};
?>

<nav x-data="{ mobileOpen: false }" class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-zinc-100 shadow-sm transition-all">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="/" wire:navigate class="text-2xl font-bold text-zinc-900 tracking-tight flex items-center gap-2">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Laratail Store
                </a>
            </div>

            <!-- Main Menu (Desktop) -->
            <div class="hidden md:flex space-x-8 items-center">
                <a href="/catalogo" wire:navigate class="text-zinc-600 hover:text-indigo-600 transition-colors font-medium">Catálogo</a>
                <a href="/catalogo?nuevo=1" wire:navigate class="text-zinc-600 hover:text-indigo-600 transition-colors font-medium">Novedades</a>
                <a href="/catalogo?oferta=1" wire:navigate class="text-zinc-600 hover:text-indigo-600 transition-colors font-medium">Ofertas</a>
                <a href="/comparar" wire:navigate class="text-zinc-600 hover:text-indigo-600 transition-colors font-medium flex items-center gap-1">
                    Comparar
                    @php $cmpCount = count(session('compare_products', [])); @endphp
                    @if($cmpCount > 0)
                        <span class="bg-indigo-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">{{ $cmpCount }}</span>
                    @endif
                </a>
            </div>

            <!-- Right side (Search, Account, Cart, Hamburger) -->
            <div class="flex items-center space-x-5">
                <!-- Mobile Hamburger -->
                <button @click="mobileOpen = !mobileOpen" class="md:hidden text-zinc-500 hover:text-zinc-900 transition-colors" type="button">
                    <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <!-- Search -->
                <button type="button" class="text-zinc-400 hover:text-zinc-600 transition-colors" onclick="window.dispatchEvent(new CustomEvent('open-search'))">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>

                <!-- Dark Mode Toggle -->
                <button type="button" @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="text-zinc-400 hover:text-zinc-600 transition-colors" title="Cambiar tema">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="darkMode" x-cloak class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>

                <!-- Account -->
                <div x-data="{ open: false }" class="relative hidden sm:block">
                    <button @click="open = !open" class="text-zinc-400 hover:text-zinc-600 transition-colors flex items-center gap-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-zinc-100 py-2 z-50">
                        @auth
                            <a href="/mi-cuenta" wire:navigate class="block px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 transition-colors">
                                <span class="font-medium">Mi Cuenta</span>
                            </a>
                            <a href="/mi-cuenta/pedidos" wire:navigate class="block px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 transition-colors">Mis Pedidos</a>
                            <a href="/favoritos" wire:navigate class="block px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 transition-colors">Mis Favoritos</a>
                            <a href="/mi-cuenta/direcciones" wire:navigate class="block px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 transition-colors">Mis Direcciones</a>
                            <hr class="my-1 border-zinc-100">
                            <form method="POST" action="/logout" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">Cerrar Sesión</button>
                            </form>
                        @else
                            <a href="/acceso" wire:navigate class="block px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 transition-colors">Iniciar Sesión</a>
                            <a href="/registro" wire:navigate class="block px-4 py-2.5 text-sm text-indigo-600 hover:bg-indigo-50 font-medium transition-colors">Registrarse</a>
                        @endauth
                    </div>
                </div>

                <!-- Cart -->
                <div x-data="{ open: false }" class="relative" @click.away="open = false">
                    <button @click="open = !open" class="text-zinc-400 hover:text-indigo-600 transition-colors relative flex items-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        @if($this->cartData['count'] > 0)
                            <span class="absolute -top-2 -right-2 bg-indigo-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center animate-pulse">
                                {{ $this->cartData['count'] }}
                            </span>
                        @endif
                    </button>
                    
                    <div x-show="open" x-transition
                         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-zinc-100 py-4 z-[60] overflow-hidden hidden"
                         :class="{ 'hidden': !open, 'block': open }">
                        <div class="px-4 pb-3 border-b border-zinc-100 flex justify-between items-center">
                            <h3 class="font-bold text-zinc-900">Mi Carrito</h3>
                            <span class="text-sm text-zinc-500">{{ $this->cartData['count'] }} items</span>
                        </div>
                        <div class="max-h-72 overflow-y-auto p-4 space-y-4">
                            @forelse($this->cartData['items'] as $item)
                            <div class="flex items-center gap-3">
                                <img src="{{ $item->product->imagen_principal_url ?? 'https://via.placeholder.com/100' }}" alt="{{ $item->product->nombre }}" class="w-12 h-12 rounded-lg object-cover border border-zinc-100 flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-zinc-900 truncate">{{ $item->product->nombre }}</h4>
                                    <p class="text-xs text-zinc-500">{{ $item->cantidad }} x ${{ number_format($item->precio, 2) }}</p>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-6">
                                <svg class="w-12 h-12 text-zinc-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                <p class="text-sm text-zinc-500">Tu carrito está vacío.</p>
                            </div>
                            @endforelse
                        </div>
                        @if($this->cartData['count'] > 0)
                        <div class="px-4 pt-3 border-t border-zinc-100 bg-zinc-50/50">
                            <div class="flex justify-between font-bold text-zinc-900 mb-4">
                                <span>Total:</span>
                                <span>${{ number_format($this->cartData['total'], 2) }}</span>
                            </div>
                            <flux:button href="/carrito" wire:navigate class="w-full text-center" variant="primary">Ir al carrito</flux:button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div x-show="mobileOpen" x-collapse x-cloak class="md:hidden border-t border-zinc-100 bg-white">
        <div class="px-4 py-3 flex flex-col gap-2">
            <a href="/catalogo" wire:navigate class="text-zinc-600 hover:text-indigo-600 py-2 font-medium">Catálogo</a>
            <a href="/catalogo?nuevo=1" wire:navigate class="text-zinc-600 hover:text-indigo-600 py-2 font-medium">Novedades</a>
            <a href="/catalogo?oferta=1" wire:navigate class="text-zinc-600 hover:text-indigo-600 py-2 font-medium">Ofertas</a>
            <a href="/comparar" wire:navigate class="text-zinc-600 hover:text-indigo-600 py-2 font-medium">Comparar
                @php $cmpCount = count(session('compare_products', [])); @endphp
                @if($cmpCount > 0) ({{ $cmpCount }}) @endif
            </a>
            @guest
                <hr class="border-zinc-100">
                <a href="/acceso" wire:navigate class="text-zinc-700 py-2 font-medium">Iniciar Sesión</a>
                <a href="/registro" wire:navigate class="text-indigo-600 py-2 font-medium">Registrarse</a>
            @endguest
            @auth
                <hr class="border-zinc-100">
                <a href="/mi-cuenta" wire:navigate class="text-zinc-600 py-2 font-medium">Mi Cuenta</a>
                <a href="/mi-cuenta/pedidos" wire:navigate class="text-zinc-600 py-2 font-medium">Mis Pedidos</a>
                <a href="/favoritos" wire:navigate class="text-zinc-600 py-2 font-medium">Mis Favoritos</a>
            @endauth
        </div>
    </div>
</nav>
