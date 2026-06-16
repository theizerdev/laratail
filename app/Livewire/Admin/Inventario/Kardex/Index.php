<?php

namespace App\Livewire\Admin\Inventario\Kardex;

use App\Models\InventoryMovement;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Kardex - Historial de Stock')]
class Index extends Component
{
    public ?int $product_id = null;
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $productSearch = '';
    public bool $showProductDropdown = false;

    public function updatedProductSearch(): void
    {
        $this->showProductDropdown = strlen($this->productSearch) >= 2;
    }

    public function selectProduct(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->product_id = $product->id;
        $this->productSearch = $product->nombre . ' (' . $product->sku . ')';
        $this->showProductDropdown = false;
    }

    public function clearProduct(): void
    {
        $this->product_id = null;
        $this->productSearch = '';
    }

    public function render()
    {
        $product = null;
        $movements = collect();
        $summary = [];

        if ($this->product_id) {
            $product = Product::find($this->product_id);

            $query = InventoryMovement::with(['variant', 'user', 'sucursalOrigen', 'sucursalDestino'])
                ->where('product_id', $this->product_id)
                ->orderBy('created_at', 'asc');

            if ($this->dateFrom) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            }
            if ($this->dateTo) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            }

            $movements = $query->get();

            $totalEntradas = $movements->where('tipo', 'entrada')->sum('cantidad');
            $totalSalidas = $movements->where('tipo', 'salida')->sum('cantidad');

            $summary = [
                'total_entradas' => $totalEntradas,
                'total_salidas' => $totalSalidas,
                'total_ajustes' => $movements->where('tipo', 'ajuste')->count(),
                'stock_actual' => $product?->stock ?? 0,
                'primer_movimiento' => $movements->first()?->created_at,
                'ultimo_movimiento' => $movements->last()?->created_at,
            ];
        }

        $searchResults = [];
        if ($this->showProductDropdown) {
            $searchResults = Product::where('nombre', 'like', "%{$this->productSearch}%")
                ->orWhere('sku', 'like', "%{$this->productSearch}%")
                ->limit(8)->get(['id', 'nombre', 'sku', 'stock', 'imagen_principal']);
        }

        return view('livewire.admin.inventario.kardex.index', [
            'product' => $product,
            'movements' => $movements,
            'summary' => $summary,
            'searchResults' => $searchResults,
        ]);
    }
}
