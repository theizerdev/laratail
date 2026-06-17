<?php

namespace App\Livewire\Admin\Inventario\Envios;

use App\Models\Order;
use App\Models\Shipment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Nuevo Envío')]
class Create extends Component
{
    public ?int $order_id = null;
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

    // Order search
    public string $orderSearch = '';
    public bool $showOrderDropdown = false;

    public function mount(): void
    {
        $this->fecha_envio = now()->format('Y-m-d');
    }

    public function searchOrders(): void
    {
        $this->showOrderDropdown = strlen($this->orderSearch) >= 2;
    }

    public function selectOrder(int $id): void
    {
        $order = Order::with('customer')->findOrFail($id);
        $this->order_id = $order->id;
        $this->orderSearch = $order->numero;
        $this->showOrderDropdown = false;

        // Auto-fill address
        $this->direccion_destino = $order->direccion_envio ?? '';
        $this->ciudad_destino = $order->ciudad_envio ?? '';
        $this->estado_destino = $order->estado_envio ?? '';
        $this->codigo_postal_destino = $order->codigo_postal_envio ?? '';
    }

    public function save(string $estado = 'preparando'): void
    {
        $this->validate([
            'order_id' => 'required|exists:orders,id',
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

        Shipment::create([
            'order_id' => $this->order_id,
            'empleado_id' => $empleadoId,
            'carrier_name' => $carrierName,
            'tracking_number' => $trackingNumber,
            'estado' => $estado,
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

        session()->flash('success', 'Envío creado correctamente.');
        $this->redirect(route('admin.envios'), navigate: true);
    }

    public function render()
    {
        $searchResults = [];
        if ($this->showOrderDropdown) {
            $searchResults = Order::where('numero', 'like', "%{$this->orderSearch}%")
                ->with('customer')
                ->limit(8)->get(['id', 'numero', 'customer_id', 'direccion_envio', 'ciudad_envio']);
        }

        $selectedOrder = null;
        if ($this->order_id) {
            $selectedOrder = Order::with('customer')->find($this->order_id);
        }

        return view('livewire.admin.inventario.envios.create', [
            'searchResults' => $searchResults,
            'selectedOrder' => $selectedOrder,
            'empleados' => \App\Models\Empleado::where('estado', 'activo')->orderBy('nombre')->get(),
        ]);
    }
}
