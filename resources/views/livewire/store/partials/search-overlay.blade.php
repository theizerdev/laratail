<?php
use Livewire\Volt\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Services\CartService;

new class extends Component {
    public string $query = '';
    public $results = [];
    public $suggestions = [];
    public bool $isOpen = false;
    public int $selectedIdx = -1;

    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) return;
        app(CartService::class)->addItem($product, 1);
        $this->dispatch('cart-updated');
        $this->dispatch('notify', 
            message: 'Producto añadido al carrito.', 
            type: 'success'
        );
    }

    public function updatedQuery(): void
    {
        $this->selectedIdx = -1;

        if (strlen($this->query) < 2) {
            $this->results = [];
            $this->suggestions = [];
            return;
        }

        $q = '%' . $this->query . '%';

        // Search products
        $this->results = Product::where('status', true)
            ->where(function ($builder) use ($q) {
                $builder->where('nombre', 'like', $q)
                    ->orWhere('descripcion_corta', 'like', $q)
                    ->orWhere('sku', 'like', $q);
            })
            ->with('category')
            ->select(['id', 'nombre', 'slug', 'imagen_principal', 'precio', 'precio_oferta', 'precio_bs', 'stock', 'category_id'])
            ->limit(8)
            ->get()
            ->toArray();

        // Search categories matching query
        $catSuggestions = Category::where('status', true)
            ->where('nombre', 'like', $q)
            ->select(['id', 'nombre', 'slug'])
            ->limit(3)
            ->get()
            ->map(fn($c) => ['type' => 'category', 'label' => $c->nombre, 'url' => '/catalogo/' . $c->slug])
            ->toArray();

        // Search brands matching query
        $brandSuggestions = Brand::where('status', true)
            ->where('nombre', 'like', $q)
            ->select(['id', 'nombre'])
            ->limit(2)
            ->get()
            ->map(fn($b) => ['type' => 'brand', 'label' => $b->nombre, 'url' => '/catalogo?brands[]=' . $b->id])
            ->toArray();

        $this->suggestions = array_merge($catSuggestions, $brandSuggestions);
    }

    public function open(): void
    {
        $this->isOpen = true;
        $this->query = '';
        $this->results = [];
        $this->suggestions = [];
        $this->selectedIdx = -1;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function selectResult(): void
    {
        $allItems = $this->getAllItems();
        if ($this->selectedIdx >= 0 && $this->selectedIdx < count($allItems)) {
            $item = $allItems[$this->selectedIdx];
            $this->redirect($item['url'], navigate: true);
        } elseif (strlen($this->query) >= 2) {
            $this->redirect('/catalogo?search=' . urlencode($this->query), navigate: true);
        }
    }

    public function moveDown(): void
    {
        $total = count($this->getAllItems());
        if ($total > 0) {
            $this->selectedIdx = min($this->selectedIdx + 1, $total - 1);
        }
    }

    public function moveUp(): void
    {
        if ($this->selectedIdx > 0) {
            $this->selectedIdx--;
        }
    }

    private function getAllItems(): array
    {
        $products = collect($this->results)->map(fn($r) => [
            'type' => 'product',
            'label' => $r['nombre'],
            'url' => '/producto/' . $r['slug'],
        ])->toArray();

        return array_merge($this->suggestions, $products);
    }

    public function with(): array
    {
        return [
            'trendingProducts' => Product::where('status', true)
                ->where('destacado', true)
                ->select(['id', 'nombre', 'slug', 'precio', 'precio_oferta'])
                ->limit(4)
                ->get(),
            'popularCategories' => Category::where('status', true)
                ->whereNull('parent_id')
                ->orderBy('orden')
                ->select(['id', 'nombre', 'slug'])
                ->limit(6)
                ->get(),
        ];
    }
};
?>

