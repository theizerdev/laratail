<?php

namespace App\Livewire\Admin\Ventas\Pedidos;

use App\Models\Order;
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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function cambiarEstado(int $id, string $estado): void
    {
        $order = Order::findOrFail($id);
        $order->cambiarEstado($estado);
        session()->flash('success', "Pedido actualizado a: {$order->estado_label}");
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

        return view('livewire.admin.ventas.pedidos.index', [
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }
}
