<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Wishlist;
use App\Models\Review;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    public Product $product;
    public int $quantity = 1;
    public bool $addedToCart = false;
    public ?int $selectedVariantId = null;
    public bool $notifySubmitted = false;
    public array $selectedAttributes = []; // [attribute_id => attribute_value_id]
    public string $activeTab = 'description'; // 'description' | 'reviews'
    public int $reviewRating = 5;
    public string $reviewTitulo = '';
    public string $reviewComentario = '';
    public ?string $reviewError = null;
    public ?string $reviewSuccess = null;

    public function rendering($view)
    {
        $title = $this->product->meta_title ?: $this->product->nombre . ' - Abastos Los Trinis';
        $description = $this->product->meta_description ?: ($this->product->descripcion_corta ?: 'Compra ' . $this->product->nombre . ' en Abastos Los Trinis al mejor precio y con la mejor calidad.');
        
        $image = $this->product->imagen_principal_url ?: asset('images/logo.png');

        $view->title($title)
             ->layoutData([
                 'title' => $title,
                 'description' => $description,
                 'og_image' => $image,
                 'og_type' => 'product',
             ]);
    }

    public function toggleCompare(): void
    {
        $compare = session('compare_products', []);
        if (in_array($this->product->id, $compare)) {
            $compare = array_values(array_diff($compare, [$this->product->id]));
        } else {
            if (count($compare) >= 4) return;
            $compare[] = $this->product->id;
        }
        session(['compare_products' => $compare]);
    }

    public function getIsComparingProperty(): bool
    {
        return in_array($this->product->id, session('compare_products', []));
    }

    public function getIsWishlistedProperty(): bool
    {
        if (!Auth::check()) return false;
        $customer = Customer::where('user_id', Auth::id())->first();
        if (!$customer) return false;
        return Wishlist::where('customer_id', $customer->id)
            ->where('product_id', $this->product->id)
            ->exists();
    }

    public function toggleWishlist(): void
    {
        if (!Auth::check()) {
            $this->redirect('/acceso', navigate: true);
            return;
        }

        $customer = Customer::where('user_id', Auth::id())->first();
        if (!$customer) return;

        Wishlist::toggle($customer->id, $this->product->id, $this->selectedVariantId);
    }

    public function mount(Product $product)
    {
        $this->product = $product->load([
            'category', 'brand', 'images',
            'variants.attributeValues.attribute',
        ]);

        // Track recently viewed
        $viewed = session('recently_viewed', []);
        $viewed = array_values(array_diff($viewed, [$this->product->id]));
        array_unshift($viewed, $this->product->id);
        session(['recently_viewed' => array_slice($viewed, 0, 20)]);

        // Auto-select first available variant
        if ($this->product->tiene_variantes && $this->product->variants->count() > 0) {
            $firstAvailable = $this->product->variants->firstWhere('stock', '>', 0)
                ?? $this->product->variants->first();
            $this->selectVariant($firstAvailable->id);
        }
    }

    public function selectVariant(int $variantId): void
    {
        $variant = $this->product->variants->firstWhere('id', $variantId);
        if ($variant) {
            $this->selectedVariantId = $variantId;
            // Sync selected attributes
            $this->selectedAttributes = $variant->attributeValues
                ->mapWithKeys(fn($av) => [$av->pivot->attribute_id => $av->id])
                ->toArray();
            $this->quantity = 1;
        }
    }

    public function selectAttributeValue(int $attributeId, int $valueId): void
    {
        $this->selectedAttributes[$attributeId] = $valueId;

        // Find variant matching ALL selected attributes
        $matching = $this->product->variants->filter(function ($v) {
            foreach ($this->selectedAttributes as $attrId => $valId) {
                if (!$v->attributeValues->contains(fn($av) => $av->id == $valId && $av->pivot->attribute_id == $attrId)) {
                    return false;
                }
            }
            return true;
        });

        if ($matching->count() === 1) {
            $this->selectedVariantId = $matching->first()->id;
            $this->quantity = 1;
        }
    }

    public function isAttributeValueSelected(int $attributeId, int $valueId): bool
    {
        return ($this->selectedAttributes[$attributeId] ?? null) == $valueId;
    }

    public function isAttributeValueAvailable(int $attributeId, int $valueId): bool
    {
        // Check if any variant with this value + currently selected other attributes has stock
        return $this->product->variants->contains(function ($v) use ($attributeId, $valueId) {
            $hasValue = $v->attributeValues->contains(fn($av) => $av->id == $valueId && $av->pivot->attribute_id == $attributeId);
            if (!$hasValue) return false;
            // Check other selected attributes
            foreach ($this->selectedAttributes as $aId => $vId) {
                if ($aId == $attributeId) continue;
                if (!$v->attributeValues->contains(fn($av) => $av->id == $vId && $av->pivot->attribute_id == $aId)) {
                    return false;
                }
            }
            return $v->stock > 0;
        });
    }

    #[Computed]
    public function selectedVariant()
    {
        if (!$this->selectedVariantId) return null;
        return $this->product->variants->firstWhere('id', $this->selectedVariantId);
    }

    #[Computed]
    public function currentStock(): int
    {
        if ($this->product->tiene_variantes && $this->selectedVariant) {
            return $this->selectedVariant->stock;
        }
        return $this->product->stock;
    }

    #[Computed]
    public function currentPrice(): float
    {
        if ($this->product->tiene_variantes && $this->selectedVariant) {
            return $this->selectedVariant->precio_final;
        }
        return $this->product->precio_final;
    }

    #[Computed]
    public function currentOriginalPrice(): ?float
    {
        if ($this->product->tiene_variantes && $this->selectedVariant) {
            if ($this->selectedVariant->precio_oferta && $this->selectedVariant->precio_oferta < $this->selectedVariant->precio) {
                return $this->selectedVariant->precio;
            }
            return null;
        }
        return $this->product->tiene_descuento ? $this->product->precio : null;
    }

    public function incrementQuantity()
    {
        if ($this->quantity < $this->currentStock) {
            $this->quantity++;
        }
    }

    public function decrementQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart()
    {
        if ($this->currentStock <= 0) return;

        $cartService = app(CartService::class);
        $cartService->addItem($this->product, $this->quantity, $this->selectedVariantId);

        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: '¡Producto agregado al carrito!', type: 'success');
        $this->addedToCart = true;
        $this->js('setTimeout(() => { $wire.addedToCart = false }, 3000)');
    }

    public function notifyWhenAvailable(): void
    {
        $this->notifySubmitted = true;
        // TODO: Store notification in DB or send email
    }

    public function submitReview(): void
    {
        $this->reviewError = null;
        $this->reviewSuccess = null;

        if (!Auth::check()) {
            $this->reviewError = 'Debes iniciar sesión para dejar una reseña.';
            return;
        }

        $customer = Customer::where('user_id', Auth::id())->first();
        if (!$customer) {
            $this->reviewError = 'No se encontró tu perfil de cliente.';
            return;
        }

        $existing = Review::where('product_id', $this->product->id)
            ->where('customer_id', $customer->id)
            ->first();

        if ($existing) {
            $this->reviewError = 'Ya has dejado una reseña para este producto.';
            return;
        }

        if ($this->reviewRating < 1 || $this->reviewRating > 5) {
            $this->reviewError = 'Selecciona una calificación entre 1 y 5 estrellas.';
            return;
        }

        Review::create([
            'product_id'    => $this->product->id,
            'customer_id'   => $customer->id,
            'rating'        => $this->reviewRating,
            'titulo'        => $this->reviewTitulo ?: null,
            'comentario'    => $this->reviewComentario ?: null,
            'verificado'    => false,
            'aprobado'      => false,
        ]);

        $this->reviewTitulo = '';
        $this->reviewComentario = '';
        $this->reviewRating = 5;
        $this->reviewSuccess = '¡Gracias! Tu reseña ha sido enviada y será publicada tras ser aprobada.';
    }

    public function with(): array
    {
        $relatedProducts = Product::with('category', 'brand')
            ->where('status', true)
            ->where('id', '!=', $this->product->id)
            ->when($this->product->category_id, fn($q) => $q->where('category_id', $this->product->category_id))
            ->limit(4)
            ->get();

        // If not enough related, fill with featured
        if ($relatedProducts->count() < 4) {
            $ids = $relatedProducts->pluck('id')->merge([$this->product->id]);
            $more = Product::with('category', 'brand')
                ->where('status', true)
                ->whereNotIn('id', $ids)
                ->where('destacado', true)
                ->limit(4 - $relatedProducts->count())
                ->get();
            $relatedProducts = $relatedProducts->merge($more);
        }

        // Build all images list: main image + additional images
        $allImages = collect();
        if ($this->product->imagen_principal) {
            $allImages->push(['url' => $this->product->imagen_principal_url, 'alt' => $this->product->nombre]);
        }
        foreach ($this->product->images as $img) {
            $ruta = $img->ruta;
            if (\Illuminate\Support\Str::startsWith($ruta, ['http://', 'https://'])) {
                $url = $ruta;
            } elseif (\Illuminate\Support\Str::startsWith($ruta, ['app/', 'build/'])) {
                $url = asset($ruta);
            } else {
                $url = asset('storage/' . $ruta);
            }
            $allImages->push(['url' => $url, 'alt' => $img->alt_text ?? $this->product->nombre]);
        }

        $variantImagesMap = $this->product->variants
            ->filter(fn($v) => !empty($v->imagen))
            ->mapWithKeys(fn($v) => [$v->id => $v->imagen_url])
            ->toArray();

        return [
            'relatedProducts' => $relatedProducts,
            'allImages' => $allImages,
            'variantImagesMap' => $variantImagesMap,
            'approvedReviews' => Review::with('customer')
                ->where('product_id', $this->product->id)
                ->where('aprobado', true)
                ->latest()
                ->get(),
            'ratingStats' => [
                'promedio' => $this->product->rating_promedio,
                'total'    => $this->product->reviews_count,
                'dist'     => collect([5,4,3,2,1])->mapWithKeys(fn($s) => [
                    $s => Review::where('product_id', $this->product->id)
                        ->where('aprobado', true)
                        ->where('rating', $s)
                        ->count()
                ])->toArray(),
            ],
            'hasReviewed' => Auth::check()
                ? (function() {
                    $c = Customer::where('user_id', Auth::id())->first();
                    return $c ? Review::where('product_id', $this->product->id)->where('customer_id', $c->id)->exists() : false;
                })()
                : false,
            'recentlyViewed' => (function() {
                $ids = session('recently_viewed', []);
                if (empty($ids)) return collect();
                return Product::with('category', 'brand')
                    ->where('status', true)
                    ->whereIn('id', $ids)
                    ->where('id', '!=', $this->product->id)
                    ->limit(6)
                    ->get();
            })(),
        ];
    }
};
?>

