<?php

namespace App\Livewire\Admin\Ventas\Pedidos;

use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Order;
use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Pedidos')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filter = 'all';
    public string $estadoPago = '';
    public ?int $selectedOrderId = null;
    public string $nuevoEstado = '';
    public string $nuevoMetodoPago = 'efectivo';
    public ?float $montoPago = null;
    public string $referenciaPago = '';

    public ?int $empleadoAsignadoId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // Modal de Estado
    public function openModalEstado(int $id, string $estadoActual): void
    {
        $this->selectedOrderId = $id;
        $this->nuevoEstado = $estadoActual;
        $this->empleadoAsignadoId = null;
        $this->js('Flux.modal("modal-estado").show()');
    }

    public function updateEstadoModal(): void
    {
        $this->validate([
            'nuevoEstado' => 'required|string',
            'empleadoAsignadoId' => 'required_if:nuevoEstado,asignado'
        ]);
        
        $order = Order::findOrFail($this->selectedOrderId);
        $order->cambiarEstado($this->nuevoEstado);
        
        $whatsappService = app(\App\Services\WhatsAppService::class);
        $empleado = null;

        if ($this->nuevoEstado === 'asignado' && $this->empleadoAsignadoId) {
            $empleado = \App\Models\Empleado::find($this->empleadoAsignadoId);
            if ($empleado) {
                // Crear o actualizar el envío (Shipment)
                $order->shipment()->updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'empleado_id' => $empleado->id,
                        'carrier_name' => $empleado->full_name,
                        'estado' => 'preparando',
                        'empresa_id' => $order->empresa_id ?? 1,
                        'sucursal_id' => $order->sucursal_id ?? 1,
                    ]
                );
            }
        }
        
        // WhatsApp Notification
        try {
            // Notificación al Cliente
            if ($order->customer && !empty($order->customer->telefono)) {
                if ($this->nuevoEstado === 'asignado' && $empleado) {
                    $telEmp = $empleado->telefono ?? 'no disponible';
                    $message = "Hola *{$order->customer->nombre}*, tu pedido *#{$order->numero}* ha sido asignado. El encargado de tu orden será *{$empleado->full_name}* ({$empleado->cargo}). Puedes contactarlo al {$telEmp}.";
                } else {
                    $message = "Hola *{$order->customer->nombre}*, te informamos que el estado de tu pedido *#{$order->numero}* ha sido actualizado a: *{$order->estado_label}*.\n\nGracias por confiar en nosotros.";
                }
                $whatsappService->sendMessage($order->customer->telefono, $message);
            }

            // Notificación al Empleado (solo si fue asignado)
            if ($this->nuevoEstado === 'asignado' && $empleado && !empty($empleado->telefono)) {
                $dir = $order->direccion_envio ? "{$order->direccion_envio}, {$order->ciudad_envio}" : 'No especificada';
                
                $detalleItems = "";
                foreach ($order->items as $item) {
                    $detalleItems .= "- {$item->cantidad}x {$item->nombre_producto}\n";
                }

                $msgEmpleado = "Hola *{$empleado->nombre}*, se te ha asignado un nuevo pedido: *#{$order->numero}*.\nCliente: {$order->customer->nombre}\nDirección a entregar: {$dir}.\n\n*Detalle del Pedido:*\n{$detalleItems}";
                $whatsappService->sendMessage($empleado->telefono, $msgEmpleado);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error enviando notificación via WhatsApp: " . $e->getMessage());
        }

        $this->js('Flux.modal("modal-estado").close()');
        session()->flash('success', "Pedido #{$order->numero} actualizado a: {$order->estado_label}");
    }

    // Modal de Pago
    public function openModalPago(int $id): void
    {
        $order = Order::findOrFail($id);
        $this->selectedOrderId = $id;
        $pagado = $order->payments()->where('estado', 'completado')->sum('amount');
        $this->montoPago = max(0, $order->total - $pagado);
        $this->nuevoMetodoPago = 'efectivo';
        $this->referenciaPago = '';
        $this->js('Flux.modal("modal-pago").show()');
    }

    public function registrarPago(): void
    {
        $this->validate([
            'montoPago' => 'required|numeric|min:0.01',
            'nuevoMetodoPago' => 'required|string',
        ]);

        $order = Order::findOrFail($this->selectedOrderId);

        // Find open cash register for current user
        $caja = CashRegister::where('user_id', auth()->id())
            ->where('estado', 'abierta')
            ->first();

        if (!$caja) {
            session()->flash('error', 'No tienes una caja abierta. Debes abrir una caja antes de registrar pagos.');
            $this->js('Flux.modal("modal-pago").close()');
            return;
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'amount' => $this->montoPago,
            'metodo_pago' => $this->nuevoMetodoPago,
            'referencia' => $this->referenciaPago,
            'fecha_pago' => now(),
            'estado' => 'completado',
            'user_id' => auth()->id(),
            'empresa_id' => $order->empresa_id ?? 1,
            'sucursal_id' => $order->sucursal_id ?? 1,
        ]);

        // Create cash movement (ingreso) linked to the open register
        CashMovement::create([
            'cash_register_id' => $caja->id,
            'tipo' => 'ingreso',
            'monto' => $this->montoPago,
            'descripcion' => "Pago pedido #{$order->numero} — {$payment->metodo_pago_label}",
            'payment_id' => $payment->id,
            'user_id' => auth()->id(),
        ]);

        $this->js('Flux.modal("modal-pago").close()');
        session()->flash('success', 'Pago de $' . number_format($this->montoPago, 2) . " registrado para el pedido #{$order->numero}. Caja: #{$caja->id}");
    }

    public function delete(int $id): void
    {
        $order = Order::findOrFail($id);
        $order->delete();
        session()->flash('success', 'Pedido eliminado correctamente.');
    }

    public function render()
    {
        $query = Order::where('tipo', 'venta')
            ->with(['customer', 'user', 'items'])
            ->withCount('items')
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero', 'like', "%{$this->search}%")
                    ->orWhereHas('customer', fn($q2) => $q2->where('nombre', 'like', "%{$this->search}%"));
            });
        }

        if ($this->filter !== 'all') {
            $query->where('estado', $this->filter);
        }

        if ($this->estadoPago) {
            $query->where('estado_pago', $this->estadoPago);
        }

        $orders = $query->paginate(15);

        $stats = [
            'total' => Order::where('tipo', 'venta')->count(),
            'pendientes' => Order::where('tipo', 'venta')->whereIn('estado', ['pendiente', 'confirmado'])->count(),
            'completados' => Order::where('tipo', 'venta')->where('estado', 'entregado')->count(),
            'cancelados' => Order::where('tipo', 'venta')->where('estado', 'cancelado')->count(),
            'ingresos_hoy' => Order::where('tipo', 'venta')
                ->where('estado_pago', 'pagado')
                ->whereDate('created_at', today())
                ->sum('total'),
        ];

        $cajaAbierta = CashRegister::where('user_id', auth()->id())
            ->where('estado', 'abierta')
            ->first();

        return view('livewire.admin.ventas.pedidos.index', [
            'orders' => $orders,
            'stats' => $stats,
            'empleados' => \App\Models\Empleado::where('estado', 'activo')->orderBy('nombre')->get(),
            'cajaAbierta' => $cajaAbierta,
        ]);
    }
}
