<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Productos</h2>
            <p class="mt-1 text-sm text-gray-500">Gestiona tu inventario y catálogo de productos.</p>
        </div>
        @can('productos.create')
            <a href="{{ route('admin.productos.create') }}" wire:navigate>
                <flux:button variant="primary" class="!bg-indigo-500 hover:!bg-indigo-600">
                    <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon>
                    Nuevo Producto
                </flux:button>
            </a>
        @endcan
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Total</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50">
                    <iconify-icon icon="heroicons:square-3-stack-3d-solid" class="h-5 w-5 text-indigo-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Activos</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50">
                    <iconify-icon icon="heroicons:check-badge-solid" class="h-5 w-5 text-emerald-600"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['active'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Destacados</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50">
                    <iconify-icon icon="heroicons:star-solid" class="h-5 w-5 text-amber-500"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['featured'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Stock Bajo</p>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-red-50">
                    <iconify-icon icon="heroicons:exclamation-triangle-solid" class="h-5 w-5 text-red-500"></iconify-icon>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $stats['low_stock'] }}</p>
        </div>
    </div>

    {{-- Search + Filters --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, SKU..." icon="magnifying-glass" />
        </div>
        <div class="flex flex-wrap items-center gap-2">
            {{-- Status Filter --}}
            <div class="flex items-center gap-1 rounded-xl bg-white p-1 shadow-sm">
                <button wire:click="$set('filter', 'all')" @class(['rounded-lg px-3 py-1.5 text-xs font-semibold transition', $filter === 'all' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-900'])>Todos</button>
                <button wire:click="$set('filter', 'active')" @class(['rounded-lg px-3 py-1.5 text-xs font-semibold transition', $filter === 'active' ? 'bg-emerald-500 text-white' : 'text-gray-500 hover:text-gray-900'])>Activos</button>
                <button wire:click="$set('filter', 'featured')" @class(['rounded-lg px-3 py-1.5 text-xs font-semibold transition', $filter === 'featured' ? 'bg-amber-500 text-white' : 'text-gray-500 hover:text-gray-900'])>Destacados</button>
                <button wire:click="$set('filter', 'low_stock')" @class(['rounded-lg px-3 py-1.5 text-xs font-semibold transition', $filter === 'low_stock' ? 'bg-red-500 text-white' : 'text-gray-500 hover:text-gray-900'])>Stock Bajo</button>
            </div>

            {{-- Category Filter --}}
            <select wire:model.live="filterCategory" class="rounded-lg border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todas las categorías</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                @endforeach
            </select>

            {{-- Brand Filter --}}
            <select wire:model.live="filterBrand" class="rounded-lg border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todas las marcas</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}">{{ $brand->nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Producto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Precio</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Stock</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Categoría</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Marca</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($product->imagen_principal)
                                    <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-lg border border-gray-200">
                                        <img src="{{ $product->imagen_principal_url }}" alt="{{ $product->nombre }}" class="h-full w-full object-cover" />
                                    </div>
                                @else
                                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700">
                                        <iconify-icon icon="heroicons:photo" class="h-5 w-5"></iconify-icon>
                                    </div>
                                @endif
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-900">{{ $product->nombre }}</span>
                                        @if($product->destacado)
                                            <iconify-icon icon="heroicons:star-solid" class="h-3.5 w-3.5 text-amber-500"></iconify-icon>
                                        @endif
                                        @if($product->nuevo)
                                            <span class="rounded bg-blue-100 px-1.5 py-0.5 text-[9px] font-bold text-blue-700">NUEVO</span>
                                        @endif
                                        @if($product->tiene_variantes)
                                            <span class="rounded bg-purple-100 px-1.5 py-0.5 text-[9px] font-bold text-purple-700">{{ $product->variants_count }} VAR</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-400">{{ Str::limit($product->descripcion_corta, 60) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-mono font-medium text-gray-700">{{ $product->sku }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($product->tiene_descuento)
                                <span class="text-sm font-bold text-emerald-600">${{ number_format($product->precio_final, 2) }}</span>
                                <span class="block text-xs text-gray-400 line-through">${{ number_format($product->precio, 2) }}</span>
                            @else
                                <span class="text-sm font-bold text-gray-900">${{ number_format($product->precio, 2) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php $totalStock = $product->stock_total; @endphp
                            <span @class([
                                'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold',
                                $totalStock <= 0 ? 'bg-red-100 text-red-700' :
                                ($product->stock_bajo ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700')
                            ])>
                                {{ $totalStock }} uds
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($product->category)
                                <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">{{ $product->category->nombre }}</span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($product->brand)
                                <span class="text-sm text-gray-600">{{ $product->brand->nombre }}</span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @can('productos.edit')
                                    <a href="{{ route('admin.productos.edit', $product->id) }}" wire:navigate>
                                        <flux:button variant="ghost" size="sm" class="!text-gray-400 hover:!text-indigo-600">
                                            <iconify-icon icon="heroicons:pencil-square" class="h-4 w-4"></iconify-icon>
                                        </flux:button>
                                    </a>
                                @endcan
                                @can('productos.edit')
                                    <flux:button variant="ghost" size="sm" wire:click="toggleDestacado({{ $product->id }})" @class(['!text-gray-400', $product->destacado ? '!text-amber-500' : 'hover:!text-amber-500'])>
                                        <iconify-icon icon="heroicons:star{{ $product->destacado ? '-solid' : '' }}" class="h-4 w-4"></iconify-icon>
                                    </flux:button>
                                @endcan
                                @can('productos.delete')
                                    <flux:button variant="ghost" size="sm" wire:click="delete({{ $product->id }})" wire:confirm="¿Estás seguro de eliminar '{{ $product->nombre }}'?" class="!text-gray-400 hover:!text-red-600">
                                        <iconify-icon icon="heroicons:trash" class="h-4 w-4"></iconify-icon>
                                    </flux:button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                                    <iconify-icon icon="heroicons:square-3-stack-3d" class="h-6 w-6 text-gray-400"></iconify-icon>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-900">No hay productos</p>
                                <p class="text-xs text-gray-500">Crea tu primer producto para comenzar</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $products->links() }}</div>
</div>
