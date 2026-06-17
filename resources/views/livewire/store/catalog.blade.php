<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Services\CartService;

new #[Layout('layouts.app')] #[Title('Catálogo - Laratail Store')] class extends Component {
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

    public bool $showMobileFilters = false;

    public function with(): array
    {
        $query = Product::with(['category', 'brand'])
            ->where('status', true);

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

        return [
            'products' => $query->paginate(12)->withQueryString(),
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
        ];
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
                <span class="text-zinc-900 font-medium">
                    {{ $activeCategory?->nombre ?? 'Catálogo' }}
                </span>
                <span class="text-zinc-400 ml-2">({{ $products->total() }} productos)</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">

            <!-- Mobile Filter flux:button -->
            <div class="lg:hidden">
                <flux:button wire:click="$toggle('showMobileFilters')" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border border-zinc-200 rounded-xl text-zinc-700 font-medium hover:bg-zinc-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filtros
                    @if($category || $nuevo || $oferta || !empty($brands))
                        <span class="bg-indigo-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                            {{ ($category ? 1 : 0) + ($nuevo ? 1 : 0) + ($oferta ? 1 : 0) + (!empty($brands) ? 1 : 0) }}
                        </span>
                    @endif
                </flux:button>
            </div>

            <!-- Sidebar Filters -->
            <aside class="{{ $showMobileFilters ? 'block' : 'hidden' }} lg:block w-full lg:w-64 flex-shrink-0">
                <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 space-y-6 lg:sticky lg:top-24">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-zinc-900 text-lg">Filtros</h3>
                        <flux:button wire:click="clearFilters" class="text-xs text-indigo-600 hover:text-indigo-800 transition-colors font-medium">Limpiar</flux:button>
                    </div>

                    <!-- Categories -->
                    <div>
                        <h4 class="text-sm font-semibold text-zinc-700 mb-3">Categorías</h4>
                        <div class="space-y-1">
                            <flux:button wire:click="$set('category', '')" class="block w-full text-left px-3 py-1.5 rounded-lg text-sm transition-colors {{ !$category ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-zinc-600 hover:bg-zinc-50' }}">
                                Todas
                            </flux:button>
                            @foreach($categories as $cat)
                                <flux:button wire:click="$set('category', '{{ $cat->slug }}')" class="block w-full text-left px-3 py-1.5 rounded-lg text-sm transition-colors {{ $category === $cat->slug ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-zinc-600 hover:bg-zinc-50' }}">
                                    {{ $cat->nombre }}
                                </flux:button>
                                @foreach($cat->children as $child)
                                    <flux:button wire:click="$set('category', '{{ $child->slug }}')" class="block w-full text-left pl-7 pr-3 py-1 rounded-lg text-sm transition-colors {{ $category === $child->slug ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-zinc-500 hover:bg-zinc-50' }}">
                                        {{ $child->nombre }}
                                    </flux:button>
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                    <!-- Brands -->
                    @if($availableBrands->count() > 0)
                    <div>
                        <h4 class="text-sm font-semibold text-zinc-700 mb-3">Marcas</h4>
                        <div class="space-y-2">
                            @foreach($availableBrands as $brand)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <flux:checkbox type="checkbox" wire:model.live="brands" value="{{ $brand->id }}" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                                        </flux:checkbox>
                                    <span class="text-sm text-zinc-600">{{ $brand->nombre }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Price Range -->
                    <div>
                        <h4 class="text-sm font-semibold text-zinc-700 mb-3">Precio</h4>
                        <div class="flex items-center gap-2">
                             <flux:input
                wire:model="minPrice"
                type="number"
                placeholder="Min"
            />
                            <span class="text-zinc-400 text-sm">—</span>
                            <flux:input
                                wire:model="maxPrice"
                                type="number"
                                placeholder="Max"
                         />
                        </div>
                    </div>

                    <!-- Quick Filters -->
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <flux:checkbox type="checkbox" wire:model.live="nuevo" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">   
                                </flux:checkbox>
                            <span class="text-sm text-zinc-600">Solo novedades</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <flux:checkbox type="checkbox" wire:model.live="oferta" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                                </flux:checkbox>
                            <span class="text-sm text-zinc-600">Solo en oferta</span>
                        </label>
                    </div>
                </div>
            </aside>

            <!-- Product Grid -->
            <main class="flex-1 min-w-0">
                <!-- Top bar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                    <p class="text-sm text-zinc-500">
                        Mostrando <span class="font-medium text-zinc-700">{{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }}</span> de <span class="font-medium text-zinc-700">{{ $products->total() }}</span> resultados
                    </p>
                    <flux:select wire:model.live="sort" class="rounded-lg border-zinc-300 text-sm focus:ring-indigo-500 focus:border-indigo-500 w-full sm:w-auto">
                        <option value="recent">Más recientes</option>
                        <option value="price_asc">Precio: menor a mayor</option>
                        <option value="price_desc">Precio: mayor a menor</option>
                        <option value="name">Nombre A-Z</option>
                        <option value="newest">Nuevos primero</option>
                    </flux:select>
                </div>

                <!-- Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @forelse($products as $product)
                    <div class="group relative flex flex-col bg-white rounded-2xl border border-zinc-100 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden">
                        <!-- Image -->
                        <div class="relative aspect-[4/5] overflow-hidden bg-zinc-100">
                            <a href="{{ route('store.product.detail', $product->slug) }}" wire:navigate>
                                <img src="{{ $product->imagen_principal ?? 'https://via.placeholder.com/400x500?text=Producto' }}" alt="{{ $product->nombre }}" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" />
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

                            <!-- Quick Add & View Detail -->
                            <div class="absolute inset-x-0 bottom-0 p-4 opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 z-10 pointer-events-none">
                                <div class="flex gap-2 w-full bg-white/90 backdrop-blur-sm p-1 rounded-xl shadow-lg pointer-events-auto">
                                    <flux:button href="{{ route('store.product.detail', $product->slug) }}" wire:navigate variant="subtle" class="flex-1 !px-2">
                                        <svg class="w-4 h-4 mr-1 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Detalle
                                    </flux:button>
                                    <flux:button wire:click.prevent="addToCart({{ $product->id }})" variant="primary" class="flex-1 !px-2">
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
                            <h3 class="text-base font-semibold text-zinc-900 mb-2 line-clamp-2">
                                <a href="{{ route('store.product.detail', $product->slug) }}" wire:navigate>
                                    <span aria-hidden="true" class="absolute inset-0"></span>
                                    {{ $product->nombre }}
                                </a>
                            </h3>
                            <div class="mt-auto flex items-center gap-2">
                                @if($product->tiene_descuento)
                                    <p class="text-lg font-bold text-red-600">${{ number_format($product->precio_oferta, 2) }}</p>
                                    <p class="text-sm text-zinc-400 line-through">${{ number_format($product->precio, 2) }}</p>
                                @else
                                    <p class="text-lg font-bold text-zinc-900">${{ number_format($product->precio, 2) }}</p>
                                @endif
                            </div>
                            @if($product->stock <= 5 && $product->stock > 0)
                                <p class="text-xs text-orange-500 mt-1">¡Solo {{ $product->stock }} disponibles!</p>
                            @elseif($product->stock === 0)
                                <p class="text-xs text-red-500 mt-1 font-medium">Agotado</p>
                            @endif
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
    </div>
</div>
