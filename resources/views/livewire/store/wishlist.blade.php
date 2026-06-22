<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use App\Models\Customer;
use App\Models\Wishlist;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] #[Title('Mis Favoritos - Laratail Store')] class extends Component {
    public function with(): array
    {
        $customer = Auth::check() ? Customer::where('user_id', Auth::id())->first() : null;

        $items = $customer
            ? Wishlist::with(['product.category', 'product.brand'])
                ->where('customer_id', $customer->id)
                ->latest()
                ->get()
            : collect();

        return [
            'items' => $items,
            'shareUrl' => $customer ? url('/lista-regalos/' . $customer->id) : null,
        ];
    }

    public function removeItem(int $wishlistId): void
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        if (!$customer) return;

        Wishlist::where('id', $wishlistId)
            ->where('customer_id', $customer->id)
            ->delete();
    }

    public function addToCart(int $productId): void
    {
        $product = \App\Models\Product::find($productId);
        if (!$product) return;
        app(CartService::class)->addItem($product, 1);
        $this->dispatch('cart-updated');
    }
};
?>

<div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900">Mis Favoritos</h1>
                <p class="mt-1 text-sm text-zinc-500">{{ $items->count() }} {{ $items->count() === 1 ? 'producto guardado' : 'productos guardados' }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if($items->count() > 0)
                <button x-data onclick="navigator.clipboard.writeText('{{ $shareUrl }}'); this.innerText = '¡Copiado!'; setTimeout(() => this.innerText = 'Compartir', 2000)" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    Compartir
                </button>
                @endif
                <a href="/catalogo" wire:navigate class="hidden sm:inline-flex items-center gap-2 text-indigo-600 font-semibold hover:text-indigo-700 transition-colors text-sm">
                    Seguir comprando
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>

        @if($items->count() > 0)
            <div class="space-y-4">
                @foreach($items as $item)
                @php $product = $item->product; @endphp
                <div class="flex items-center gap-4 sm:gap-6 bg-white rounded-2xl border border-zinc-100 shadow-sm p-4 sm:p-5 hover:shadow-md transition-shadow">
                    <!-- Image -->
                    <a href="{{ route('store.product.detail', $product->slug) }}" wire:navigate class="flex-shrink-0">
                        <img src="{{ $product->imagen_principal_url ?? 'https://via.placeholder.com/120' }}" alt="{{ $product->nombre }}" class="w-20 h-24 sm:w-24 sm:h-28 object-cover rounded-xl bg-zinc-100">
                    </a>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-zinc-400 uppercase tracking-wider mb-1">
                            {{ optional($product->category)->nombre ?? 'General' }}
                            @if($product->brand) · {{ $product->brand->nombre }} @endif
                        </p>
                        <h3 class="font-semibold text-zinc-900 text-sm sm:text-base line-clamp-2 mb-2">
                            <a href="{{ route('store.product.detail', $product->slug) }}" wire:navigate class="hover:text-indigo-600 transition-colors">
                                {{ $product->nombre }}
                            </a>
                        </h3>
                        <div class="flex items-center gap-2">
                            @if($product->tiene_descuento)
                                <p class="text-lg font-bold text-red-600">${{ number_format($product->precio_oferta, 2) }}</p>
                                <p class="text-sm text-zinc-400 line-through">${{ number_format($product->precio, 2) }}</p>
                            @else
                                <p class="text-lg font-bold text-zinc-900">${{ number_format($product->precio, 2) }}</p>
                            @endif
                        </div>
                        @if($product->stock <= 0)
                            <span class="inline-block mt-1 text-xs font-medium text-red-500">Agotado</span>
                        @elseif($product->stock <= 5)
                            <span class="inline-block mt-1 text-xs font-medium text-orange-500">¡Solo {{ $product->stock }} disponibles!</span>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-2 flex-shrink-0">
                        @if($product->stock > 0)
                            <button
                                wire:click="addToCart({{ $product->id }})"
                                class="bg-indigo-600 text-white text-xs sm:text-sm font-semibold px-4 py-2 rounded-xl hover:bg-indigo-700 transition-colors flex items-center gap-1.5"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                Añadir
                            </button>
                        @endif
                        <button
                            wire:click="removeItem({{ $item->id }})"
                            wire:confirm="¿Quitar este producto de tus favoritos?"
                            class="text-zinc-400 hover:text-red-500 text-xs sm:text-sm font-medium px-4 py-2 rounded-xl hover:bg-red-50 transition-colors flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Quitar
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <!-- Empty state -->
            <div class="py-20 text-center">
                <svg class="w-20 h-20 mx-auto text-zinc-200 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                <h2 class="text-xl font-semibold text-zinc-700 mb-2">Tu lista de favoritos está vacía</h2>
                <p class="text-zinc-500 mb-8">Guarda productos que te gusten para encontrarlos fácilmente después.</p>
                <a href="/catalogo" wire:navigate class="inline-flex items-center px-8 py-3 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">
                    Explorar Catálogo
                </a>
            </div>
        @endif
    </div>
</div>
