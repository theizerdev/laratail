<?php

namespace App\Livewire\Admin\Inventario\Envios;

use App\Models\Shipment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Editar Envío')]
class Edit extends Component
{
    public int $shipmentId;
    public ?int $order_id = null;
    public string $estado = 'preparando';
    public string $tipo_transporte = 'carrier'; // 'carrier' o 'empleado'
    public ?int $empleado_id = null;
    public string $carrier_name = '';
    public string $tracking_number = '';
    public string $fecha_envio = '';
    public string $fecha_entrega_esperada = '';
    public ?float $peso = null;
    public ?float $costo_envio = null;
    public string $direccion_destino = '';
    public string $ciudad_destino = '';
    public string $estado_destino = '';
    public string $codigo_postal_destino = '';
    public string $notas = '';

    public function mount(int $id): void
    {
        $this->shipmentId = $id;
        $shipment = Shipment::findOrFail($id);

        $this->order_id = $shipment->order_id;
        $this->estado = $shipment->estado;
        $this->empleado_id = $shipment->empleado_id;
        $this->tipo_transporte = $shipment->empleado_id ? 'empleado' : 'carrier';
        $this->carrier_name = $shipment->carrier_name ?? '';
        $this->tracking_number = $shipment->tracking_number ?? '';
        $this->fecha_envio = $shipment->fecha_envio?->format('Y-m-d') ?? '';
        $this->fecha_entrega_esperada = $shipment->fecha_entrega_esperada?->format('Y-m-d') ?? '';
        $this->peso = $shipment->peso ? (float) $shipment->peso : null;
        $this->costo_envio = $shipment->costo_envio ? (float) $shipment->costo_envio : null;
        $this->direccion_destino = $shipment->direccion_destino ?? '';
        $this->ciudad_destino = $shipment->ciudad_destino ?? '';
        $this->estado_destino = $shipment->estado_destino ?? '';
        $this->codigo_postal_destino = $shipment->codigo_postal_destino ?? '';
        $this->notas = $shipment->notas ?? '';
    }

    public function cambiarEstado(string $nuevoEstado): void
    {
        $shipment = Shipment::findOrFail($this->shipmentId);
        $shipment->cambiarEstado($nuevoEstado);
        $this->estado = $nuevoEstado;
        session()->flash('success', "Estado cambiado a: {$shipment->estado_label}");
    }

    public function save(): void
    {
        $this->validate([
            'tipo_transporte' => 'required|string|in:carrier,empleado',
            'empleado_id' => 'required_if:tipo_transporte,empleado|nullable|exists:empleados,id',
            'carrier_name' => 'required_if:tipo_transporte,carrier|nullable|string|max:255',
            'tracking_number' => 'nullable|string|max:255',
            'fecha_envio' => 'nullable|date',
            'fecha_entrega_esperada' => 'nullable|date',
            'peso' => 'nullable|numeric|min:0',
            'costo_envio' => 'nullable|numeric|min:0',
            'direccion_destino' => 'nullable|string|max:500',
            'ciudad_destino' => 'nullable|string|max:255',
            'estado_destino' => 'nullable|string|max:255',
            'codigo_postal_destino' => 'nullable|string|max:20',
            'notas' => 'nullable|string|max:2000',
        ]);

        $shipment = Shipment::findOrFail($this->shipmentId);

        if ($this->tipo_transporte === 'empleado') {
            $empleado = \App\Models\Empleado::findOrFail($this->empleado_id);
            $carrierName = $empleado->full_name;
            $trackingNumber = null;
            $empleadoId = $empleado->id;
        } else {
            $carrierName = $this->carrier_name ?: null;
            $trackingNumber = $this->tracking_number ?: null;
            $empleadoId = null;
        }

        $shipment->update([
            'empleado_id' => $empleadoId,
            'carrier_name' => $carrierName,
            'tracking_number' => $trackingNumber,
            'fecha_envio' => $this->fecha_envio ?: null,
            'fecha_entrega_esperada' => $this->fecha_entrega_esperada ?: null,
            'peso' => $this->peso,
            'costo_envio' => $this->costo_envio,
            'direccion_destino' => $this->direccion_destino ?: null,
            'ciudad_destino' => $this->ciudad_destino ?: null,
            'estado_destino' => $this->estado_destino ?: null,
            'codigo_postal_destino' => $this->codigo_postal_destino ?: null,
            'notas' => $this->notas ?: null,
        ]);

        session()->flash('success', 'Envío actualizado correctamente.');
        $this->redirect(route('admin.envios'), navigate: true);
    }

    public function render()
    {
        $shipment = Shipment::with('order.customer')->findOrFail($this->shipmentId);

        return view('livewire.admin.inventario.envios.edit', [
            'shipment' => $shipment,
            'empleados' => \App\Models\Empleado::where('estado', 'activo')->orderBy('nombre')->get(),
        ]);
    }
}
