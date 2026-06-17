<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\Customer;
use App\Models\Invoice;
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
#[Title('Dashboard')]
class Index extends Component
{
    public function render()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfDay = $now->copy()->startOfDay();
        $startOfYear = $now->copy()->startOfYear();

        // ── KPI Cards ──
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

        // ── Monthly Revenue Chart (last 12 months) ──
        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $revenue = Order::whereNotIn('estado', ['cancelado'])
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total');
            $monthlyRevenue[] = [
                'month' => $month->format('M'),
                'revenue' => (float) $revenue,
            ];
        }

        // ── Sales by Status (Donut) ──
        $ventasPorEstado = Order::where('created_at', '>=', $startOfMonth)
            ->select('estado', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('estado')
            ->get();

        $estadoLabels = [];
        $estadoSeries = [];
        $estadoMap = [
            'pendiente' => 'Pendiente',
            'procesando' => 'Procesando',
            'completado' => 'Completado',
            'cancelado' => 'Cancelado',
        ];
        foreach ($ventasPorEstado as $e) {
            $estadoLabels[] = $estadoMap[$e->estado] ?? ucfirst($e->estado);
            $estadoSeries[] = (int) $e->cantidad;
        }

        // ── Payments by Method (Donut) ──
        $pagosPorMetodo = Payment::where('estado', 'completado')
            ->where('fecha_pago', '>=', $startOfMonth)
            ->select('metodo_pago', DB::raw('SUM(amount) as total'))
            ->groupBy('metodo_pago')
            ->orderByDesc('total')
            ->get();

        $metodoLabels = $pagosPorMetodo->map(fn($p) => ucfirst(str_replace('_', ' ', $p->metodo_pago)))->toArray();
        $metodoSeries = $pagosPorMetodo->pluck('total')->map(fn($v) => (float) $v)->toArray();

        // ── Daily Sales (last 30 days - Area chart) ──
        $dailySales = [];
        $dailyPayments = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $sales = Order::whereDate('created_at', $day->toDateString())
                ->whereNotIn('estado', ['cancelado'])
                ->sum('total');
            $payments = Payment::whereDate('fecha_pago', $day->toDateString())
                ->where('estado', 'completado')
                ->sum('amount');
            $dailySales[] = [
                'date' => $day->format('d M'),
                'sales' => (float) $sales,
                'payments' => (float) $payments,
            ];
        }

        // ── Top 5 Productos ──
        $topProductos = OrderItem::select(
                'order_items.nombre_producto',
                DB::raw('SUM(order_items.cantidad) as total_vendido'),
                DB::raw('SUM(order_items.subtotal) as total_ingresos')
            )
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.estado', '!=', 'cancelado')
            ->where('orders.created_at', '>=', $startOfMonth)
            ->groupBy('order_items.nombre_producto')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        // ── Top 5 Clientes ──
        $topClientes = Order::select(
                'customers.nombre',
                'customers.email',
                DB::raw('COUNT(*) as total_pedidos'),
                DB::raw('SUM(orders.total) as total_gastado')
            )
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->where('orders.estado', '!=', 'cancelado')
            ->where('orders.created_at', '>=', $startOfMonth)
            ->groupBy('orders.customer_id', 'customers.nombre', 'customers.email')
            ->orderByDesc('total_gastado')
            ->limit(5)
            ->get();

        // ── Últimos Pedidos ──
        $ultimosPedidos = Order::with('customer')
            ->latest()
            ->limit(5)
            ->get(['id', 'numero', 'customer_id', 'estado', 'total', 'created_at']);

        // ── Facturación rápida ──
        $facturasMes = Invoice::where('tipo', 'factura')
            ->where('estado', 'emitida')
            ->whereDate('fecha_emision', '>=', $startOfMonth)
            ->count();

        $totalFacturado = Invoice::where('tipo', 'factura')
            ->where('estado', 'emitida')
            ->whereDate('fecha_emision', '>=', $startOfMonth)
            ->sum('total');

        return view('livewire.admin.dashboard.index', compact(
            'ventasHoy', 'ventasMes', 'ingresosMes', 'pedidosPendientes',
            'monthlyRevenue',
            'estadoLabels', 'estadoSeries',
            'metodoLabels', 'metodoSeries',
            'dailySales',
            'topProductos', 'topClientes', 'ultimosPedidos',
            'facturasMes', 'totalFacturado'
        ));
    }
}
