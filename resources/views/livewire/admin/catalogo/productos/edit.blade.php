<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Editar Producto</h2>
            <p class="mt-1 text-sm text-gray-500">Modifica la información de: <strong>{{ $nombre }}</strong></p>
        </div>
        <a href="{{ route('admin.productos') }}" wire:navigate>
            <flux:button variant="ghost">
                <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon>
                Volver
            </flux:button>
        </a>
    </div>

    {{-- Tab Navigation --}}
    <div class="mb-6 flex items-center gap-1 rounded-xl bg-white p-1 shadow-sm">
        <button wire:click="setTab('info')" @class(['rounded-lg px-4 py-2 text-xs font-semibold transition', $currentTab === 'info' ? 'bg-indigo-500 text-white' : 'text-gray-500 hover:text-gray-900'])>
            <iconify-icon icon="heroicons:document-text" class="h-4 w-4 inline mr-1"></iconify-icon>
            Información
        </button>
        <button wire:click="setTab('pricing')" @class(['rounded-lg px-4 py-2 text-xs font-semibold transition', $currentTab === 'pricing' ? 'bg-indigo-500 text-white' : 'text-gray-500 hover:text-gray-900'])>
            <iconify-icon icon="heroicons:currency-dollar" class="h-4 w-4 inline mr-1"></iconify-icon>
            Precio e Inventario
        </button>
        <button wire:click="setTab('variants')" @class(['rounded-lg px-4 py-2 text-xs font-semibold transition', $currentTab === 'variants' ? 'bg-indigo-500 text-white' : 'text-gray-500 hover:text-gray-900'])>
            <iconify-icon icon="heroicons:square-3-stack-3d" class="h-4 w-4 inline mr-1"></iconify-icon>
            Variantes
        </button>
        <button wire:click="setTab('images')" @class(['rounded-lg px-4 py-2 text-xs font-semibold transition', $currentTab === 'images' ? 'bg-indigo-500 text-white' : 'text-gray-500 hover:text-gray-900'])>
            <iconify-icon icon="heroicons:photo" class="h-4 w-4 inline mr-1"></iconify-icon>
            Imágenes
        </button>
        <button wire:click="setTab('seo')" @class(['rounded-lg px-4 py-2 text-xs font-semibold transition', $currentTab === 'seo' ? 'bg-indigo-500 text-white' : 'text-gray-500 hover:text-gray-900'])>
            <iconify-icon icon="heroicons:magnifying-glass" class="h-4 w-4 inline mr-1"></iconify-icon>
            SEO
        </button>
    </div>

    <div class="space-y-6">
        {{-- TAB: Info --}}
        @if($currentTab === 'info')
            <div class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-semibold text-gray-900">Información General</h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:input wire:model="nombre" label="Nombre del Producto" placeholder="Ej: Camiseta Premium" :error="$errors->first('nombre')" required />
                    <flux:input wire:model="sku" label="SKU" placeholder="SKU del producto" :error="$errors->first('sku')" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <flux:label>Categoría</flux:label>
                        <select wire:model="category_id" class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                            <option value="">Sin categoría</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <flux:label>Marca</flux:label>
                        <select wire:model="brand_id" class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                            <option value="">Sin marca</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <flux:textarea wire:model="descripcion_corta" label="Descripción Corta" placeholder="Breve descripción..." rows="2" :error="$errors->first('descripcion_corta')" />

                <div>
                    <flux:label>Descripción Completa</flux:label>
                    <textarea wire:model="descripcion" rows="6" class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500" placeholder="Descripción detallada...">{{ $descripcion }}</textarea>
                    @error('descripcion') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <flux:input wire:model="peso" label="Peso (kg)" type="number" step="0.001" placeholder="0.500" :error="$errors->first('peso')" />
                    <flux:input wire:model="largo" label="Largo (cm)" type="number" step="0.01" placeholder="30" :error="$errors->first('largo')" />
                    <flux:input wire:model="ancho" label="Ancho (cm)" type="number" step="0.01" placeholder="20" :error="$errors->first('ancho')" />
                    <flux:input wire:model="alto" label="Alto (cm)" type="number" step="0.01" placeholder="5" :error="$errors->first('alto')" />
                </div>

                <div class="flex flex-wrap gap-4">
                    <flux:checkbox wire:model="destacado" label="Producto Destacado" />
                    <flux:checkbox wire:model="nuevo" label="Producto Nuevo" />
                    <flux:checkbox wire:model="status" label="Activo" />
                    <flux:input wire:model="fecha_publicacion" label="Fecha Publicación" type="date" :error="$errors->first('fecha_publicacion')" />
                </div>
            </div>
        @endif

        {{-- TAB: Pricing --}}
        @if($currentTab === 'pricing')
            <div class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-semibold text-gray-900">Precio e Inventario</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <flux:input wire:model="precio" label="Precio de Venta" type="number" step="0.01" placeholder="0.00" :error="$errors->first('precio')" required />
                    <flux:input wire:model="precio_oferta" label="Precio Oferta" type="number" step="0.01" placeholder="Sin oferta" :error="$errors->first('precio_oferta')" />
                    <flux:input wire:model="precio_compra" label="Precio de Compra (Costo)" type="number" step="0.01" placeholder="0.00" :error="$errors->first('precio_compra')" />
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <flux:input wire:model="stock" label="Stock" type="number" placeholder="0" :error="$errors->first('stock')" />
                    <flux:input wire:model="stock_minimo" label="Stock Mínimo" type="number" placeholder="5" :error="$errors->first('stock_minimo')" />
                    <div class="flex items-end pb-1">
                        <flux:checkbox wire:model="rastrear_inventario" label="Rastrear Inventario" />
                    </div>
                </div>
                @if($precio > 0 && $precio_oferta && $precio_oferta < $precio)
                    <div class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">
                        <iconify-icon icon="heroicons:tag" class="h-4 w-4 inline mr-1"></iconify-icon>
                        Descuento de <strong>{{ round((($precio - $precio_oferta) / $precio) * 100) }}%</strong>
                        — Ahorro de ${{ number_format($precio - $precio_oferta, 2) }}
                    </div>
                @endif
            </div>
        @endif

        {{-- TAB: Variants --}}
        @if($currentTab === 'variants')
            <div class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Variantes</h3>
                    <flux:checkbox wire:model.live="tiene_variantes" label="Este producto tiene variantes" />
                </div>

                @if($tiene_variantes)
                    {{-- Existing Variants --}}
                    @if(count($existingVariants) > 0)
                        <div class="space-y-2">
                            <p class="text-xs font-semibold text-gray-700">Variantes existentes ({{ count($existingVariants) }}):</p>
                            @foreach($existingVariants as $i => $variant)
                                <div class="rounded-xl border border-purple-200 bg-purple-50/30 p-3">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-semibold text-gray-900">{{ $variant['nombre'] }}</span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-400">SKU: {{ $variant['sku'] }}</span>
                                            <button wire:click="deleteExistingVariant({{ $i }})" wire:confirm="¿Eliminar esta variante?" class="text-red-400 hover:text-red-600">
                                                <iconify-icon icon="heroicons:x-mark" class="h-4 w-4"></iconify-icon>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-5">
                                        <flux:input wire:model="existingVariants.{{ $i }}.sku" label="SKU" placeholder="SKU" />
                                        <flux:input wire:model="existingVariants.{{ $i }}.precio" label="Precio" type="number" step="0.01" placeholder="Precio" />
                                        <flux:input wire:model="existingVariants.{{ $i }}.stock" label="Stock" type="number" placeholder="0" />
                                        <flux:input wire:model="existingVariants.{{ $i }}.stock_minimo" label="Stock Mín" type="number" placeholder="5" />
                                        <flux:input wire:model="existingVariants.{{ $i }}.peso" label="Peso (kg)" type="number" step="0.001" placeholder="0.000" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Generate new variants --}}
                    <div class="space-y-3 border-t border-gray-200 pt-4">
                        <p class="text-xs text-gray-500">Generar nuevas variantes:</p>
                        @foreach($attributes as $attr)
                            <div class="rounded-xl border border-gray-200 p-3">
                                <p class="mb-2 text-xs font-semibold text-gray-700">{{ $attr->nombre }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($attr->values as $val)
                                        <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-full border px-3 py-1 text-xs transition
                                            {{ in_array($val->id, $selectedVariantAttributes[$attr->id] ?? []) ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                            <input type="checkbox" value="{{ $val->id }}" wire:model.live="selectedVariantAttributes.{{ $attr->id }}" class="sr-only" />
                                            @if($attr->tipo === 'color' && $val->codigo_color)
                                                <span class="h-3 w-3 rounded-full border border-gray-300" style="background-color: {{ $val->codigo_color }}"></span>
                                            @endif
                                            {{ $val->valor }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        @if(!empty(array_filter($selectedVariantAttributes, fn($v) => !empty($v))))
                            <flux:button wire:click="generateVariants" class="!bg-purple-500 hover:!bg-purple-600">
                                <iconify-icon icon="heroicons:sparkles" class="h-4 w-4"></iconify-icon>
                                Generar Variantes
                            </flux:button>
                        @endif
                    </div>

                    {{-- New Generated Variants --}}
                    @if(count($variants) > 0)
                        <div class="space-y-2">
                            <p class="text-xs font-semibold text-indigo-700">{{ count($variants) }} nueva(s) variante(s):</p>
                            @foreach($variants as $i => $variant)
                                <div class="rounded-xl border border-indigo-200 bg-indigo-50/30 p-3">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-semibold text-gray-900">{{ $variant['nombre'] }}</span>
                                        <button wire:click="removeVariant({{ $i }})" class="text-red-400 hover:text-red-600">
                                            <iconify-icon icon="heroicons:x-mark" class="h-4 w-4"></iconify-icon>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-5">
                                        <flux:input wire:model="variants.{{ $i }}.sku" label="SKU" placeholder="SKU" />
                                        <flux:input wire:model="variants.{{ $i }}.precio" label="Precio" type="number" step="0.01" placeholder="Precio" />
                                        <flux:input wire:model="variants.{{ $i }}.stock" label="Stock" type="number" placeholder="0" />
                                        <flux:input wire:model="variants.{{ $i }}.stock_minimo" label="Stock Mín" type="number" placeholder="5" />
                                        <flux:input wire:model="variants.{{ $i }}.peso" label="Peso (kg)" type="number" step="0.001" placeholder="0.000" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <p class="py-8 text-center text-sm text-gray-400">Activa "tiene variantes" para generar combinaciones de atributos.</p>
                @endif
            </div>
        @endif

        {{-- TAB: Images --}}
        @if($currentTab === 'images')
            <div class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-semibold text-gray-900">Imágenes</h3>

                {{-- Main Image --}}
                <div>
                    <flux:label>Imagen Principal</flux:label>
                    <div class="mt-2 flex items-center gap-4">
                        <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-gray-200 bg-gray-50">
                            @if($imagen_principal)
                                <img src="{{ $imagen_principal->temporaryUrl() }}" alt="Preview" class="h-full w-full object-cover" />
                            @elseif($existingMainImage)
                                <img src="{{ asset('storage/' . $existingMainImage) }}" alt="Actual" class="h-full w-full object-cover" />
                            @else
                                <iconify-icon icon="heroicons:photo" class="h-8 w-8 text-gray-300"></iconify-icon>
                            @endif
                        </div>
                        <div>
                            <input type="file" wire:model="imagen_principal" accept="image/png,image/jpeg,image/webp" class="block w-full text-xs text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-indigo-600 hover:file:bg-indigo-100" />
                            @if($imagen_principal || $existingMainImage)
                                <button wire:click="removeMainImage" class="mt-1 text-xs text-red-500 hover:text-red-700">Quitar</button>
                            @endif
                            @error('imagen_principal') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Existing Images --}}
                @if(count($existingImages) > 0)
                    <div>
                        <flux:label>Imágenes Existentes</flux:label>
                        <div class="mt-2 flex flex-wrap gap-3">
                            @foreach($existingImages as $img)
                                <div class="relative h-20 w-20 overflow-hidden rounded-xl border border-gray-200">
                                    <img src="{{ asset('storage/' . $img['ruta']) }}" alt="Imagen {{ $img['orden'] }}" class="h-full w-full object-cover" />
                                    <button wire:click="deleteImage({{ $img['id'] }})" wire:confirm="¿Eliminar esta imagen?" class="absolute -top-1 -right-1 rounded-full bg-red-500 p-0.5 text-white">
                                        <iconify-icon icon="heroicons:x-mark" class="h-3 w-3"></iconify-icon>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Upload New --}}
                <div>
                    <flux:label>Agregar Imágenes</flux:label>
                    <input type="file" wire:model="imagenes" accept="image/png,image/jpeg,image/webp" multiple class="mt-1 block w-full text-xs text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-indigo-600 hover:file:bg-indigo-100" />
                    <div wire:loading wire:target="imagenes" class="mt-1 text-xs text-indigo-500">Subiendo imágenes...</div>
                    @if(count($imagenes) > 0)
                        <div class="mt-3 flex flex-wrap gap-3">
                            @foreach($imagenes as $idx => $img)
                                @if($img)
                                    <div class="relative h-20 w-20 overflow-hidden rounded-xl border border-indigo-200">
                                        <img src="{{ $img->temporaryUrl() }}" alt="Nueva {{ $idx + 1 }}" class="h-full w-full object-cover" />
                                        <span class="absolute bottom-0 left-0 right-0 bg-indigo-500/80 text-center text-[9px] text-white">NUEVA</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- TAB: SEO --}}
        @if($currentTab === 'seo')
            <div class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-semibold text-gray-900">SEO (Meta Tags)</h3>
                <flux:input wire:model="meta_title" label="Meta Title" placeholder="Título para buscadores" :error="$errors->first('meta_title')" />
                <flux:textarea wire:model="meta_description" label="Meta Description" placeholder="Descripción para buscadores" rows="3" :error="$errors->first('meta_description')" />
                <div class="rounded-xl bg-gray-50 p-4">
                    <p class="mb-1 text-xs font-semibold text-gray-500">Vista previa en Google:</p>
                    <p class="text-lg text-blue-700 leading-tight">{{ $meta_title ?: $nombre ?: 'Título del producto' }}</p>
                    <p class="text-xs text-green-700">{{ request()->getHost() }}/productos/{{ \Illuminate\Support\Str::slug($nombre ?: 'nombre-del-producto') }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ $meta_description ?: $descripcion_corta ?: 'Descripción del producto...' }}</p>
                </div>
            </div>
        @endif

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.productos') }}" wire:navigate>
                <flux:button variant="ghost">Cancelar</flux:button>
            </a>
            <flux:button wire:click="save" class="!bg-indigo-500 hover:!bg-indigo-600">
                <iconify-icon icon="heroicons:check-circle" class="h-4 w-4"></iconify-icon>
                Actualizar Producto
            </flux:button>
        </div>
    </div>
</div>
