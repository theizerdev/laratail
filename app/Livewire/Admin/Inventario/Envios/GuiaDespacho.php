<?php

namespace App\Livewire\Admin\Inventario\Envios;

use App\Models\Shipment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Guía de Despacho')]
class GuiaDespacho extends Component
{
    public int $shipmentId;

    public function mount(int $id): void
    {
        $this->shipmentId = $id;
    }

    public function render()
    {
        $shipment = Shipment::with('order.items.product', 'order.customer', 'empleado')->findOrFail($this->shipmentId);

        return view('livewire.admin.inventario.envios.guia-despacho', [
            'shipment' => $shipment,
        ]);
    }
}
