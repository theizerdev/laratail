<?php

namespace App\Livewire\Admin\Catalogo\Productos;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
#[Title('Editar Producto')]
class Edit extends Component
{
    use WithFileUploads;

    public Product $product;

    // Basic Info
    public string $nombre = '';
    public string $sku = '';
    public ?int $category_id = null;
    public ?int $brand_id = null;

    // Pricing
    public float $precio = 0;
    public ?float $precio_oferta = null;
    public ?float $precio_compra = null;
    public ?float $precio_bs = null;

    // Inventory
    public int $stock = 0;
    public int $stock_minimo = 5;
    public bool $rastrear_inventario = true;

    // Dimensions
    public ?float $peso = null;
    public ?float $largo = null;
    public ?float $ancho = null;
    public ?float $alto = null;

    // Description
    public string $descripcion_corta = '';
    public string $descripcion = '';

    // Flags
    public bool $tiene_variantes = false;
    public bool $destacado = false;
    public bool $nuevo = false;
    public ?string $fecha_publicacion = null;
    public bool $status = true;

    // SEO
    public string $meta_title = '';
    public string $meta_description = '';

    // Images
    public $imagen_principal = null;
    public ?string $existingMainImage = null;
    public array $imagenes = [];
    public array $existingImages = [];

    // Variants
    public array $selectedVariantAttributes = [];
    public array $variants = [];
    public array $existingVariants = [];

    public string $currentTab = 'info';

