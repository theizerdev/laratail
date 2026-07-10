<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use App\Models\Customer;
use App\Models\Wishlist;
use App\Services\CartService;

new #[Layout('layouts.app')] #[Title('Lista de Regalos - Abastos Los Trinis')] class extends Component {
    public Customer $customer;

    public function mount(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function with(): array
    {
        $items = Wishlist::with(['product.category', 'product.brand'])
            ->where('customer_id', $this->customer->id)
            ->latest()
            ->get();

        return ['items' => $items];
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
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-rose-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-rose-500" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900">Lista de Regalos de {{ $customer->nombre }}</h1>
            <p class="mt-2 text-sm text-zinc-500">{{ $items->count() }} {{ $items->count() === 1 ? 'producto deseado' : 'productos deseados' }}</p>
        </div>

        @if($items->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($items as $item)
                @php $product = $item->product; @endphp
                <div class="flex gap-4 bg-white rounded-2xl border border-zinc-100 shadow-sm p-4 hover:shadow-md transition-shadow">
                    <a href="{{ route('store.product.detail', $product->slug) }}" wire:navigate class="flex-shrink-0">
                        <img src="{{ $product->imagen_principal_url ?? 'https://placehold.co/100' }}" alt="{{ $product->nombre }}" class="w-20 h-24 object-cover rounded-xl bg-zinc-100">
                    </a>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-zinc-400 uppercase tracking-wider">{{ optional($product->category)->nombre ?? '' }}</p>
                        <h3 class="font-semibold text-zinc-900 text-sm line-clamp-2 mt-1">
                            <a href="{{ route('store.product.detail', $product->slug) }}" wire:navigate class="hover:text-indigo-600 transition-colors">{{ $product->nombre }}</a>
                        </h3>
                        <div class="flex items-center gap-2 mt-2">
                            @if($product->tiene_descuento)
                                <p class="text-base font-bold text-red-600">${{ number_format($product->precio_oferta, 2) }}</p>
                                <p class="text-xs text-zinc-400 line-through">${{ number_format($product->precio, 2) }}</p>
                            @else
                                <p class="text-base font-bold text-zinc-900">${{ number_format($product->precio, 2) }}</p>
                            @endif
                        </div>
                        @if($product->stock > 0)
                            <button wire:click="addToCart({{ $product->id }})" class="mt-2 bg-indigo-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition-colors">
                                Regalar
                            </button>
                        @else
                            <span class="mt-2 inline-block text-xs text-red-500 font-medium">Agotado</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="py-16 text-center">
                <svg class="w-16 h-16 mx-auto text-zinc-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                <p class="text-zinc-500">Esta lista de regalos está vacía.</p>
            </div>
        @endif

        <div class="mt-8 text-center">
            <a href="/catalogo" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← Explorar más productos</a>
        </div>
    </div>
</div>
