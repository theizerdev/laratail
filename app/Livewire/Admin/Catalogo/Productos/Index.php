<?php

namespace App\Livewire\Admin\Catalogo\Productos;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Productos')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filter = 'all';
    public ?int $filterCategory = null;
    public ?int $filterBrand = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->delete(); // soft delete

        session()->flash('success', 'Producto eliminado correctamente.');
    }

    public function toggleStatus(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['status' => !$product->status]);
    }

    public function toggleDestacado(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['destacado' => !$product->destacado]);
    }

    public function render()
    {
        $query = Product::with(['category', 'brand', 'variants'])
            ->withCount('variants', 'images')
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%")
                    ->orWhere('descripcion_corta', 'like', "%{$this->search}%");
            });
        }

        if ($this->filter === 'active') {
            $query->where('status', true);
        } elseif ($this->filter === 'inactive') {
            $query->where('status', false);
        } elseif ($this->filter === 'featured') {
            $query->where('destacado', true);
        } elseif ($this->filter === 'low_stock') {
            $query->whereColumn('stock', '<=', 'stock_minimo');
        }

        if ($this->filterCategory) {
            $query->where('category_id', $this->filterCategory);
        }
        if ($this->filterBrand) {
            $query->where('brand_id', $this->filterBrand);
        }

        $products = $query->paginate(15);

        $categories = Category::where('status', true)->orderBy('nombre')->get();
        $brands = Brand::where('status', true)->orderBy('nombre')->get();

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('status', true)->count(),
            'featured' => Product::where('destacado', true)->count(),
            'low_stock' => Product::whereColumn('stock', '<=', 'stock_minimo')->count(),
        ];

        return view('livewire.admin.catalogo.productos.index', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'stats' => $stats,
        ]);
    }
}