    public function mount(int $id): void
    {
        $this->product = Product::with(['variants.attributeValues', 'images'])->findOrFail($id);

        $this->nombre = $this->product->nombre;
        $this->sku = $this->product->sku;
        $this->category_id = $this->product->category_id;
        $this->brand_id = $this->product->brand_id;
        $this->precio = (float) $this->product->precio;
        $this->precio_oferta = $this->product->precio_oferta ? (float) $this->product->precio_oferta : null;
        $this->precio_compra = $this->product->precio_compra ? (float) $this->product->precio_compra : null;
        $this->precio_bs = $this->product->precio_bs ? (float) $this->product->precio_bs : null;
        $this->stock = $this->product->stock;
        $this->stock_minimo = $this->product->stock_minimo;
        $this->rastrear_inventario = $this->product->rastrear_inventario;
        $this->peso = $this->product->peso ? (float) $this->product->peso : null;
        $this->largo = $this->product->largo ? (float) $this->product->largo : null;
        $this->ancho = $this->product->ancho ? (float) $this->product->ancho : null;
        $this->alto = $this->product->alto ? (float) $this->product->alto : null;
        $this->descripcion_corta = $this->product->descripcion_corta ?? '';
        $this->descripcion = $this->product->descripcion ?? '';
        $this->tiene_variantes = $this->product->tiene_variantes;
        $this->destacado = $this->product->destacado;
        $this->nuevo = $this->product->nuevo;
        $this->fecha_publicacion = $this->product->fecha_publicacion?->format('Y-m-d');
        $this->status = $this->product->status;
        $this->meta_title = $this->product->meta_title ?? '';
        $this->meta_description = $this->product->meta_description ?? '';
        $this->existingMainImage = $this->product->imagen_principal;

        // Load existing images
        $this->existingImages = $this->product->images->map(fn($img) => [
            'id' => $img->id,
            'ruta' => $img->ruta,
            'orden' => $img->orden,
        ])->toArray();

        // Load existing variants
        if ($this->tiene_variantes) {
            $this->existingVariants = $this->product->variants->map(function ($v) {
                $attrValues = [];
                foreach ($v->attributeValues as $av) {
                    $attrValues[$av->pivot->attribute_id] = $av->id;
                }
                return [
                    'id' => $v->id,
                    'nombre' => $v->nombre ?? $v->nombre_compuesto,
                    'sku' => $v->sku,
                    'precio' => $v->precio ? (float) $v->precio : null,
                    'precio_oferta' => $v->precio_oferta ? (float) $v->precio_oferta : null,
                    'stock' => $v->stock,
                    'stock_minimo' => $v->stock_minimo,
                    'peso' => $v->peso ? (float) $v->peso : null,
                    'status' => $v->status,
                    'attribute_values' => $attrValues,
                ];
            })->toArray();

            // Reconstruct selectedVariantAttributes from existing variants
            $attrMap = [];
            foreach ($this->product->variants as $variant) {
                foreach ($variant->attributeValues as $av) {
                    $attrId = $av->pivot->attribute_id;
                    $attrMap[$attrId][$av->id] = $av->id;
                }
            }
            foreach ($attrMap as $attrId => $values) {
                $this->selectedVariantAttributes[$attrId] = array_values($values);
            }
        }
    }

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50|unique:products,sku,' . $this->product->id,
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'precio' => 'required|numeric|min:0',
            'precio_oferta' => 'nullable|numeric|min:0',
            'precio_compra' => 'nullable|numeric|min:0',
            'precio_bs' => 'nullable|numeric|min:0',
            'stock' => 'integer|min:0',
            'stock_minimo' => 'integer|min:0',
            'rastrear_inventario' => 'boolean',
            'peso' => 'nullable|numeric|min:0',
            'largo' => 'nullable|numeric|min:0',
            'ancho' => 'nullable|numeric|min:0',
            'alto' => 'nullable|numeric|min:0',
            'descripcion_corta' => 'nullable|string|max:1000',
            'descripcion' => 'nullable|string',
            'tiene_variantes' => 'boolean',
            'destacado' => 'boolean',
            'nuevo' => 'boolean',
            'fecha_publicacion' => 'nullable|date',
            'status' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'imagen_principal' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:3072',
            'imagenes.*' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:3072',
        ];
    }

    public function setTab(string $tab): void
    {
        $this->currentTab = $tab;
    }

    public function removeMainImage(): void
    {
        if ($this->existingMainImage) {
            Storage::disk('public')->delete($this->existingMainImage);
            $this->product->update(['imagen_principal' => null]);
            $this->existingMainImage = null;
        }
        $this->imagen_principal = null;
    }

    public function deleteImage(int $imageId): void
    {
        $image = ProductImage::findOrFail($imageId);
        Storage::disk('public')->delete($image->ruta);
        $image->delete();

        $this->existingImages = array_values(array_filter(
            $this->existingImages,
            fn($img) => $img['id'] !== $imageId
        ));
    }

    public function generateVariants(): void
    {
        $this->variants = [];

        $attributeIds = array_keys(array_filter($this->selectedVariantAttributes, fn($v) => !empty($v)));
        if (count($attributeIds) < 1) {
            return;
        }

        $valueGroups = [];
        foreach ($attributeIds as $attrId) {
            $valueIds = $this->selectedVariantAttributes[$attrId] ?? [];
            if (!empty($valueIds)) {
                $valueGroups[$attrId] = $valueIds;
            }
        }

        if (empty($valueGroups)) {
            return;
        }

        $combinations = $this->cartesianProduct($valueGroups);

        foreach ($combinations as $combo) {
            $names = [];
            $attrValueMap = [];
            foreach ($combo as $attrId => $valueId) {
                $value = AttributeValue::find($valueId);
                if ($value) {
                    $names[] = $value->valor;
                    $attrValueMap[$attrId] = $valueId;
                }
            }

            $this->variants[] = [
                'nombre' => implode(' / ', $names),
                'sku' => '',
                'precio' => null,
                'precio_oferta' => null,
                'stock' => 0,
                'stock_minimo' => 5,
                'peso' => null,
                'status' => true,
                'attribute_values' => $attrValueMap,
            ];
        }
    }

    private function cartesianProduct(array $arrays): array
    {
        $result = [[]];
        foreach ($arrays as $key => $values) {
            $append = [];
            foreach ($result as $product) {
                foreach ($values as $item) {
                    $product[$key] = $item;
                    $append[] = $product;
                }
            }
            $result = $append;
        }
        return $result;
    }

    public function removeVariant(int $index): void
    {
        unset($this->variants[$index]);
        $this->variants = array_values($this->variants);
    }

    public function deleteExistingVariant(int $index): void
    {
        $variant = $this->existingVariants[$index];
        if (isset($variant['id'])) {
            ProductVariant::find($variant['id'])?->delete();
        }
        unset($this->existingVariants[$index]);
        $this->existingVariants = array_values($this->existingVariants);
    }

    public function save(): void
    {
        $this->validate();

        // Upload main image
        $imagenPath = $this->existingMainImage;
        if ($this->imagen_principal) {
            if ($this->existingMainImage) {
                Storage::disk('public')->delete($this->existingMainImage);
            }
            $imagenPath = $this->imagen_principal->store('productos', 'public');
        }

        $this->product->update([
            'nombre' => $this->nombre,
            'sku' => $this->sku ?: null,
            'category_id' => $this->category_id ?: null,
            'brand_id' => $this->brand_id ?: null,
            'precio' => $this->precio,
            'precio_oferta' => $this->precio_oferta,
            'precio_compra' => $this->precio_compra,
            'precio_bs' => $this->precio_bs,
            'stock' => $this->tiene_variantes ? 0 : $this->stock,
            'stock_minimo' => $this->stock_minimo,
            'rastrear_inventario' => $this->rastrear_inventario,
            'peso' => $this->peso,
            'largo' => $this->largo,
            'ancho' => $this->ancho,
            'alto' => $this->alto,
            'descripcion_corta' => $this->descripcion_corta ?: null,
            'descripcion' => $this->descripcion ?: null,
            'tiene_variantes' => $this->tiene_variantes,
            'destacado' => $this->destacado,
            'nuevo' => $this->nuevo,
            'fecha_publicacion' => $this->fecha_publicacion,
            'status' => $this->status,
            'meta_title' => $this->meta_title ?: null,
            'meta_description' => $this->meta_description ?: null,
            'imagen_principal' => $imagenPath,
        ]);

        // Upload additional images
        $maxOrden = collect($this->existingImages)->pluck('orden')->max() ?? 0;
        foreach ($this->imagenes as $img) {
            if ($img) {
                $path = $img->store('productos', 'public');
                ProductImage::create([
                    'product_id' => $this->product->id,
                    'ruta' => $path,
                    'orden' => ++$maxOrden,
                ]);
            }
        }

        // Handle variants: update existing + create new
        if ($this->tiene_variantes) {
            // Update existing variants
            foreach ($this->existingVariants as $variantData) {
                if (isset($variantData['id'])) {
                    $variant = ProductVariant::find($variantData['id']);
                    if ($variant) {
                        $variant->update([
                            'sku' => $variantData['sku'],
                            'precio' => $variantData['precio'],
                            'precio_oferta' => $variantData['precio_oferta'],
                            'stock' => $variantData['stock'],
                            'stock_minimo' => $variantData['stock_minimo'],
                            'peso' => $variantData['peso'],
                            'status' => $variantData['status'],
                        ]);

                        if (!empty($variantData['attribute_values'])) {
                            $syncData = [];
                            foreach ($variantData['attribute_values'] as $attrId => $valueId) {
                                $syncData[$valueId] = ['attribute_id' => $attrId];
                            }
                            $variant->attributeValues()->sync($syncData);
                        }
                    }
                }
            }

            // Create new variants
            foreach ($this->variants as $variantData) {
                $variant = ProductVariant::create([
                    'product_id' => $this->product->id,
                    'sku' => $variantData['sku'] ?: $this->product->sku . '-' . Str::upper(Str::random(4)),
                    'nombre' => $variantData['nombre'],
                    'precio' => $variantData['precio'],
                    'precio_oferta' => $variantData['precio_oferta'],
                    'stock' => $variantData['stock'],
                    'stock_minimo' => $variantData['stock_minimo'],
                    'peso' => $variantData['peso'],
                    'status' => $variantData['status'],
                ]);

                if (!empty($variantData['attribute_values'])) {
                    $syncData = [];
                    foreach ($variantData['attribute_values'] as $attrId => $valueId) {
                        $syncData[$valueId] = ['attribute_id' => $attrId];
                    }
                    $variant->attributeValues()->sync($syncData);
                }
            }
        }

        session()->flash('success', 'Producto actualizado correctamente.');

        $this->redirect(route('admin.productos'), navigate: true);
    }

    public function render()
    {
        $categories = Category::where('status', true)->orderBy('nombre')->get();
        $brands = Brand::where('status', true)->orderBy('nombre')->get();
        $attributes = Attribute::where('status', true)
            ->where('usado_para_variantes', true)
            ->with(['values' => fn($q) => $q->orderBy('orden')])
            ->orderBy('nombre')
            ->get();

        return view('livewire.admin.catalogo.productos.edit', [
            'categories' => $categories,
            'brands' => $brands,
            'attributes' => $attributes,
        ]);
    }
}
