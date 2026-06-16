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
#[Title('Editar Orden de Compra')]
class Edit extends Component
{
    public int $orderId;
    public ?int $supplier_id = null;
    public string $fecha = '';
    public string $fecha_entrega_esperada = '';
    public string $estado = 'borrador';
    public string $notas = '';

    public string $productSearch = '';
    public bool $showProductDropdown = false;
    public array $items = [];
    public float $impuesto_rate = 16;

    public function mount(int $id): void
    {
        $this->orderId = $id;
        $order = PurchaseOrder::with('items')->findOrFail($id);

        $this->supplier_id = $order->supplier_id;
        $this->fecha = $order->fecha?->format('Y-m-d') ?? '';
        $this->fecha_entrega_esperada = $order->fecha_entrega_esperada?->format('Y-m-d') ?? '';
        $this->estado = $order->estado;
        $this->notas = $order->notas ?? '';

        $this->items = $order->items->map(fn($item) => [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'nombre_producto' => $item->nombre_producto,
            'sku' => $item->sku,
            'cantidad_pedida' => $item->cantidad_pedida,
            'cantidad_recibida' => $item->cantidad_recibida,
            'costo_unitario' => (float) $item->costo_unitario,
            'subtotal' => (float) $item->subtotal,
        ])->toArray();
    }

    public function searchProducts(): void
    {
        $this->showProductDropdown = strlen($this->productSearch) >= 2;
    }

    public function addItem(int $productId): void
    {
        $product = Product::findOrFail($productId);

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
            'id' => null,
            'product_id' => $product->id,
            'nombre_producto' => $product->nombre,
            'sku' => $product->sku,
            'cantidad_pedida' => 1,
            'cantidad_recibida' => 0,
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
        $item = $this->items[$index] ?? null;
        if ($item && !empty($item['id'])) {
            PurchaseOrderItem::destroy($item['id']);
        }
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

    public function cambiarEstado(string $nuevoEstado): void
    {
        $order = PurchaseOrder::findOrFail($this->orderId);
        $order->cambiarEstado($nuevoEstado);
        $this->estado = $nuevoEstado;
        session()->flash('success', "Estado cambiado a: {$order->estado_label}");
    }

    public function save(): void
    {
        $this->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'fecha' => 'required|date',
            'fecha_entrega_esperada' => 'nullable|date|after_or_equal:fecha',
            'notas' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
        ]);

        DB::transaction(function () {
            $order = PurchaseOrder::findOrFail($this->orderId);
            $order->update([
                'supplier_id' => $this->supplier_id,
                'fecha' => $this->fecha,
                'fecha_entrega_esperada' => $this->fecha_entrega_esperada ?: null,
                'subtotal' => $this->getSubtotal(),
                'impuesto' => $this->getImpuesto(),
                'total' => $this->getTotal(),
                'notas' => $this->notas ?: null,
            ]);

            $existingIds = [];
            foreach ($this->items as $item) {
                if (!empty($item['id'])) {
                    $poi = PurchaseOrderItem::find($item['id']);
                    $poi->update([
                        'cantidad_pedida' => $item['cantidad_pedida'],
                        'costo_unitario' => $item['costo_unitario'],
                        'subtotal' => $item['subtotal'],
                    ]);
                    $existingIds[] = $item['id'];
                } else {
                    $newItem = PurchaseOrderItem::create([
                        'purchase_order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'nombre_producto' => $item['nombre_producto'],
                        'sku' => $item['sku'],
                        'cantidad_pedida' => $item['cantidad_pedida'],
                        'cantidad_recibida' => 0,
                        'costo_unitario' => $item['costo_unitario'],
                        'subtotal' => $item['subtotal'],
                    ]);
                    $existingIds[] = $newItem->id;
                }
            }

            $order->items()->whereNotIn('id', $existingIds)->delete();
            $order->recalcular();
        });

        session()->flash('success', 'Orden de compra actualizada correctamente.');
        $this->redirect(route('admin.ordenes-compra'), navigate: true);
    }

    public function render()
    {
        $order = PurchaseOrder::findOrFail($this->orderId);
        $suppliers = Supplier::where('status', true)->orderBy('nombre')->get(['id', 'nombre']);

        $searchResults = [];
        if ($this->showProductDropdown) {
            $searchResults = Product::where('nombre', 'like', "%{$this->productSearch}%")
                ->orWhere('sku', 'like', "%{$this->productSearch}%")
                ->limit(8)->get(['id', 'nombre', 'sku', 'precio_compra', 'stock']);
        }

        return view('livewire.admin.inventario.ordenes-compra.edit', [
            'order' => $order,
            'suppliers' => $suppliers,
            'searchResults' => $searchResults,
        ]);
    }
}
