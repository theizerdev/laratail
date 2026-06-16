<?php

namespace App\Livewire\Admin\Pagos\NotasCredito;

use App\Models\CreditNote;
use App\Models\CreditNoteItem;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Nueva Nota de Crédito')]
class Create extends Component
{
    public ?int $invoice_id = null;
    public string $motivo = '';

    public string $invoiceSearch = '';
    public bool $showInvoiceDropdown = false;

    public array $items = [];
    public array $selectedItems = [];
    public float $totalMonto = 0;

    public function searchInvoices(): void
    {
        $this->showInvoiceDropdown = strlen($this->invoiceSearch) >= 2;
    }

    public function selectInvoice(int $id): void
    {
        $invoice = Invoice::with('items')->findOrFail($id);
        $this->invoice_id = $invoice->id;
        $this->invoiceSearch = $invoice->numero . ' — $' . number_format($invoice->total, 2);
        $this->showInvoiceDropdown = false;

        $this->items = $invoice->items->map(fn($item, $index) => [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'nombre_producto' => $item->nombre_producto,
            'cantidad_max' => $item->cantidad,
            'precio_unitario' => (float) $item->precio_unitario,
            'cantidad' => 0,
            'subtotal' => 0,
            'selected' => false,
        ])->toArray();

        $this->selectedItems = [];
        $this->recalcular();
    }

    public function updatedItems(): void
    {
        $this->recalcular();
    }

    public function toggleItem(int $index): void
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['selected'] = !$this->items[$index]['selected'];
            if ($this->items[$index]['selected'] && $this->items[$index]['cantidad'] == 0) {
                $this->items[$index]['cantidad'] = $this->items[$index]['cantidad_max'];
            }
            if (!$this->items[$index]['selected']) {
                $this->items[$index]['cantidad'] = 0;
            }
            $this->items[$index]['subtotal'] = $this->items[$index]['cantidad'] * $this->items[$index]['precio_unitario'];
            $this->recalcular();
        }
    }

    public function recalcular(): void
    {
        $this->totalMonto = collect($this->items)
            ->filter(fn($item) => $item['selected'])
            ->sum('subtotal');
    }

    public function save(): void
    {
        $this->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'motivo' => 'required|string|min:5',
        ]);

        $selectedItems = collect($this->items)->filter(fn($item) => $item['selected'] && $item['cantidad'] > 0);

        if ($selectedItems->isEmpty()) {
            session()->flash('error', 'Selecciona al menos un item para la nota de crédito.');
            return;
        }

        DB::transaction(function () use ($selectedItems) {
            $this->recalcular();

            $creditNote = CreditNote::create([
                'invoice_id' => $this->invoice_id,
                'motivo' => $this->motivo,
                'monto' => $this->totalMonto,
                'fecha_emision' => now(),
            ]);

            foreach ($selectedItems as $item) {
                CreditNoteItem::create([
                    'credit_note_id' => $creditNote->id,
                    'product_id' => $item['product_id'],
                    'nombre_producto' => $item['nombre_producto'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            session()->flash('success', 'Nota de crédito creada correctamente.');
            $this->redirect(route('admin.notas-credito'), navigate: true);
        });
    }

    public function render()
    {
        return view('livewire.admin.pagos.notas-credito.create');
    }
}