<div x-on:open-search.window="$wire.open()" x-on:keydown.escape.window="$wire.close()">
    <div x-show="$wire.isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm"
         x-on:click="$wire.close()"
         style="display: none;">

        <div class="max-w-2xl mx-auto mt-[8vh] px-4" x-on:click.stop>
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden max-h-[80vh] flex flex-col">
                <!-- Search Input -->
                <div class="flex items-center gap-3 px-5 border-b border-zinc-100 flex-shrink-0">
                    <svg class="w-5 h-5 text-zinc-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text"
                           wire:model.live.debounce.300ms="query"
                           wire:keydown.arrow-down="moveDown"
                           wire:keydown.arrow-up="moveUp"
                           wire:keydown.enter="selectResult"
                           placeholder="Buscar productos, categorías, marcas..."
                           class="w-full py-4 text-base border-0 focus:ring-0 text-zinc-900 placeholder-zinc-400 outline-none"
                           autofocus>
                    @if(strlen($query) > 0)
                        <button wire:click="$set('query', '')" class="text-zinc-400 hover:text-zinc-600 transition-colors p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    @endif
                    <button x-on:click="$wire.close()" class="text-zinc-400 hover:text-zinc-600 transition-colors p-1 hidden sm:block">
                        <kbd class="text-xs bg-zinc-100 px-1.5 py-0.5 rounded text-zinc-500 font-mono">ESC</kbd>
                    </button>
                </div>

                <!-- Scrollable Content -->
                <div class="overflow-y-auto flex-1">
                    {{-- Results --}}
                    @if(!empty($results) || !empty($suggestions))
                        {{-- Category/Brand suggestions --}}
                        @if(!empty($suggestions))
                        <div class="px-5 pt-3 pb-1">
                            <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider mb-2">Categorías y Marcas</p>
                            @foreach($suggestions as $sIdx => $sug)
                                @php $globalIdx = $sIdx; @endphp
                                <a href="{{ $sug['url'] }}" wire:navigate
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $selectedIdx === $globalIdx ? 'bg-indigo-50' : 'hover:bg-zinc-50' }}"
                                   x-on:click="$wire.close()">
                                    @if($sug['type'] === 'category')
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    @else
                                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    @endif
                                    <span class="text-sm text-zinc-700">{{ $sug['label'] }}</span>
                                    <span class="text-xs text-zinc-400 ml-auto">{{ $sug['type'] === 'category' ? 'Categoría' : 'Marca' }}</span>
                                </a>
                            @endforeach
                        </div>
                        @endif

                        {{-- Product results --}}
                        @if(!empty($results))
                        <div class="px-5 pt-3 pb-1">
                            <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider mb-2">Productos</p>
                            @foreach($results as $rIdx => $result)
                                @php $globalIdx = count($suggestions) + $rIdx; @endphp
                                <div class="group/item flex items-center justify-between rounded-lg transition-colors {{ $selectedIdx === $globalIdx ? 'bg-indigo-50' : 'hover:bg-zinc-50' }}">
                                    <!-- Product Info Link -->
                                    <a href="/producto/{{ $result['slug'] }}" wire:navigate
                                       class="flex items-center gap-4 px-3 py-2.5 flex-1 min-w-0"
                                       x-on:click="$wire.close()">
                                        <img src="{{ $result['imagen_principal_url'] ?? 'https://placehold.co/50' }}" class="w-12 h-12 rounded-lg object-cover bg-zinc-100 flex-shrink-0" alt="">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-zinc-900 truncate">{{ $result['nombre'] }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                @if(!empty($result['precio_oferta']) && $result['precio_oferta'] < $result['precio'])
                                                     <span class="text-sm font-bold text-red-600">{{ money_product($result, true) }}</span>
                                                     <span class="text-xs text-zinc-400 line-through">{{ money_product($result, false) }}</span>
                                                 @else
                                                     <span class="text-sm font-bold text-zinc-900">{{ money_product($result, false) }}</span>
                                                 @endif
                                                @if(($result['stock'] ?? 0) <= 0)
                                                    <span class="text-xs text-red-500 font-medium">Agotado</span>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                    <!-- Action Buttons -->
                                    <div class="flex items-center gap-1.5 px-3 py-2.5 flex-shrink-0">
                                        @if(($result['stock'] ?? 0) > 0)
                                            <button wire:click.stop.prevent="addToCart({{ $result['id'] }})"
                                                    class="p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-full transition-colors flex items-center justify-center"
                                                    title="Añadir al carrito">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                            </button>
                                        @endif
                                        <svg class="w-4 h-4 text-zinc-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- "Ver todos" link --}}
                        @if(strlen($query) >= 2)
                        <div class="px-5 py-2 border-t border-zinc-50">
                            <a href="/catalogo?search={{ urlencode($query) }}" wire:navigate class="flex items-center justify-center gap-2 py-2.5 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors rounded-lg hover:bg-indigo-50" x-on:click="$wire.close()">
                                Ver todos los resultados para "{{ $query }}"
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                        </div>
                        @endif
                        @endif

                    {{-- Empty state when typing --}}
                    @elseif(strlen($query) >= 2)
                    <div class="px-5 py-10 text-center">
                        <svg class="w-12 h-12 mx-auto text-zinc-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <p class="text-sm text-zinc-500">No se encontraron resultados para "<strong>{{ $query }}</strong>"</p>
                        <p class="text-xs text-zinc-400 mt-1">Intenta con otros términos o explora nuestro catálogo</p>
                    </div>
                    @elseif(strlen($query) > 0)
                    <div class="px-5 py-10 text-center">
                        <p class="text-sm text-zinc-400">Escribe al menos 2 caracteres para buscar...</p>
                    </div>

                    {{-- Default state: trending + categories --}}
                    @else
                    <div class="px-5 py-4">
                        {{-- Trending products --}}
                        @if($trendingProducts->count() > 0)
                        <div class="mb-5">
                            <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0013 13a2.99 2.99 0 00-.879-2.121z" clip-rule="evenodd"/></svg>
                                Productos Destacados
                            </p>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($trendingProducts as $tp)
                                <a href="{{ route('store.product.detail', $tp->slug) }}" wire:navigate class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-zinc-50 transition-colors" x-on:click="$wire.close()">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-zinc-800 truncate">{{ $tp->nombre }}</p>
                                        <p class="text-xs font-bold {{ $tp->tiene_descuento ? 'text-red-600' : 'text-zinc-900' }}">{{ money_product($tp, $tp->tiene_descuento) }}</p>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Popular categories --}}
                        @if($popularCategories->count() > 0)
                        <div>
                            <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider mb-3">Categorías Populares</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($popularCategories as $pc)
                                <a href="/catalogo/{{ $pc->slug }}" wire:navigate class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors" x-on:click="$wire.close()">
                                    {{ $pc->nombre }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="px-5 py-2.5 bg-zinc-50 border-t border-zinc-100 flex items-center justify-between text-xs text-zinc-400 flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1"><kbd class="bg-white px-1 py-0.5 rounded text-[10px] font-mono border border-zinc-200">↑↓</kbd> navegar</span>
                        <span class="flex items-center gap-1"><kbd class="bg-white px-1 py-0.5 rounded text-[10px] font-mono border border-zinc-200">↵</kbd> seleccionar</span>
                    </div>
                    <span class="flex items-center gap-1"><kbd class="bg-white px-1 py-0.5 rounded text-[10px] font-mono border border-zinc-200">esc</kbd> cerrar</span>
                </div>
            </div>
        </div>
    </div>
</div>
