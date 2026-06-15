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
#[Title('Crear Producto')]
class Create extends Component
{
    use WithFileUploads;

    // Basic Info
    public string $nombre = '';
    public string $sku = '';
    public ?int $category_id = null;
    public ?int $brand_id = null;

    // Pricing
    public float $precio = 0;
    public ?float $precio_oferta = null;
    public ?float $precio_compra = null;

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
    public array $imagenes = [];

    // Variants
    public array $selectedVariantAttributes = []; // [attribute_id => [value_ids]]
    public array $variants = []; // Generated variants

    // Current step (for tab navigation)
    public string $currentTab = 'info';

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50|unique:products,sku',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'precio' => 'required|numeric|min:0',
            'precio_oferta' => 'nullable|numeric|min:0',
            'precio_compra' => 'nullable|numeric|min:0',
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

    /**
     * Generate variants from selected attribute values
     */
    public function generateVariants(): void
    {
        $this->variants = [];

        $attributeIds = array_keys(array_filter($this->selectedVariantAttributes, fn($v) => !empty($v)));
        if (count($attributeIds) < 1) {
            return;
        }

        // Get all value IDs selected
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

        // Generate cartesian product
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

    public function save(): void
    {
        $this->validate();

        // Upload main image
        $imagenPath = null;
        if ($this->imagen_principal) {
            $imagenPath = $this->imagen_principal->store('productos', 'public');
        }

        $product = Product::create([
            'nombre' => $this->nombre,
            'sku' => $this->sku ?: null,
            'category_id' => $this->category_id ?: null,
            'brand_id' => $this->brand_id ?: null,
            'precio' => $this->precio,
            'precio_oferta' => $this->precio_oferta,
            'precio_compra' => $this->precio_compra,
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
        $orden = 1;
        foreach ($this->imagenes as $img) {
            if ($img) {
                $path = $img->store('productos', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'ruta' => $path,
                    'orden' => $orden++,
                ]);
            }
        }

        // Save variants
        if ($this->tiene_variantes && !empty($this->variants)) {
            foreach ($this->variants as $variantData) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $variantData['sku'] ?: $product->sku . '-' . Str::upper(Str::random(4)),
                    'nombre' => $variantData['nombre'],
                    'precio' => $variantData['precio'],
                    'precio_oferta' => $variantData['precio_oferta'],
                    'stock' => $variantData['stock'],
                    'stock_minimo' => $variantData['stock_minimo'],
                    'peso' => $variantData['peso'],
                    'status' => $variantData['status'],
                ]);

                // Link attribute values
                if (!empty($variantData['attribute_values'])) {
                    $syncData = [];
                    foreach ($variantData['attribute_values'] as $attrId => $valueId) {
                        $syncData[$valueId] = ['attribute_id' => $attrId];
                    }
                    $variant->attributeValues()->sync($syncData);
                }
            }
        }

        session()->flash('success', 'Producto creado correctamente.');

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

        return view('livewire.admin.catalogo.productos.create', [
            'categories' => $categories,
            'brands' => $brands,
            'attributes' => $attributes,
        ]);
    }
}
