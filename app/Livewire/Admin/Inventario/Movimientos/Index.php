<?php

namespace App\Livewire\Admin\Inventario\Movimientos;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sucursal;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Movimientos de Inventario')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterTipo = 'all';
    public string $dateFrom = '';
    public string $dateTo = '';

    // Modal state
    public ?int $detailId = null;

    // Form fields
    public string $tipo = 'entrada';
    public ?int $product_id = null;
    public ?int $product_variant_id = null;
    public int $cantidad = 1;
    public ?float $costo_unitario = null;
    public ?int $sucursal_origen_id = null;
    public ?int $sucursal_destino_id = null;
    public string $referencia = '';
    public string $motivo = '';

    // Product search
    public string $productSearch = '';
    public bool $showProductDropdown = false;

    // Calculated preview
    public int $stockAnterior = 0;
    public int $stockNuevo = 0;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterTipo(): void
    {
        $this->resetPage();
    }

    public function updatedTipo(): void
    {
        $this->reset(['sucursal_origen_id', 'sucursal_destino_id']);
        $this->calculatePreview();
    }

    public function updatedCantidad(): void
    {
        $this->calculatePreview();
    }

    public function updatedProductId(): void
    {
        $this->calculatePreview();
    }

    public function searchProducts(): void
    {
        $this->showProductDropdown = strlen($this->productSearch) >= 2;
    }

    public function selectProduct(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->product_id = $product->id;
        $this->productSearch = $product->nombre . ' (' . $product->sku . ')';
        $this->showProductDropdown = false;
        $this->product_variant_id = null;
        $this->calculatePreview();
    }

    protected function calculatePreview(): void
    {
        if (!$this->product_id) {
            $this->stockAnterior = 0;
            $this->stockNuevo = 0;
            return;
        }

        $product = Product::find($this->product_id);
        if (!$product) return;

        $stockActual = $this->product_variant_id
            ? ($product->variants()->find($this->product_variant_id)?->stock ?? 0)
            : $product->stock;

        $this->stockAnterior = $stockActual;
        $cantidad = (int) $this->cantidad;

        $this->stockNuevo = match ($this->tipo) {
            'entrada' => $stockActual + $cantidad,
            'salida' => max(0, $stockActual - $cantidad),
            'ajuste' => $cantidad,
            'transferencia' => $stockActual - $cantidad,
            default => $stockActual,
        };
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'modal-create-movimiento');
    }

    public function closeModal(): void
    {
        $this->dispatch('close-modal', 'modal-create-movimiento');
        $this->resetForm();
    }

    public function showDetail(int $id): void
    {
        $this->detailId = $id;
        $this->dispatch('open-modal', 'modal-detail-movimiento');
    }

    protected function resetForm(): void
    {
        $this->tipo = 'entrada';
        $this->product_id = null;
        $this->product_variant_id = null;
        $this->cantidad = 1;
        $this->costo_unitario = null;
        $this->sucursal_origen_id = null;
        $this->sucursal_destino_id = null;
        $this->referencia = '';
        $this->motivo = '';
        $this->productSearch = '';
        $this->showProductDropdown = false;
        $this->stockAnterior = 0;
        $this->stockNuevo = 0;
    }

    public function save(): void
    {
        $rules = [
            'tipo' => 'required|in:entrada,salida,ajuste,transferencia',
            'product_id' => 'required|exists:products,id',
            'cantidad' => 'required|integer|min:1',
            'costo_unitario' => 'nullable|numeric|min:0',
            'referencia' => 'nullable|string|max:255',
            'motivo' => 'nullable|string|max:1000',
        ];

        if ($this->tipo === 'transferencia') {
            $rules['sucursal_origen_id'] = 'required|exists:sucursales,id';
            $rules['sucursal_destino_id'] = 'required|exists:sucursales,id|different:sucursal_origen_id';
        }

        if ($this->tipo === 'ajuste') {
            $rules['motivo'] = 'required|string|max:1000';
        }

        $this->validate($rules);

        try {
            InventoryMovement::registrar([
                'product_id' => $this->product_id,
                'product_variant_id' => $this->product_variant_id,
                'tipo' => $this->tipo,
                'cantidad' => $this->tipo === 'ajuste' ? $this->stockNuevo : $this->cantidad,
                'costo_unitario' => $this->costo_unitario,
                'sucursal_origen_id' => $this->sucursal_origen_id,
                'sucursal_destino_id' => $this->sucursal_destino_id,
                'referencia' => $this->referencia ?: null,
                'motivo' => $this->motivo ?: null,
            ]);

            session()->flash('success', 'Movimiento registrado correctamente.');
            $this->closeModal();
            $this->dispatch('close-modal', 'modal-create-movimiento');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al registrar movimiento: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = InventoryMovement::with(['product', 'variant', 'user', 'sucursalOrigen', 'sucursalDestino'])
            ->latest();

        if ($this->filterTipo !== 'all') {
            $query->where('tipo', $this->filterTipo);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('product', fn($p) => $p->where('nombre', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%"))
                    ->orWhere('referencia', 'like', "%{$this->search}%")
                    ->orWhere('motivo', 'like', "%{$this->search}%");
            });
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $movements = $query->paginate(20);

        $detailMovement = null;
        if ($this->detailId) {
            $detailMovement = InventoryMovement::with(['product', 'variant', 'user', 'sucursalOrigen', 'sucursalDestino'])->find($this->detailId);
        }

        $searchResults = [];
        if ($this->showProductDropdown) {
            $searchResults = Product::where('nombre', 'like', "%{$this->productSearch}%")
                ->orWhere('sku', 'like', "%{$this->productSearch}%")
                ->limit(8)->get(['id', 'nombre', 'sku', 'stock', 'imagen_principal']);
        }

        $stats = [
            'total' => InventoryMovement::count(),
            'entradas' => InventoryMovement::where('tipo', 'entrada')->count(),
            'salidas' => InventoryMovement::where('tipo', 'salida')->count(),
            'ajustes' => InventoryMovement::where('tipo', 'ajuste')->count(),
        ];

        $sucursales = Sucursal::orderBy('nombre')->get(['id', 'nombre']);

        return view('livewire.admin.inventario.movimientos.index', [
            'movements' => $movements,
            'stats' => $stats,
            'detailMovement' => $detailMovement,
            'searchResults' => $searchResults,
            'sucursales' => $sucursales,
        ]);
    }
}
