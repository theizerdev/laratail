<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use App\Models\Product;
use App\Services\CartService;

new #[Layout('layouts.app', ['meta_robots' => 'noindex, nofollow'])] #[Title('Comparar Productos - Abastos Los Trinis')] class extends Component {

    public function addToCompare(int $productId): void
    {
        $compare = session('compare_products', []);
        if (in_array($productId, $compare)) return;
        if (count($compare) >= 4) {
            $this->dispatch('notify', message: 'Solo puedes comparar hasta 4 productos a la vez.', type: 'warning');
            return;
        }
        $compare[] = $productId;
        session(['compare_products' => $compare]);
    }

    public function removeFromCompare(int $productId): void
    {
        $compare = session('compare_products', []);
        $compare = array_values(array_diff($compare, [$productId]));
        session(['compare_products' => $compare]);
    }

    public function clearCompare(): void
    {
        session()->forget('compare_products');
    }

    public function addAllToCart(): void
    {
        $ids = session('compare_products', []);
        $cartService = app(CartService::class);
        foreach ($ids as $id) {
            $product = Product::find($id);
            if ($product && $product->stock > 0) {
                $cartService->addItem($product, 1);
            }
        }
        $this->dispatch('cart-updated');
        $this->redirect('/carrito', navigate: true);
    }

    public function with(): array
    {
        $ids = session('compare_products', []);
        $products = collect();
        if (!empty($ids)) {
            $products = Product::with('category', 'brand', 'images')
                ->where('status', true)
                ->whereIn('id', $ids)
                ->get();
        }
        return ['products' => $products];
    }
};
?>

