{{-- Product Search + Cart Partial --}}
{{-- Expects: $products, $items, $productSearch, $addItemCantidad --}}

<div class="rounded-2xl bg-white shadow-sm overflow-hidden">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 px-5 py-4 border-b border-amber-100">
        <div class="flex items-center gap-2">
            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-100 text-amber-600">
                <iconify-icon icon="heroicons:shopping-cart-solid" class="h-4 w-4"></iconify-icon>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-800">Productos</h3>
                <p class="text-xs text-gray-500">Busca y agrega productos al carrito</p>
            </div>
            <div class="ml-auto">
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                    <iconify-icon icon="heroicons:square-3-stack-3d-solid" class="h-3 w-3"></iconify-icon>
                    {{ count($items) }} {{ count($items) === 1 ? 'item' : 'items' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Search Area --}}
    <div class="p-5 border-b border-gray-100">
        <div class="flex gap-3">
            {{-- Product Search Input --}}
            <div class="flex-1 relative">
                <div class="relative">
                    <iconify-icon icon="heroicons:magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"></iconify-icon>
                    <input
                        wire:model.live.debounce.300ms="productSearch"
                        type="text"
                        placeholder="Buscar por nombre, SKU, categoría o marca..."
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-9 pr-4 py-2.5 text-sm placeholder-gray-400 focus:border-amber-300 focus:bg-white focus:ring-2 focus:ring-amber-100 focus:outline-none transition-all"
                    />
                    @if($productSearch)
                        <button wire:click="$set('productSearch', '')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <iconify-icon icon="heroicons:x-mark" class="h-4 w-4"></iconify-icon>
                        </button>
                    @endif
                </div>

                {{-- Search Results Dropdown --}}
                @if($productSearch && count($products) > 0)
                    <div class="absolute z-30 mt-2 w-full rounded-xl border border-gray-200 bg-white shadow-xl max-h-80 overflow-y-auto">
                        <div class="p-2">
                            @foreach($products as $p)
                                <button
                                    wire:click="$set('selectedProductId', {{ $p->id }}); addItem()"
                                    class="group w-full flex items-center gap-3 rounded-lg p-2.5 text-left hover:bg-amber-50 transition-colors border border-transparent hover:border-amber-200"
                                >
                                    {{-- Product Image --}}
                                    <div class="flex-shrink-0 w-12 h-12 rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                                        @if($p->imagen_principal)
                                            <img src="{{ asset('storage/' . $p->imagen_principal) }}" alt="{{ $p->nombre }}" class="w-full h-full object-cover" />
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                <iconify-icon icon="heroicons:photo" class="h-5 w-5"></iconify-icon>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Product Info --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-gray-800 truncate group-hover:text-amber-700">{{ $p->nombre }}</span>
                                            @if($p->tiene_descuento)
                                                <span class="flex-shrink-0 inline-flex items-center rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-600">-{{ $p->porcentaje_descuento }}%</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[11px] text-gray-400 font-mono">{{ $p->sku }}</span>
                                            @if($p->category)
                                                <span class="text-[11px] text-gray-400">·</span>
                                                <span class="text-[11px] text-gray-400">{{ $p->category->nombre }}</span>
                                            @endif
                                            @if($p->brand)
                                                <span class="text-[11px] text-gray-400">·</span>
                                                <span class="text-[11px] text-gray-400">{{ $p->brand->nombre }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Price + Stock --}}
                                    <div class="flex-shrink-0 text-right">
                                        <div class="text-sm font-bold text-amber-600">${{ number_format($p->precio_final, 2) }}</div>
                                        @if($p->tiene_descuento)
                                            <div class="text-[10px] text-gray-400 line-through">${{ number_format($p->precio, 2) }}</div>
                                        @endif
                                        <div class="flex items-center gap-1 justify-end mt-0.5">
                                            <div class="w-1.5 h-1.5 rounded-full {{ $p->stock_total > 0 ? ($p->stock_total <= 5 ? 'bg-amber-400' : 'bg-emerald-400') : 'bg-red-400' }}"></div>
                                            <span class="text-[10px] text-gray-400">{{ $p->stock_total > 0 ? $p->stock_total . ' disp.' : 'Agotado' }}</span>
                                        </div>
                                    </div>

                                    {{-- Add Icon --}}
                                    <div class="flex-shrink-0 w-7 h-7 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @elseif($productSearch && count($products) === 0)
                    <div class="absolute z-30 mt-2 w-full rounded-xl border border-gray-200 bg-white shadow-lg p-6 text-center">
                        <iconify-icon icon="heroicons:magnifying-glass" class="h-8 w-8 text-gray-300 mx-auto mb-2"></iconify-icon>
                        <p class="text-sm text-gray-500">No se encontraron productos para "<span class="font-medium">{{ $productSearch }}</span>"</p>
                        <p class="text-xs text-gray-400 mt-1">Intenta con otro nombre, SKU o categoría</p>
                    </div>
                @endif
            </div>

            {{-- Quantity Selector --}}
            <div class="flex-shrink-0">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Cantidad</label>
                <div class="flex items-center rounded-xl border border-gray-200 bg-gray-50 overflow-hidden">
                    <button wire:click="$set('addItemCantidad', Math.max(1, {{ $addItemCantidad }} - 1))" class="px-2.5 py-2 text-gray-500 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                        <iconify-icon icon="heroicons:minus" class="h-3.5 w-3.5"></iconify-icon>
                    </button>
                    <input wire:model="addItemCantidad" type="number" min="1" class="w-12 border-0 bg-transparent text-center text-sm font-semibold text-gray-800 focus:ring-0 focus:outline-none" />
                    <button wire:click="$set('addItemCantidad', {{ $addItemCantidad }} + 1)" class="px-2.5 py-2 text-gray-500 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                        <iconify-icon icon="heroicons:plus" class="h-3.5 w-3.5"></iconify-icon>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Cart Items --}}
    @if(count($items) > 0)
        <div class="divide-y divide-gray-100">
            @foreach($items as $i => $item)
                <div class="px-5 py-4 hover:bg-gray-50/50 transition-colors group" wire:key="item-{{ $i }}-{{ $item['product_id'] ?? 'custom' }}">
                    <div class="flex items-start gap-4">
                        {{-- Product Image --}}
                        <div class="flex-shrink-0 w-14 h-14 rounded-xl overflow-hidden bg-gray-100 border border-gray-200 shadow-sm">
                            @if(!empty($item['imagen']))
                                <img src="{{ asset('storage/' . $item['imagen']) }}" alt="{{ $item['nombre'] }}" class="w-full h-full object-cover" />
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <iconify-icon icon="heroicons:square-3-stack-3d-solid" class="h-6 w-6"></iconify-icon>
                                </div>
                            @endif
                        </div>

                        {{-- Product Details --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 truncate">{{ $item['nombre'] }}</h4>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[11px] text-gray-400 font-mono">{{ $item['sku'] ?? 'N/A' }}</span>
                                        @if(!empty($item['categoria']))
                                            <span class="text-[11px] text-gray-400">·</span>
                                            <span class="text-[11px] text-gray-400">{{ $item['categoria'] }}</span>
                                        @endif
                                    </div>
                                </div>
                                <button wire:click="removeItem({{ $i }})" class="flex-shrink-0 w-7 h-7 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-all opacity-0 group-hover:opacity-100">
                                    <iconify-icon icon="heroicons:trash" class="h-4 w-4"></iconify-icon>
                                </button>
                            </div>

                            <div class="flex items-center justify-between mt-3 gap-4">
                                {{-- Quantity Controls --}}
                                <div class="flex items-center rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm">
                                    <button wire:click="decrementItem({{ $i }})" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                        <iconify-icon icon="heroicons:minus" class="h-3 w-3"></iconify-icon>
                                    </button>
                                    <input
                                        type="number"
                                        wire:model.live="items.{{ $i }}.cantidad"
                                        min="1"
                                        class="w-12 h-8 border-x border-gray-200 bg-white text-center text-sm font-semibold text-gray-800 focus:ring-0 focus:outline-none"
                                    />
                                    <button wire:click="incrementItem({{ $i }})" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                        <iconify-icon icon="heroicons:plus" class="h-3 w-3"></iconify-icon>
                                    </button>
                                </div>

                                {{-- Unit Price --}}
                                <div class="text-center">
                                    <div class="text-[10px] text-gray-400 uppercase tracking-wider">Precio</div>
                                    <div class="text-sm font-medium text-gray-700">${{ number_format($item['precio_unitario'], 2) }}</div>
                                </div>

                                {{-- Per-item Discount --}}
                                <div class="flex items-center gap-1">
                                    <div class="text-center">
                                        <div class="text-[10px] text-gray-400 uppercase tracking-wider">Desc.</div>
                                        <input
                                            type="number"
                                            wire:model.live="items.{{ $i }}.descuento"
                                            min="0"
                                            step="0.01"
                                            class="w-20 h-8 rounded-lg border border-gray-200 bg-white text-center text-sm text-gray-700 focus:border-amber-300 focus:ring-1 focus:ring-amber-100 focus:outline-none"
                                            placeholder="0.00"
                                        />
                                    </div>
                                </div>

                                {{-- Subtotal --}}
                                <div class="text-right">
                                    <div class="text-[10px] text-gray-400 uppercase tracking-wider">Subtotal</div>
                                    <div class="text-base font-bold text-gray-900">
                                        ${{ number_format(($item['cantidad'] * $item['precio_unitario']) - ($item['descuento'] ?? 0) + ($item['impuesto'] ?? 0), 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Cart Footer Totals (inline) --}}
        <div class="bg-gray-50 px-5 py-3 border-t border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span>{{ count($items) }} {{ count($items) === 1 ? 'producto' : 'productos' }} en el carrito</span>
                <button wire:click="$set('items', [])" class="text-red-400 hover:text-red-600 font-medium transition-colors">
                    <iconify-icon icon="heroicons:trash" class="h-3 w-3 inline"></iconify-icon> Vaciar carrito
                </button>
            </div>
        </div>
    @else
        {{-- Empty Cart State --}}
        <div class="px-5 py-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <iconify-icon icon="heroicons:shopping-cart" class="h-8 w-8 text-gray-300"></iconify-icon>
            </div>
            <h4 class="text-sm font-semibold text-gray-600">Carrito vacío</h4>
            <p class="text-xs text-gray-400 mt-1">Busca un producto arriba y haz clic para agregarlo</p>
        </div>
    @endif
</div>
