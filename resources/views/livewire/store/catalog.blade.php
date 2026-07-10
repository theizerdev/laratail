<?php
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] #[Title('Catálogo - Abastos Los Trinis')] class extends Component {
    public string $currency = 'usd';
    public string $searchQuery = '';
    public $searchResults = [];
    public $searchSuggestions = [];
    public bool $showSearchDropdown = false;

    public function mount(): void
    {
        $this->currency = get_current_currency();
    }

    public function updatedSearchQuery(): void
    {
        if (strlen($this->searchQuery) < 2) {
            $this->searchResults = [];
            $this->searchSuggestions = [];
            $this->showSearchDropdown = false;
            return;
        }

        $q = '%' . $this->searchQuery . '%';

        // Buscar productos (misma lógica que search-overlay)
        $this->searchResults = Product::where('status', true)
            ->where(function ($builder) use ($q) {
                $builder->where('nombre', 'like', $q)
                    ->orWhere('descripcion_corta', 'like', $q)
                    ->orWhere('sku', 'like', $q);
            })
            ->with('category')
            ->select(['id', 'nombre', 'slug', 'imagen_principal', 'precio', 'precio_oferta', 'precio_bs', 'stock', 'category_id'])
            ->limit(5)
            ->get()
            ->toArray();

        // Buscar categorías
        $catSuggestions = Category::where('status', true)
            ->where('nombre', 'like', $q)
            ->select(['id', 'nombre', 'slug'])
            ->limit(2)
            ->get()
            ->map(fn($c) => ['type' => 'category', 'label' => $c->nombre, 'url' => '/catalogo/' . $c->slug])
            ->toArray();

        $this->searchSuggestions = $catSuggestions;
        $this->showSearchDropdown = true;
    }

    public function hideSearchDropdown(): void
    {
        // Pequeño delay para permitir click en resultados antes de ocultar
        $this->js('setTimeout(() => { $wire.showSearchDropdown = false }, 200)');
    }

    public function updatedCurrency($value): void
    {
        session(['currency' => strtolower($value)]);
        $this->dispatch('currency-updated', currency: $value);
    }

    #[On('currency-updated')]
    public function updateSelectedCurrency($currency): void
    {
        $this->currency = $currency;
    }
    // Filters
    #[Url]
    public string $category = '';
    #[Url]
    public string $sort = 'recent';
    #[Url]
    public bool $nuevo = false;
    #[Url]
    public bool $oferta = false;
    #[Url]
    public array $brands = [];
    #[Url]
    public int $minPrice = 0;
    #[Url]
    public int $maxPrice = 10000;
    #[Url]
    public string $search = '';

    public bool $showMobileFilters = false;
    public ?int $quickViewProductId = null;
    public bool $showBreadcrumbs = true;

    public function with(): array
    {
        $query = Product::with(['category', 'brand'])
            ->where('status', true);
            
        // Filtro de búsqueda
        if (!empty($this->search)) {
            $q = '%' . $this->search . '%';
            $query->where(function ($builder) use ($q) {
                $builder->where('nombre', 'like', $q)
                    ->orWhere('descripcion_corta', 'like', $q)
                    ->orWhere('sku', 'like', $q);
            });
        }

        // Category filter
        if ($this->category) {
            $cat = Category::where('slug', $this->category)->first();
            if ($cat) {
                $childIds = $cat->children()->pluck('id')->toArray();
                $query->whereIn('category_id', array_merge([$cat->id], $childIds));
            }
        }

        // Brand filter
        if (!empty($this->brands)) {
            $query->whereIn('brand_id', $this->brands);
        }

        // Price filter
        if ($this->minPrice > 0) {
            $query->where(function ($q) {
                $q->whereRaw('COALESCE(precio_oferta, precio) >= ?', [$this->minPrice]);
            });
        }
        if ($this->maxPrice < 10000) {
            $query->where(function ($q) {
                $q->whereRaw('COALESCE(precio_oferta, precio) <= ?', [$this->maxPrice]);
            });
        }

        // New only
        if ($this->nuevo) {
            $query->where('nuevo', true);
        }

        // On sale only
        if ($this->oferta) {
            $query->whereNotNull('precio_oferta')
                  ->whereColumn('precio_oferta', '<', 'precio');
        }

        // Sort
        $query = match($this->sort) {
            'price_asc' => $query->orderByRaw('COALESCE(precio_oferta, precio) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(precio_oferta, precio) DESC'),
            'name' => $query->orderBy('nombre'),
            'newest' => $query->latest('fecha_publicacion'),
            default => $query->latest(),
        };

        $wishlistProductIds = [];
        if (Auth::check()) {
            $customer = \App\Models\Customer::where('user_id', Auth::id())->first();
            if ($customer) {
                $wishlistProductIds = \App\Models\Wishlist::where('customer_id', $customer->id)
                    ->pluck('product_id')
                    ->toArray();
            }
        }

        return [
            'products' => $query->paginate(15)->withQueryString(),
            'categories' => Category::whereNull('parent_id')
                ->where('status', true)
                ->with(['children' => fn($q) => $q->where('status', true)->orderBy('orden')])
                ->orderBy('orden')
                ->get(),
            'availableBrands' => Brand::where('status', true)
                ->whereHas('products', fn($q) => $q->where('status', true))
                ->orderBy('nombre')
                ->get(),
            'activeCategory' => $this->category ? Category::where('slug', $this->category)->first() : null,
            'wishlistProductIds' => $wishlistProductIds,
        ];
    }

    #[Computed]
    public function quickViewProduct()
    {
        return $this->quickViewProductId
            ? Product::with(['category', 'brand'])->find($this->quickViewProductId)
            : null;
    }

    public function openQuickView(int $productId): void
    {
        $this->quickViewProductId = $productId;
        $this->js('Flux.modal("quick-view-modal").show()');
    }

    public function clearFilters(): void
    {
        $this->reset(['category', 'sort', 'nuevo', 'oferta', 'brands', 'minPrice', 'maxPrice']);
    }

    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) return;
        app(CartService::class)->addItem($product, 1);
        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: '¡Producto agregado al carrito!', type: 'success');
    }

    public function toggleWishlist(int $productId): void
    {
        if (!Auth::check()) {
            $this->redirect('/acceso', navigate: true);
            return;
        }
        $customer = \App\Models\Customer::where('user_id', Auth::id())->first();
        if (!$customer) return;

        $added = \App\Models\Wishlist::toggle($customer->id, $productId);
        $this->dispatch('wishlist-updated');
        $this->dispatch('notify',
            message: $added ? 'Producto añadido a favoritos.' : 'Producto eliminado de favoritos.',
            type: $added ? 'success' : 'info'
        );
    }

    public function toggleCompare(int $productId): void
    {
        $compare = session('compare_products', []);
        if (in_array($productId, $compare)) {
            $compare = array_values(array_diff($compare, [$productId]));
        } else {
            if (count($compare) >= 4) {
                $this->dispatch('notify', message: 'Solo puedes comparar hasta 4 productos.', type: 'warning');
                return;
            }
            $compare[] = $productId;
        }
        session(['compare_products' => $compare]);
    }

    public function isComparing(int $productId): bool
    {
        return in_array($productId, session('compare_products', []));
    }

    public function clearCompare(): void
    {
        session()->forget('compare_products');
    }
};
?>

