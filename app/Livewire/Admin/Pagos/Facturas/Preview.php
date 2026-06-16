<?php

namespace App\Livewire\Admin\Pagos\Facturas;

use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Vista Previa de Factura')]
class Preview extends Component
{
    public int $invoiceId;

    public function mount(int $id): void
    {
        $this->invoiceId = $id;
    }

    public function render()
    {
        $invoice = Invoice::with(['items.product', 'order.customer', 'order.items.product', 'empresa', 'sucursal'])
            ->findOrFail($this->invoiceId);

        return view('livewire.admin.pagos.facturas.preview', [
            'invoice' => $invoice,
        ]);
    }
}