<div>
    <!-- Breadcrumb -->
    <div class="bg-white border-b border-zinc-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center gap-2 text-sm">
                <a href="/" wire:navigate class="text-zinc-500 hover:text-zinc-900 transition-colors">Inicio</a>
                <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="text-zinc-900 font-medium">Comparar Productos</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900">Comparar Productos</h1>
                <p class="mt-1 text-sm text-zinc-500">
                    @if($products->count() > 0)
                        {{ $products->count() }} de 4 productos seleccionados
                    @else
                        Agrega productos para comparar sus características
                    @endif
                </p>
            </div>
            @if($products->count() > 0)
                <div class="flex gap-3">
                    <button wire:click="clearCompare" class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors font-medium px-4 py-2 border border-zinc-200 rounded-xl hover:bg-zinc-50">
                        Limpiar todo
                    </button>
                </div>
            @endif
        </div>

        @if($products->count() === 0)
            <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm py-20 text-center">
                <svg class="w-16 h-16 mx-auto text-zinc-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <h2 class="text-lg font-semibold text-zinc-700 mb-2">No hay productos para comparar</h2>
                <p class="text-zinc-500 mb-6">Agrega productos desde el catálogo o la ficha de producto.</p>
                <a href="/catalogo" wire:navigate class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors">
                    Ir al catálogo
                </a>
            </div>
        @else
            {{-- Product cards row --}}
            <div class="grid grid-cols-2 md:grid-cols-{{ min($products->count(), 4) }} gap-4 mb-8">
                @foreach($products as $product)
                <div class="relative bg-white rounded-2xl border border-zinc-100 shadow-sm overflow-hidden group">
                    <button wire:click="removeFromCompare({{ $product->id }})" class="absolute top-3 right-3 z-10 w-7 h-7 rounded-full bg-white/80 backdrop-blur-sm border border-zinc-200 flex items-center justify-center text-zinc-400 hover:text-red-500 hover:border-red-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <a href="{{ route('store.product.detail', $product->slug) }}" wire:navigate>
                        <div class="aspect-square overflow-hidden bg-zinc-100">
                            <img src="{{ $product->imagen_principal_url ?? 'https://placehold.co/300' }}" alt="{{ $product->nombre }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
                        </div>
                    </a>
                    <div class="p-4 space-y-1">
                        <p class="text-xs text-zinc-400 uppercase tracking-wider">{{ optional($product->category)->nombre ?? '' }}</p>
                        <h3 class="text-sm font-semibold text-zinc-900 line-clamp-2">{{ $product->nombre }}</h3>
                        <p class="text-lg font-bold {{ $product->tiene_descuento ? 'text-red-600' : 'text-zinc-900' }}">
                            {{ money_product($product, $product->tiene_descuento) }}
                        </p>
                    </div>
                </div>
                @endforeach
                {{-- Empty slots --}}
                @for($i = $products->count(); $i < 4; $i++)
                <div class="bg-zinc-50 border-2 border-dashed border-zinc-200 rounded-2xl flex items-center justify-center p-8 min-h-[200px]">
                    <div class="text-center">
                        <svg class="w-8 h-8 mx-auto text-zinc-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <p class="text-xs text-zinc-400">Agregar producto</p>
                    </div>
                </div>
                @endfor
            </div>

            {{-- Comparison table --}}
            <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm overflow-hidden">
                <table class="w-full">
                    <tbody class="divide-y divide-zinc-100">
                        <tr class="bg-zinc-50">
                            <td class="px-6 py-4 text-sm font-semibold text-zinc-700 w-40">Precio</td>
                            @foreach($products as $product)
                            <td class="px-6 py-4 text-center">
                                @if($product->tiene_descuento)
                                    <p class="text-lg font-bold text-red-600">{{ money_product($product, true) }}</p>
                                    <p class="text-xs text-zinc-400 line-through">{{ money_product($product, false) }}</p>
                                @else
                                    <p class="text-lg font-bold text-zinc-900">{{ money_product($product, false) }}</p>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-6 py-4 text-sm font-semibold text-zinc-700">Marca</td>
                            @foreach($products as $product)
                            <td class="px-6 py-4 text-center text-sm text-zinc-600">{{ $product->brand?->nombre ?? '—' }}</td>
                            @endforeach
                        </tr>
                        <tr class="bg-zinc-50">
                            <td class="px-6 py-4 text-sm font-semibold text-zinc-700">Categoría</td>
                            @foreach($products as $product)
                            <td class="px-6 py-4 text-center text-sm text-zinc-600">{{ $product->category?->nombre ?? '—' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-6 py-4 text-sm font-semibold text-zinc-700">Disponibilidad</td>
                            @foreach($products as $product)
                            <td class="px-6 py-4 text-center text-sm">
                                @if($product->stock > 5)
                                    <span class="text-emerald-600 font-medium">En stock ({{ $product->stock }})</span>
                                @elseif($product->stock > 0)
                                    <span class="text-orange-500 font-medium">¡Solo {{ $product->stock }}!</span>
                                @else
                                    <span class="text-red-500 font-medium">Agotado</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        <tr class="bg-zinc-50">
                            <td class="px-6 py-4 text-sm font-semibold text-zinc-700">SKU</td>
                            @foreach($products as $product)
                            <td class="px-6 py-4 text-center text-sm text-zinc-500 font-mono">{{ $product->sku }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-6 py-4 text-sm font-semibold text-zinc-700">Descripción</td>
                            @foreach($products as $product)
                            <td class="px-6 py-4 text-center text-sm text-zinc-600">{{ Str::limit($product->descripcion_corta, 80) ?? '—' }}</td>
                            @endforeach
                        </tr>
                        <tr class="bg-zinc-50">
                            <td class="px-6 py-4 text-sm font-semibold text-zinc-700">Reseñas</td>
                            @foreach($products as $product)
                            <td class="px-6 py-4 text-center text-sm text-zinc-600">
                                @if($product->reviews_count > 0)
                                    <span class="flex items-center justify-center gap-1">
                                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        {{ number_format($product->rating_promedio, 1) }} ({{ $product->reviews_count }})
                                    </span>
                                @else
                                    <span class="text-zinc-400">Sin reseñas</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-6 py-4 text-sm font-semibold text-zinc-700">Acciones</td>
                            @foreach($products as $product)
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('store.product.detail', $product->slug) }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Ver detalle</a>
                            </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
