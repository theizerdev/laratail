<?php

namespace App\Livewire\Admin\Inventario\OrdenesCompra;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Nueva Orden de Compra')]
class Create extends Component
{
    // Order fields
    public ?int $supplier_id = null;
    public string $fecha = '';
    public string $fecha_entrega_esperada = '';
    public string $notas = '';

    // Product search
    public string $productSearch = '';
    public bool $showProductDropdown = false;

    // Cart items
    public array $items = [];

    // Tax rate
    public float $impuesto_rate = 16;

    public function mount(): void
    {
        $this->fecha = now()->format('Y-m-d');
    }

    public function searchProducts(): void
    {
        $this->showProductDropdown = strlen($this->productSearch) >= 2;
    }

    public function addItem(int $productId): void
    {
        $product = Product::findOrFail($productId);

        // Check if already in cart
        foreach ($this->items as &$item) {
            if ($item['product_id'] === $productId) {
                $item['cantidad_pedida']++;
                $item['subtotal'] = $item['cantidad_pedida'] * $item['costo_unitario'];
                $this->showProductDropdown = false;
                $this->productSearch = '';
                return;
            }
        }

        $this->items[] = [
            'product_id' => $product->id,
            'nombre_producto' => $product->nombre,
            'sku' => $product->sku,
            'cantidad_pedida' => 1,
            'costo_unitario' => (float) ($product->precio_compra ?? $product->precio * 0.6),
            'subtotal' => (float) ($product->precio_compra ?? $product->precio * 0.6),
        ];

        $this->showProductDropdown = false;
        $this->productSearch = '';
    }

    public function updateItemCantidad(int $index, int $cantidad): void
    {
        if (isset($this->items[$index]) && $cantidad > 0) {
            $this->items[$index]['cantidad_pedida'] = $cantidad;
            $this->items[$index]['subtotal'] = $cantidad * $this->items[$index]['costo_unitario'];
        }
    }

    public function updateItemCosto(int $index, float $costo): void
    {
        if (isset($this->items[$index]) && $costo >= 0) {
            $this->items[$index]['costo_unitario'] = $costo;
            $this->items[$index]['subtotal'] = $this->items[$index]['cantidad_pedida'] * $costo;
        }
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function getSubtotal(): float
    {
        return collect($this->items)->sum('subtotal');
    }

    public function getImpuesto(): float
    {
        return $this->getSubtotal() * ($this->impuesto_rate / 100);
    }

    public function getTotal(): float
    {
        return $this->getSubtotal() + $this->getImpuesto();
    }

    public function save(string $estado = 'borrador'): void
    {
        $this->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'fecha' => 'required|date',
            'fecha_entrega_esperada' => 'nullable|date|after_or_equal:fecha',
            'notas' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.cantidad_pedida' => 'required|integer|min:1',
            'items.*.costo_unitario' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($estado) {
            $order = PurchaseOrder::create([
                'supplier_id' => $this->supplier_id,
                'estado' => $estado,
                'fecha' => $this->fecha,
                'fecha_entrega_esperada' => $this->fecha_entrega_esperada ?: null,
                'subtotal' => $this->getSubtotal(),
                'impuesto' => $this->getImpuesto(),
                'total' => $this->getTotal(),
                'notas' => $this->notas ?: null,
            ]);

            foreach ($this->items as $item) {
                $product = Product::find($item['product_id']);
                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'nombre_producto' => $item['nombre_producto'],
                    'sku' => $item['sku'],
                    'cantidad_pedida' => $item['cantidad_pedida'],
                    'cantidad_recibida' => 0,
                    'costo_unitario' => $item['costo_unitario'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
        });

        session()->flash('success', 'Orden de compra creada correctamente.');
        $this->redirect(route('admin.ordenes-compra'), navigate: true);
    }

    public function render()
    {
        $suppliers = Supplier::where('status', true)->orderBy('nombre')->get(['id', 'nombre']);

        $searchResults = [];
        if ($this->showProductDropdown) {
            $searchResults = Product::where('nombre', 'like', "%{$this->productSearch}%")
                ->orWhere('sku', 'like', "%{$this->productSearch}%")
                ->limit(8)->get(['id', 'nombre', 'sku', 'precio_compra', 'stock']);
        }

        return view('livewire.admin.inventario.ordenes-compra.create', [
            'suppliers' => $suppliers,
            'searchResults' => $searchResults,
        ]);
    }
}
