<?php

namespace App\Livewire\Admin\Pagos\Facturas;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Nueva Factura')]
class Create extends Component
{
    public ?int $order_id = null;
    public string $tipo = 'factura';
    public string $serie = '';
    public string $numero_control = '';
    public string $notas = '';

    public string $orderSearch = '';
    public bool $showOrderDropdown = false;

    public array $items = [];
    public float $subtotal = 0;
    public float $impuesto = 0;
    public float $total = 0;

    public function searchOrders(): void
    {
        $this->showOrderDropdown = strlen($this->orderSearch) >= 2;
    }

    public function selectOrder(int $id): void
    {
        $order = Order::with('items.product')->findOrFail($id);
        $this->order_id = $order->id;
        $this->orderSearch = $order->numero . ' — $' . number_format($order->total, 2);
        $this->showOrderDropdown = false;

        $this->items = $order->items->map(fn($item) => [
            'product_id' => $item->product_id,
            'nombre_producto' => $item->product?->nombre ?? $item->nombre_producto ?? 'Producto',
            'sku' => $item->product?->sku ?? '',
            'cantidad' => $item->cantidad,
            'precio_unitario' => (float) $item->precio_unitario,
            'descuento' => (float) ($item->descuento ?? 0),
            'subtotal' => (float) $item->subtotal,
        ])->toArray();

        $this->recalcular();
    }

    public function recalcular(): void
    {
        $this->subtotal = collect($this->items)->sum('subtotal') - collect($this->items)->sum('descuento');
        $this->impuesto = $this->subtotal * 0.16;
        $this->total = $this->subtotal + $this->impuesto;
    }

    public function save(): void
    {
        $this->validate([
            'order_id' => 'required|exists:orders,id',
            'tipo' => 'required|in:factura,proforma',
        ]);

        if (empty($this->items)) {
            session()->flash('error', 'La factura debe tener al menos un item.');
            return;
        }

        DB::transaction(function () {
            $this->recalcular();

            $invoice = Invoice::create([
                'order_id' => $this->order_id,
                'tipo' => $this->tipo,
                'serie' => $this->serie ?: null,
                'numero_control' => $this->numero_control ?: null,
                'fecha_emision' => now(),
                'subtotal' => $this->subtotal,
                'impuesto' => $this->impuesto,
                'total' => $this->total,
                'notas' => $this->notas ?: null,
            ]);

            foreach ($this->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'nombre_producto' => $item['nombre_producto'],
                    'sku' => $item['sku'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'descuento' => $item['descuento'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            $this->redirect(route('admin.facturas.preview', $invoice->id), navigate: true);
        });
    }

    public function render()
    {
        return view('livewire.admin.pagos.facturas.create');
    }
}