<div>
    <!-- JSON-LD Datos Estructurados para Google (Product) -->
    @push('structured-data')
    <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "Product",
      "name": "{{ $product->nombre }}",
      "image": "{{ $product->imagen_principal_url ?: asset('images/logo.png') }}",
      "description": "{{ str_replace('"', '\"', strip_tags($product->meta_description ?: $product->descripcion_corta ?: $product->descripcion)) }}",
      "sku": "{{ $product->sku }}",
      @if($product->brand)
      "brand": {
        "@type": "Brand",
        "name": "{{ $product->brand->nombre }}"
      },
      @endif
      "offers": {
        "@type": "Offer",
        "url": "{{ url()->current() }}",
        "priceCurrency": "USD",
        "price": "{{ $product->precio_final }}",
        "priceValidUntil": "{{ date('Y-m-d', strtotime('+1 year')) }}",
        "itemCondition": "https://schema.org/NewCondition",
        "availability": "{{ $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}"
      }
    }
    </script>
    @endpush

    <!-- Breadcrumb -->
    <div class="bg-white border-b border-zinc-100">
        <nav aria-label="Breadcrumb" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <ol class="flex items-center space-x-3 py-4 text-sm">
                <li><a href="/" wire:navigate class="text-zinc-500 hover:text-zinc-900 transition-colors">Inicio</a></li>
                @if($product->category)
                    <li class="flex items-center gap-3">
                        <svg class="h-4 w-4 text-zinc-300" fill="currentColor" viewBox="0 0 20 20"><path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z"/></svg>
                        <a href="/catalogo/{{ $product->category->slug }}" wire:navigate class="text-zinc-500 hover:text-zinc-900 transition-colors">{{ $product->category->nombre }}</a>
                    </li>
                @endif
                <li class="flex items-center gap-3">
                    <svg class="h-4 w-4 text-zinc-300" fill="currentColor" viewBox="0 0 20 20"><path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z"/></svg>
                    <span class="text-zinc-900 font-medium" aria-current="page">{{ $product->nombre }}</span>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Product Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {{-- Success message --}}
        @if($addedToCart)
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span class="font-medium">¡Producto agregado al carrito exitosamente!</span>
                <a href="/carrito" wire:navigate class="ml-auto text-sm font-semibold text-emerald-600 hover:text-emerald-800 transition-colors">Ver carrito →</a>
            </div>
        @endif

        <div x-data="{
            activeImg: 0,
            zooming: false,
            images: {{ Js::from($allImages->pluck('url')->values()) }},
            variantImages: {{ Js::from($variantImagesMap) }},
            init() {
                this.$watch('$wire.selectedVariantId', (id) => {
                    if (id && this.variantImages[id]) {
                        let url = this.variantImages[id];
                        let idx = this.images.indexOf(url);
                        if (idx !== -1) {
                            this.activeImg = idx;
                        } else {
                            this.images.push(url);
                            this.activeImg = this.images.length - 1;
                        }
                    }
                });
            }
        }" class="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16">

            {{-- GALLERY --}}
            <div class="flex flex-col gap-4">
                {{-- Main Image with Zoom --}}
                <div
                    class="relative aspect-[4/5] w-full overflow-hidden rounded-2xl bg-zinc-100 cursor-zoom-in"
                    @mousemove="zooming = true; let rect = $el.getBoundingClientRect(); $el.style.setProperty('--zoom-x', ((event.clientX - rect.left) / rect.width * 100) + '%'); $el.style.setProperty('--zoom-y', ((event.clientY - rect.top) / rect.height * 100) + '%')"
                    @mouseleave="zooming = false"
                >
                    <template x-for="(img, idx) in images" :key="idx">
                        <img
                            x-show="activeImg === idx"
                            x-transition:enter="transition-opacity duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            :src="img"
                            :alt="'Imagen {{ $product->nombre }} ' + (idx + 1)"
                            class="w-full h-full object-cover object-center"
                            :class="{ '!hidden': zooming }"
                            loading="lazy"
                        />
                    </template>
                    {{-- Zoomed overlay --}}
                    <div
                        x-show="zooming"
                        class="absolute inset-0 pointer-events-none"
                        :style="`background-image: url('${images[activeImg]}'); background-size: 200%; background-position: var(--zoom-x) var(--zoom-y);`"
                    ></div>
                    {{-- Badges --}}
                    <div class="absolute top-4 left-4 flex flex-col gap-2">
                        @if($product->nuevo)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-500 text-white shadow-sm">Nuevo</span>
                        @endif
                        @if($product->tiene_descuento)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500 text-white shadow-sm">-{{ $product->porcentaje_descuento }}%</span>
                        @endif
                    </div>
                </div>

                {{-- Thumbnails --}}
                @if($allImages->count() > 1)
                <div class="flex gap-3 overflow-x-auto pb-1">
                    @foreach($allImages as $idx => $img)
                        <button
                            @click="activeImg = {{ $idx }}"
                            :class="activeImg === {{ $idx }} ? 'ring-2 ring-indigo-500 ring-offset-2' : 'ring-1 ring-zinc-200 hover:ring-zinc-400'"
                            class="relative h-20 w-20 flex-shrink-0 rounded-xl overflow-hidden transition-all focus:outline-none"
                            type="button"
                        >
                            <img src="{{ $img['url'] }}" alt="{{ $img['alt'] }}" class="w-full h-full object-cover" />
                        </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- PRODUCT INFO --}}
            <div class="mt-10 lg:mt-0 space-y-6">
                {{-- Title & SKU --}}
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-zinc-900">{{ $product->nombre }}</h1>
                    <div class="mt-2 flex items-center gap-3 flex-wrap">
                        <p class="text-sm text-zinc-500">SKU: <span class="font-medium text-zinc-700">{{ $product->sku }}</span></p>
                        @if($product->brand)
                            <span class="text-zinc-300">·</span>
                            <p class="text-sm text-zinc-500">{{ $product->brand->nombre }}</p>
                        @endif
                    </div>
                </div>

                {{-- Price (reactive to variant) --}}
                <div class="flex items-center gap-3" x-data>
                    @if($product->tiene_variantes)
                        {{-- Dynamic price via Alpine --}}
                        <template x-if="true">
                            <div class="flex items-center gap-3">
                                @php
                                    $variantsJson = $product->variants->map(fn($v) => [
                                        'id' => $v->id,
                                        'precio' => money_product($v, $v->precio_oferta && $v->precio_oferta < $v->precio),
                                        'original' => ($v->precio_oferta && $v->precio_oferta < $v->precio) ? money_product($v, false) : null,
                                    ])->values();
                                @endphp
                                <span class="text-zinc-900 text-3xl font-bold" :class="$wire.selectedVariantId ? ({{ json_encode($variantsJson) }}.find(v => v.id === $wire.selectedVariantId)?.original ? 'text-red-600' : 'text-zinc-900') : 'text-zinc-900'"
                                      x-text="$wire.selectedVariantId ? ({{ json_encode($variantsJson) }}.find(v => v.id === $wire.selectedVariantId)?.precio || '{{ money_product($product, $product->tiene_descuento) }}') : '{{ money_product($product, $product->tiene_descuento) }}'"></span>
                                <template x-if="{{ json_encode($variantsJson) }}.find(v => v.id === $wire.selectedVariantId)?.original">
                                    <span class="text-xl text-zinc-400 line-through" x-text="{{ json_encode($variantsJson) }}.find(v => v.id === $wire.selectedVariantId)?.original"></span>
                                </template>
                            </div>
                        </template>
                    @else
                        @if($product->tiene_descuento)
                            <p class="text-3xl font-bold text-red-600">{{ money_product($product, true) }}</p>
                            <p class="text-xl text-zinc-400 line-through">{{ money_product($product, false) }}</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                Ahorras {{ $product->porcentaje_descuento }}%
                            </span>
                        @else
                            <p class="text-3xl font-bold text-zinc-900">{{ money_product($product, false) }}</p>
                        @endif
                    @endif
                </div>

                {{-- Description --}}
                @if($product->descripcion_corta || $product->descripcion)
                <div class="text-sm text-zinc-600 space-y-2 border-t border-zinc-100 pt-6">
                    @if($product->descripcion_corta)<p class="font-medium text-zinc-800">{{ $product->descripcion_corta }}</p>@endif
                    @if($product->descripcion)<p>{{ $product->descripcion }}</p>@endif
                </div>
                @endif

                {{-- VARIANT SELECTOR --}}
                @if($product->tiene_variantes && $product->variants->count() > 0)
                <div class="border-t border-zinc-100 pt-6 space-y-5">
                    @php
                        // Group attribute values by attribute (preserve attribute object)
                        $attributeGroups = $product->variants
                            ->flatMap(fn($v) => $v->attributeValues->map(fn($av) => [
                                'attribute_id' => $av->pivot->attribute_id,
                                'value' => $av,
                            ]))
                            ->unique(fn($item) => $item['attribute_id'] . '-' . $item['value']->id)
                            ->groupBy(fn($item) => $item['attribute_id']);
                    @endphp

                    @foreach($attributeGroups as $attributeId => $items)
                        @php
                            $attrName = $items->first()['value']->attribute->nombre;
                            $values = $items->pluck('value');
                        @endphp
                        <div>
                            <p class="text-sm font-semibold text-zinc-800 mb-2">{{ $attrName }}</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($values as $value)
                                    @php
                                        $isSelected = $this->isAttributeValueSelected($attributeId, $value->id);
                                        $isAvailable = $this->isAttributeValueAvailable($attributeId, $value->id);
                                        $isColor = $value->attribute->tipo === 'color' || !empty($value->codigo_color);
                                    @endphp
                                    <button
                                        type="button"
                                        wire:click="selectAttributeValue({{ $attributeId }}, {{ $value->id }})"
                                        @disabled(!$isAvailable)
                                        class="
                                            relative flex items-center justify-center
                                            {{ $isColor ? 'w-10 h-10 rounded-full' : 'min-w-[3rem] px-4 py-2 rounded-xl text-sm font-medium' }}
                                            border-2 transition-all
                                            @if($isSelected) ring-2 ring-indigo-500 ring-offset-1 border-indigo-500 @endif
                                            @if(!$isAvailable)
                                                border-zinc-100 bg-zinc-50 text-zinc-300 cursor-not-allowed
                                            @elseif(!$isSelected)
                                                border-zinc-200 hover:border-indigo-400 cursor-pointer
                                            @endif
                                        "
                                        @if($isColor && $value->codigo_color)
                                            style="background-color: {{ $value->codigo_color }}"
                                            title="{{ $value->valor }}"
                                        @endif
                                    >
                                        @if(!$isColor)
                                            <span class="{{ !$isAvailable ? 'line-through' : '' }}">{{ $value->valor }}</span>
                                        @endif
                                        @if($isSelected && !$isColor)
                                            <svg class="w-3.5 h-3.5 ml-1 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        @endif
                                        @if(!$isAvailable)
                                            <span class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                                <span class="block w-full h-px bg-zinc-400 rotate-45"></span>
                                            </span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    {{-- Selected variant info --}}
                    @if($this->selectedVariant)
                    <div class="bg-zinc-50 rounded-xl px-4 py-3 text-sm text-zinc-600 space-y-1">
                        <p><span class="font-medium text-zinc-800">Variante:</span> {{ $this->selectedVariant->nombre_compuesto }}</p>
                        <p>
                            <span class="font-medium text-zinc-800">Stock:</span>
                            @if($this->selectedVariant->stock > 0)
                                <span class="text-emerald-600">{{ $this->selectedVariant->stock }} disponibles</span>
                            @else
                                <span class="text-red-500">Agotado</span>
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
                @endif

                {{-- QUANTITY + ADD TO CART --}}
                <div class="border-t border-zinc-100 pt-6 space-y-4">
                    {{-- Quantity selector --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-zinc-800">Cantidad</span>
                            @if($this->currentStock > 0 && $this->currentStock <= 5)
                                <span class="text-sm text-orange-500 font-medium">¡Solo {{ $this->currentStock }} disponibles!</span>
                            @elseif($this->currentStock > 5)
                                <span class="text-sm text-emerald-600 font-medium">En stock</span>
                            @else
                                <span class="text-sm text-red-500 font-medium">Agotado</span>
                            @endif
                        </div>
                        <div class="flex items-center border border-zinc-300 rounded-xl w-fit">
                            <button type="button" wire:click="decrementQuantity" class="w-10 h-10 flex items-center justify-center text-zinc-500 hover:text-zinc-900 rounded-l-xl hover:bg-zinc-50 transition-colors" :disabled="$wire.quantity <= 1">−</button>
                            <span class="w-14 text-center text-zinc-900 font-semibold select-none">{{ $this->quantity }}</span>
                            <button type="button" wire:click="incrementQuantity" class="w-10 h-10 flex items-center justify-center text-zinc-500 hover:text-zinc-900 rounded-r-xl hover:bg-zinc-50 transition-colors" :disabled="$wire.quantity >= $wire.currentStock">+</button>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-3">
                        @if($this->currentStock > 0)
                            <button
                                wire:click="addToCart"
                                class="flex-1 bg-indigo-600 text-white font-semibold py-4 px-8 rounded-xl hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all shadow-lg shadow-indigo-200 flex items-center justify-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                Agregar al Carrito
                            </button>
                        @else
                            <button
                                wire:click="notifyWhenAvailable"
                                class="flex-1 bg-zinc-900 text-white font-semibold py-4 px-8 rounded-xl hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2 transition-all flex items-center justify-center gap-2"
                            >
                                @if($notifySubmitted)
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    ¡Te avisaremos!
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    Notifícame cuando haya stock
                                @endif
                            </button>
                        @endif

                        <button
                            type="button"
                            wire:click="toggleWishlist"
                            class="w-12 h-12 rounded-xl border border-zinc-200 flex items-center justify-center transition-all flex-shrink-0
                                {{ $this->isWishlisted
                                    ? 'text-rose-500 border-rose-200 bg-rose-50'
                                    : 'text-zinc-400 hover:text-rose-500 hover:border-rose-200 hover:bg-rose-50' }}"
                        >
                            <svg class="w-5 h-5" fill="{{ $this->isWishlisted ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Trust badges --}}
                <div class="border-t border-zinc-100 pt-6 grid grid-cols-2 gap-4">
                    <div class="flex items-center gap-3 text-sm text-zinc-600 bg-zinc-50 rounded-xl px-4 py-3">
                        <svg class="w-5 h-5 text-zinc-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Envío gratis +$100</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-zinc-600 bg-zinc-50 rounded-xl px-4 py-3">
                        <svg class="w-5 h-5 text-zinc-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>30 días de devolución</span>
                    </div>
                    <button wire:click="toggleCompare" class="flex items-center gap-3 text-sm rounded-xl px-4 py-3 transition-colors col-span-2 {{ $this->isComparing ? 'bg-indigo-50 text-indigo-700 font-medium border border-indigo-200' : 'bg-zinc-50 text-zinc-600 hover:bg-zinc-100 border border-transparent' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ $this->isComparing ? 'text-indigo-500' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        {{ $this->isComparing ? 'Quitar de comparación' : 'Agregar a comparación' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- DESCRIPTION & REVIEWS TABS --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <div x-data="{ tab: @entangle('activeTab') }">
            {{-- Tab nav --}}
            <div class="border-b border-zinc-200 flex gap-8">
                <button @click="tab = 'description'"
                    :class="tab === 'description' ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-zinc-500 hover:text-zinc-700'"
                    class="py-4 text-sm border-b-2 transition-colors focus:outline-none">Descripción</button>
                <button @click="tab = 'reviews'"
                    :class="tab === 'reviews' ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-zinc-500 hover:text-zinc-700'"
                    class="py-4 text-sm border-b-2 transition-colors focus:outline-none">
                    Reseñas
                    @if($ratingStats['total'] > 0)
                        <span class="ml-1 bg-zinc-100 text-zinc-600 text-xs font-bold px-2 py-0.5 rounded-full">{{ $ratingStats['total'] }}</span>
                    @endif
                </button>
            </div>

            {{-- Description tab --}}
            <div x-show="tab === 'description'" x-transition.opacity class="py-8 max-w-none">
                @if($product->descripcion)
                    <div class="text-zinc-700 leading-relaxed space-y-4">
                        {!! nl2br(e($product->descripcion)) !!}
                    </div>
                @elseif($product->descripcion_corta)
                    <p class="text-zinc-700 leading-relaxed">{{ $product->descripcion_corta }}</p>
                @else
                    <p class="text-zinc-400 italic">Sin descripción disponible.</p>
                @endif
            </div>

            {{-- Reviews tab --}}
            <div x-show="tab === 'reviews'" x-transition.opacity class="py-8">
                <div class="lg:grid lg:grid-cols-3 lg:gap-12">
                    {{-- Rating summary --}}
                    <div class="mb-8 lg:mb-0">
                        @if($ratingStats['total'] > 0)
                            <div class="bg-zinc-50 rounded-2xl p-6 text-center">
                                <p class="text-5xl font-extrabold text-zinc-900">{{ number_format($ratingStats['promedio'], 1) }}</p>
                                <div class="flex items-center justify-center gap-0.5 mt-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-5 h-5 {{ $i <= round($ratingStats['promedio']) ? 'text-yellow-400' : 'text-zinc-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </div>
                                <p class="text-sm text-zinc-500 mt-1">{{ $ratingStats['total'] }} reseña(s)</p>
                                {{-- Distribution bars --}}
                                <div class="mt-5 space-y-2">
                                    @foreach([5,4,3,2,1] as $star)
                                        @php $count = $ratingStats['dist'][$star] ?? 0; $pct = $ratingStats['total'] > 0 ? ($count / $ratingStats['total'] * 100) : 0; @endphp
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="w-3 text-zinc-500">{{ $star }}</span>
                                            <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            <div class="flex-1 bg-zinc-200 rounded-full h-1.5">
                                                <div class="bg-yellow-400 h-1.5 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="w-6 text-right text-zinc-400">{{ $count }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="bg-zinc-50 rounded-2xl p-6 text-center">
                                <svg class="w-12 h-12 mx-auto text-zinc-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                <p class="text-zinc-500 text-sm">Aún no hay reseñas para este producto.</p>
                            </div>
                        @endif
                    </div>

                    {{-- Review list + form --}}
                    <div class="lg:col-span-2 space-y-8">
                        {{-- Review list --}}
                        @forelse($approvedReviews as $review)
                            <div class="border-b border-zinc-100 pb-6 last:border-0">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm font-bold flex-shrink-0">
                                        {{ $review->autor_iniciales }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-zinc-900">{{ $review->autor_nombre }}</p>
                                        <div class="flex items-center gap-1.5">
                                            <div class="flex gap-0.5">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-zinc-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                @endfor
                                            </div>
                                            <span class="text-xs text-zinc-400">·</span>
                                            <span class="text-xs text-zinc-400">{{ $review->created_at->diffForHumans() }}</span>
                                            @if($review->verificado)
                                                <span class="text-xs bg-emerald-50 text-emerald-600 font-medium px-1.5 py-0.5 rounded">Verificado</span>
                                            @endif
                                        </div>
                                        @if($review->titulo)
                                            <h4 class="mt-3 text-sm font-semibold text-zinc-800">{{ $review->titulo }}</h4>
                                        @endif
                                        @if($review->comentario)
                                            <p class="mt-1 text-sm text-zinc-600 leading-relaxed">{{ $review->comentario }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-zinc-400 text-sm italic">Sé el primero en reseñar este producto.</p>
                        @endforelse

                        {{-- Review form --}}
                        <div class="bg-zinc-50 rounded-2xl p-6 mt-6">
                            <h3 class="font-semibold text-zinc-900 mb-4">Escribe tu reseña</h3>
                            @if($reviewError)
                                <div class="mb-4 bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-3 rounded-xl">{{ $reviewError }}</div>
                            @endif
                            @if($reviewSuccess)
                                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3 rounded-xl">{{ $reviewSuccess }}</div>
                            @endif
                            @if($hasReviewed)
                                <p class="text-sm text-zinc-500 italic">Ya has dejado una reseña para este producto.</p>
                            @elseif(!Auth::check())
                                <p class="text-sm text-zinc-500">
                                    <a href="/acceso" wire:navigate class="text-indigo-600 font-medium hover:text-indigo-800">Inicia sesión</a> para dejar tu reseña.
                                </p>
                            @else
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 mb-2">Calificación</label>
                                        <div class="flex gap-1">
                                            @for($s = 1; $s <= 5; $s++)
                                                <button type="button" wire:click="$set('reviewRating', {{ $s }})" class="focus:outline-none">
                                                    <svg class="w-7 h-7 transition-colors {{ $s <= $reviewRating ? 'text-yellow-400' : 'text-zinc-300 hover:text-yellow-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                </button>
                                            @endfor
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 mb-1">Título (opcional)</label>
                                        <input type="text" wire:model="reviewTitulo" maxlength="120" placeholder="Resumen de tu experiencia" class="w-full rounded-xl border-zinc-300 text-sm focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 mb-1">Comentario (opcional)</label>
                                        <textarea wire:model="reviewComentario" rows="3" maxlength="1000" placeholder="Cuenta tu experiencia con este producto..." class="w-full rounded-xl border-zinc-300 text-sm focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 resize-none"></textarea>
                                    </div>
                                    <button wire:click="submitReview" class="bg-indigo-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-indigo-700 transition-colors text-sm">
                                        Enviar reseña
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RELATED PRODUCTS --}}
    @if($relatedProducts->count() > 0)
    <section class="bg-white border-t border-zinc-100 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-zinc-900 mb-8">También te puede interesar</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($relatedProducts as $rp)
                <a href="{{ route('store.product.detail', $rp->slug) }}" wire:navigate class="group flex flex-col bg-white rounded-2xl border border-zinc-100 shadow-sm hover:shadow-lg transition-all overflow-hidden">
                    <div class="relative aspect-square overflow-hidden bg-zinc-100">
                        <img src="{{ $rp->imagen_principal_url ?? 'https://placehold.co/300' }}" alt="{{ $rp->nombre }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
                        @if($rp->tiene_descuento)
                            <span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">-{{ $rp->porcentaje_descuento }}%</span>
                        @endif
                    </div>
                    <div class="p-4 space-y-1">
                        <p class="text-xs text-zinc-400 uppercase tracking-wider">{{ optional($rp->category)->nombre ?? '' }}</p>
                        <h3 class="text-sm font-semibold text-zinc-900 line-clamp-2 group-hover:text-indigo-600 transition-colors">{{ $rp->nombre }}</h3>
                        <p class="text-base font-bold {{ $rp->tiene_descuento ? 'text-red-600' : 'text-zinc-900' }}">
                            {{ money_product($rp, $rp->tiene_descuento) }}
                        </p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- RECENTLY VIEWED --}}
    @if($recentlyViewed->count() > 0)
    <section class="bg-zinc-50 border-t border-zinc-100 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-zinc-900 mb-8">Vistos Recientemente</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($recentlyViewed as $rv)
                <a href="{{ route('store.product.detail', $rv->slug) }}" wire:navigate class="group flex flex-col bg-white rounded-xl border border-zinc-100 shadow-sm hover:shadow-md transition-all overflow-hidden">
                    <div class="relative aspect-square overflow-hidden bg-zinc-100">
                        <img src="{{ $rv->imagen_principal_url ?? 'https://placehold.co/200' }}" alt="{{ $rv->nombre }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
                        @if($rv->tiene_descuento)
                            <span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">-{{ $rv->porcentaje_descuento }}%</span>
                        @endif
                    </div>
                    <div class="p-3 space-y-0.5">
                        <h3 class="text-xs font-semibold text-zinc-900 line-clamp-2 group-hover:text-indigo-600 transition-colors">{{ $rv->nombre }}</h3>
                        <p class="text-sm font-bold {{ $rv->tiene_descuento ? 'text-red-600' : 'text-zinc-900' }}">
                            {{ money_product($rv, $rv->tiene_descuento) }}
                        </p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif
</div>
