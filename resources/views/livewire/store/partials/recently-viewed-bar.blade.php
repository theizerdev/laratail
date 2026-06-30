<?php
use Livewire\Volt\Component;
use App\Models\Product;

new class extends Component {
    public function clearHistory(): void
    {
        session()->forget('recently_viewed');
    }

    public function with(): array
    {
        $ids = session('recently_viewed', []);
        $products = collect();
        if (!empty($ids)) {
            $products = Product::with('category', 'brand')
                ->where('status', true)
                ->whereIn('id', $ids)
                ->limit(8)
                ->get()
                ->sortBy(fn($p) => array_search($p->id, $ids))
                ->values();
        }
        return ['products' => $products];
    }
};
?>

<div>
    @if($products->count() > 0)
    <section class="bg-zinc-50 border-t border-zinc-100 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-zinc-900">Vistos Recientemente</h2>
                <div class="flex gap-3">
                    <button wire:click="clearHistory" class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors font-medium">Limpiar</button>
                    <a href="/vistos-recientemente" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800 transition-colors font-medium">Ver todos →</a>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($products as $product)
                <a href="{{ route('store.product.detail', $product->slug) }}" wire:navigate class="group flex flex-col bg-white rounded-xl border border-zinc-100 shadow-sm hover:shadow-md transition-all overflow-hidden">
                    <div class="relative aspect-square overflow-hidden bg-zinc-100">
                        <img src="{{ $product->imagen_principal_url ?? 'https://placehold.co/200' }}" alt="{{ $product->nombre }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
                        @if($product->tiene_descuento)
                            <span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">-{{ $product->porcentaje_descuento }}%</span>
                        @endif
                    </div>
                    <div class="p-3 space-y-0.5">
                        <h3 class="text-xs font-semibold text-zinc-900 line-clamp-2 group-hover:text-indigo-600 transition-colors">{{ $product->nombre }}</h3>
                        <p class="text-sm font-bold {{ $product->tiene_descuento ? 'text-red-600' : 'text-zinc-900' }}">
                            {{ money_product($product, $product->tiene_descuento) }}
                        </p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif
</div>