<div>
    <!-- Breadcrumb -->
    @if($showBreadcrumbs)
    <div class="bg-white border-b border-zinc-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center gap-2 text-sm">
                <a href="/" wire:navigate class="text-zinc-500 hover:text-zinc-900 transition-colors">Inicio</a>
                <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="text-zinc-900 font-medium">
                    {{ $activeCategory?->nombre ?? 'Catálogo' }}
                </span>
                <span class="text-zinc-400 ml-2">({{ $products->total() }} productos)</span>
            </nav>
        </div>
    </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Product Grid -->
        <main class="w-full">
                <!-- Section Header -->
                <div class="mb-8">
                    <span class="text-indigo-600 text-sm font-semibold tracking-wider uppercase">Nuestra Tienda</span>
                    <h2 class="text-3xl font-extrabold text-zinc-900 tracking-tight mt-1">
                        {{ $activeCategory?->nombre ?? 'Catálogo de Productos' }}
                    </h2>
                </div>

                <!-- Top bar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                    <p class="text-sm text-zinc-500">
                        Mostrando <span class="font-medium text-zinc-700">{{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }}</span> de <span class="font-medium text-zinc-700">{{ $products->total() }}</span> resultados
                    </p>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                        <!-- Input de búsqueda en tiempo real -->
                        <div class="relative w-full sm:w-72">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce="searchQuery"
                                    wire:focus="showSearchDropdown = true"
                                    wire:blur="hideSearchDropdown()"
                                    placeholder="Buscar productos..."
                                    class="w-full pl-10 pr-4 py-2 rounded-lg border border-zinc-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all"
                                >
                            </div>
                            
                            <!-- Dropdown de resultados en tiempo real -->
                            @if($showSearchDropdown && (count($searchResults) > 0 || count($searchSuggestions) > 0))
                                <div class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-xl border border-zinc-200 z-50 overflow-hidden">
                                    <!-- Sugerencias de categorías -->
                                    @if(count($searchSuggestions) > 0)
                                        <div class="px-4 py-2 bg-zinc-50 border-b border-zinc-100">
                                            <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Categorías</p>
                                        </div>
                                        @foreach($searchSuggestions as $suggestion)
                                            <a href="{{ $suggestion['url'] }}" wire:navigate class="block px-4 py-3 hover:bg-zinc-50 transition-colors">
                                                <span class="text-sm font-medium text-zinc-700">{{ $suggestion['label'] }}</span>
                                            </a>
                                        @endforeach
                                    @endif
                                    
                                    <!-- Resultados de productos -->
                                    @if(count($searchResults) > 0)
                                        <div class="px-4 py-2 bg-zinc-50 border-b border-zinc-100 {{ count($searchSuggestions) > 0 ? '' : 'border-t-0' }}">
                                            <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Productos</p>
                                        </div>
                                        @foreach($searchResults as $product)
                                            <a href="/producto/{{ $product['slug'] }}" wire:navigate class="flex items-center gap-3 px-4 py-3 hover:bg-zinc-50 transition-colors">
                                                <img 
                                                    src="{{ $product['imagen_principal'] ?? 'https://placehold.co/40x40?text=?' }}" 
                                                    alt="{{ $product['nombre'] }}"
                                                    class="w-10 h-10 rounded-lg object-cover bg-zinc-100"
                                                >
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-zinc-900 truncate">{{ $product['nombre'] }}</p>
                                                    <p class="text-xs text-zinc-500">
                                                        ${{ number_format($product['precio_oferta'] ?? $product['precio'], 2) }}
                                                    </p>
                                                </div>
                                            </a>
                                        @endforeach
                                    @endif
                                    
                                    <!-- Ver todos los resultados -->
                                    @if(strlen($searchQuery) >= 2)
                                        <a href="/catalogo?search={{ urlencode($searchQuery) }}" wire:navigate class="block px-4 py-3 bg-indigo-50 hover:bg-indigo-100 transition-colors text-center">
                                            <span class="text-sm font-semibold text-indigo-600">Ver todos los resultados</span>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex items-center gap-2">
                            @if(is_venezuela_company())
                                <flux:select wire:model.live="currency" class="rounded-lg border-zinc-300 text-sm focus:ring-indigo-500 focus:border-indigo-500 w-32">
                                    <option value="usd">USD ($)</option>
                                    <option value="bs">VES (Bs.)</option>
                                </flux:select>
                            @endif
                            <flux:select wire:model.live="sort" class="rounded-lg border-zinc-300 text-sm focus:ring-indigo-500 focus:border-indigo-500 flex-1 sm:flex-initial">
                                <option value="recent">Más recientes</option>
                                <option value="price_asc">Precio: menor a mayor</option>
                                <option value="price_desc">Precio: mayor a menor</option>
                                <option value="name">Nombre A-Z</option>
                                <option value="newest">Nuevos primero</option>
                            </flux:select>
                        </div>
                    </div>
                </div>

                <!-- Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                    @forelse($products as $product)
                    <div class="group relative flex flex-col bg-white rounded-2xl border border-zinc-100 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden">
                        <!-- Image -->
                        <div class="relative z-10 aspect-[4/5] overflow-hidden bg-zinc-100">
                            <a href="{{ route('store.product.detail', $product->slug) }}" wire:navigate class="block w-full h-full">
                                <img src="{{ $product->imagen_principal_url ?? 'https://placehold.co/400x500?text=Producto' }}" alt="{{ $product->nombre }}" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" />
                            </a>

                            @if($product->nuevo)
                                <div class="absolute top-4 left-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-900 text-white shadow-sm">Nuevo</span>
                                </div>
                            @elseif($product->tiene_descuento)
                                <div class="absolute top-4 left-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-600 text-white shadow-sm">-{{ $product->porcentaje_descuento }}%</span>
                                </div>
                            @endif

                            {{-- Wishlist button --}}
                            <button wire:click.prevent.stop="toggleWishlist({{ $product->id }})" class="absolute top-4 right-4 z-20 w-8 h-8 rounded-full bg-white/90 backdrop-blur-sm text-zinc-500 hover:text-red-500 flex items-center justify-center transition-all shadow-sm" title="Guardar en favoritos">
                                @if(in_array($product->id, $wishlistProductIds))
                                    <svg class="w-4 h-4 text-red-500 fill-current" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                @endif
                            </button>

                            {{-- Compare button --}}
                            <button wire:click.prevent.stop="toggleCompare({{ $product->id }})" class="absolute top-14 right-4 z-20 w-8 h-8 rounded-full flex items-center justify-center transition-all shadow-sm {{ $this->isComparing($product->id) ? 'bg-indigo-600 text-white' : 'bg-white/80 backdrop-blur-sm text-zinc-500 hover:text-indigo-600 border border-zinc-200' }}" title="{{ $this->isComparing($product->id) ? 'Quitar de comparación' : 'Comparar' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </button>
                            <!-- Quick Add & View Detail (Desktop Only) -->
                            <div class="absolute inset-x-0 bottom-0 p-4 hidden lg:block opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 z-20 pointer-events-none">
                                <div class="flex gap-2 w-full bg-white/90 backdrop-blur-sm p-1 rounded-xl shadow-lg pointer-events-auto">
                                    <flux:button wire:click.prevent.stop="openQuickView({{ $product->id }})" variant="subtle" class="flex-1 !px-2">
                                        <svg class="w-4 h-4 mr-1 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Detalle
                                    </flux:button>
                                    <flux:button wire:click.prevent.stop="addToCart({{ $product->id }})" variant="primary" class="flex-1 !px-2">
                                        <svg class="w-4 h-4 mr-1 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        Añadir
                                    </flux:button>
                                </div>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="p-5 flex-1 flex flex-col">
                            <p class="text-xs text-zinc-500 mb-1">
                                {{ optional($product->category)->nombre ?? 'General' }}
                                @if($product->brand) · {{ $product->brand->nombre }} @endif
                            </p>
                            <h6 class="text-[.625rem] font-semibold text-zinc-900 mb-2 line-clamp-2">
                                <a href="{{ route('store.product.detail', $product->slug) }}" wire:navigate>
                                    <span aria-hidden="true" class="absolute inset-0"></span>
                                    {{ $product->nombre }}
                                </a>
                            </h6>
                            <div class="mt-auto flex items-center gap-2">
                                @if($product->tiene_descuento)
                                    <p class="text-lg font-bold text-red-600">{{ money_product($product, true) }}</p>
                                    <p class="text-sm text-zinc-400 line-through">{{ money_product($product) }}</p>
                                @else
                                    <p class="text-lg font-bold text-zinc-900">{{ money_product($product) }}</p>
                                @endif
                            </div>
                            @if($product->stock <= 5 && $product->stock > 0)
                                <p class="text-xs text-orange-500 mt-1">¡Solo {{ $product->stock }} disponibles!</p>
                            @elseif($product->stock === 0)
                                <p class="text-xs text-red-500 mt-1 font-medium">Agotado</p>
                            @endif

                            <!-- Mobile Actions (Visible on mobile/tablet, hidden on desktop) -->
                            <div class="mt-3 flex gap-1.5 lg:hidden z-20 relative">
                                <flux:button wire:click.prevent.stop="openQuickView({{ $product->id }})" variant="subtle" size="sm" class="flex-1 !px-1.5 text-xs">
                                    Detalle
                                </flux:button>
                                <flux:button wire:click.prevent.stop="addToCart({{ $product->id }})" variant="primary" size="sm" class="flex-1 !px-1.5 text-xs" :disabled="$product->stock <= 0">
                                    Añadir
                                </flux:button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full py-16 text-center">
                        <svg class="w-16 h-16 mx-auto text-zinc-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <h3 class="text-lg font-semibold text-zinc-700 mb-2">No se encontraron productos</h3>
                        <p class="text-zinc-500 mb-6">Intenta ajustar los filtros para ver más resultados.</p>
                        <flux:button wire:click="clearFilters" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors">
                            Limpiar filtros
                        </flux:button>
                    </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            </main>
    </div>

    {{-- Floating Compare Bar --}}
    @php $compareCount = count(session('compare_products', [])); @endphp
    @if($compareCount > 0)
    <div class="fixed bottom-0 inset-x-0 z-50 bg-white border-t border-zinc-200 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span class="text-sm font-semibold text-zinc-900">{{ $compareCount }} producto(s) seleccionados para comparar</span>
            </div>
            <div class="flex gap-3">
                <button wire:click="clearCompare" class="text-sm text-zinc-500 hover:text-zinc-700 font-medium transition-colors">Limpiar</button>
                <a href="/comparar" wire:navigate class="bg-indigo-600 text-white text-sm font-semibold px-5 py-2 rounded-xl hover:bg-indigo-700 transition-colors">Comparar ahora</a>
            </div>
        </div>
    </div>
    @endif

    {{-- Quick View Modal --}}
    <flux:modal name="quick-view-modal" class="!max-w-2xl sm:p-6 overflow-hidden">
        @if($this->quickViewProduct)
            @php $qp = $this->quickViewProduct; @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">

                {{-- Left: Image --}}
                <div class="relative aspect-[4/5] rounded-xl overflow-hidden bg-zinc-50 border border-zinc-100/80 shadow-xs flex items-center justify-center">
                    <img
                        src="{{ $qp->imagen_principal_url ?? 'https://placehold.co/400x500?text=Producto' }}"
                        alt="{{ $qp->nombre }}"
                        class="w-full h-full object-cover object-center"
                    />

                    @if($qp->nuevo)
                        <div class="absolute top-3 left-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-zinc-950 text-white shadow-sm">Nuevo</span>
                        </div>
                    @elseif($qp->tiene_descuento)
                        <div class="absolute top-3 left-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-600 text-white shadow-sm">-{{ $qp->porcentaje_descuento }}%</span>
                        </div>
                    @endif
                </div>

                {{-- Right: Content --}}
                <div class="flex flex-col h-full space-y-4">
                    {{-- Categories / Brand --}}
                    <div class="flex flex-wrap gap-1.5 text-[.625rem] font-bold uppercase tracking-wider text-zinc-400">
                        <span>{{ optional($qp->category)->nombre ?? 'General' }}</span>
                        @if($qp->brand)
                            <span class="text-zinc-300">&middot;</span>
                            <span class="text-indigo-600">{{ $qp->brand->nombre }}</span>
                        @endif
                    </div>

                    {{-- Title --}}
                    <h2 class="text-xl font-bold text-zinc-900 leading-tight">{{ $qp->nombre }}</h2>

                    {{-- Pricing --}}
                    <div class="flex items-baseline gap-2">
                        @if($qp->tiene_descuento)
                            <span class="text-2xl font-black text-red-600">${{ number_format($qp->precio_oferta, 2) }}</span>
                            <span class="text-sm text-zinc-400 line-through">${{ number_format($qp->precio, 2) }}</span>
                        @else
                            <span class="text-2xl font-black text-zinc-900">${{ number_format($qp->precio, 2) }}</span>
                        @endif
                    </div>

                    {{-- Stock Badge --}}
                    <div>
                        @if($qp->stock > 5)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100/80">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                En Stock
                            </span>
                        @elseif($qp->stock <= 5 && $qp->stock > 0)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-50 text-orange-700 border border-orange-100/80">
                                <span class="h-1.5 w-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                                Últimas {{ $qp->stock }} unidades
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-100/80">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                Agotado
                            </span>
                        @endif
                    </div>

                    {{-- Short description --}}
                    <p class="text-xs text-zinc-600 leading-relaxed max-h-36 overflow-y-auto pr-2">
                        {{ $qp->descripcion_corta ?: ($qp->descripcion ?: 'No hay descripción disponible para este producto.') }}
                    </p>

                    {{-- Actions block --}}
                    <div class="pt-4 border-t border-zinc-100 flex items-center gap-2 mt-auto">
                        {{-- Add to Cart --}}
                        <flux:button
                            wire:click.prevent="addToCart({{ $qp->id }})"
                            variant="primary"
                            class="flex-1 !bg-indigo-600 hover:!bg-indigo-700"
                            :disabled="$qp->stock <= 0"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            Añadir al carrito
                        </flux:button>

                        {{-- Add to Wishlist --}}
                        <button
                            wire:click.prevent="toggleWishlist({{ $qp->id }})"
                            class="w-10 h-10 rounded-xl border border-zinc-200 bg-white flex items-center justify-center text-zinc-500 hover:text-red-500 hover:border-red-200 transition-all shadow-xs shrink-0"
                            title="Añadir a favoritos"
                        >
                            @if(in_array($qp->id, $wishlistProductIds))
                                <svg class="w-5 h-5 text-red-500 fill-current" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            @endif
                        </button>
                    </div>

                </div>

            </div>
        @endif
    </flux:modal>
</div>