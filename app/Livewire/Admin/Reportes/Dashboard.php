<?php

namespace App\Livewire\Admin\Reportes;

use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Dashboard Analítico')]
class Dashboard extends Component
{
    public function render()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfDay = $now->copy()->startOfDay();

        // KPI Cards
        $ventasHoy = Order::where('created_at', '>=', $startOfDay)
            ->whereNotIn('estado', ['cancelado'])
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(total),0) as monto')
            ->first();

        $ventasMes = Order::where('created_at', '>=', $startOfMonth)
            ->whereNotIn('estado', ['cancelado'])
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(total),0) as monto')
            ->first();

        $ingresosMes = Payment::where('estado', 'completado')
            ->where('fecha_pago', '>=', $startOfMonth)
            ->sum('amount');

        $pedidosPendientes = Order::where('estado', 'pendiente')->count();

        $productosBajoStock = Product::where('rastrear_inventario', true)
            ->whereRaw('stock <= stock_minimo')
            ->count();

        // Top 5 productos más vendidos
        $topProductos = OrderItem::select(
                'order_items.product_id',
                'order_items.nombre_producto',
                DB::raw('SUM(order_items.cantidad) as total_vendido'),
                DB::raw('SUM(order_items.subtotal) as total_ingresos')
            )
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.estado', '!=', 'cancelado')
            ->where('orders.created_at', '>=', $startOfMonth)
            ->groupBy('order_items.product_id', 'order_items.nombre_producto')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        $maxProductoVendido = $topProductos->max('total_vendido') ?: 1;

        // Top 5 clientes
        $topClientes = Order::select(
                'orders.customer_id',
                DB::raw('COUNT(*) as total_pedidos'),
                DB::raw('SUM(orders.total) as total_gastado')
            )
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->where('orders.estado', '!=', 'cancelado')
            ->where('orders.created_at', '>=', $startOfMonth)
            ->groupBy('orders.customer_id', 'customers.nombre', 'customers.email')
            ->orderByDesc('total_gastado')
            ->limit(5)
            ->get(['orders.customer_id', 'customers.nombre', 'customers.email', DB::raw('COUNT(*) as total_pedidos'), DB::raw('SUM(orders.total) as total_gastado')]);

        // Últimos 5 pedidos
        $ultimosPedidos = Order::with('customer')
            ->latest()
            ->limit(5)
            ->get(['id', 'numero', 'customer_id', 'estado', 'total', 'created_at']);

        // Distribución de pagos por método (este mes)
        $pagosPorMetodo = Payment::where('estado', 'completado')
            ->where('fecha_pago', '>=', $startOfMonth)
            ->select('metodo_pago', DB::raw('SUM(amount) as total'))
            ->groupBy('metodo_pago')
            ->orderByDesc('total')
            ->get();

        $totalPagosMetodo = $pagosPorMetodo->sum('total') ?: 1;

        // Ventas últimos 7 días (bar chart)
        $ventas7Dias = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $ventasDia = Order::whereDate('created_at', $day->toDateString())
                ->whereNotIn('estado', ['cancelado'])
                ->sum('total');
            $ventas7Dias[] = [
                'fecha' => $day->format('d/m'),
                'monto' => (float) $ventasDia,
            ];
        }
        $maxVentaDia = max(array_column($ventas7Dias, 'monto')) ?: 1;

        return view('livewire.admin.reportes.dashboard', compact(
            'ventasHoy',
            'ventasMes',
            'ingresosMes',
            'pedidosPendientes',
            'productosBajoStock',
            'topProductos',
            'maxProductoVendido',
            'topClientes',
            'ultimosPedidos',
            'pagosPorMetodo',
            'totalPagosMetodo',
            'ventas7Dias',
            'maxVentaDia'
        ));
    }
}
