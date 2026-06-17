<?php

namespace App\Livewire\Admin\Reportes;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('components.layouts.admin')]
#[Title('Reporte de Ventas')]
class Ventas extends Component
{
    public string $dateFrom;
    public string $dateTo;
    public string $preset = 'este_mes';

    public function mount(): void
    {
        $this->applyPreset('este_mes');
    }

    public function applyPreset(string $preset): void
    {
        $this->preset = $preset;
        $now = Carbon::now();

        $this->dateFrom = match ($preset) {
            'hoy' => $now->toDateString(),
            'esta_semana' => $now->copy()->startOfWeek()->toDateString(),
            'este_mes' => $now->copy()->startOfMonth()->toDateString(),
            'ultimo_mes' => $now->copy()->subMonth()->startOfMonth()->toDateString(),
            'este_ano' => $now->copy()->startOfYear()->toDateString(),
            default => $now->copy()->startOfMonth()->toDateString(),
        };

        $this->dateTo = match ($preset) {
            'hoy' => $now->toDateString(),
            'esta_semana' => $now->toDateString(),
            'este_mes' => $now->toDateString(),
            'ultimo_mes' => $now->copy()->subMonth()->endOfMonth()->toDateString(),
            'este_ano' => $now->toDateString(),
            default => $now->toDateString(),
        };
    }

    public function exportCsv(): StreamedResponse
    {
        $from = $this->dateFrom;
        $to = $this->dateTo;

        $orders = Order::with('customer')
            ->whereNotIn('estado', ['cancelado'])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderByDesc('created_at')
            ->get(['id', 'numero', 'customer_id', 'estado', 'estado_pago', 'total', 'created_at']);

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Número', 'Cliente', 'Estado', 'Estado Pago', 'Total', 'Fecha']);
            foreach ($orders as $o) {
                fputcsv($handle, [
                    $o->numero,
                    $o->customer?->nombre . ' ' . ($o->customer?->apellido ?? ''),
                    ucfirst($o->estado),
                    ucfirst($o->estado_pago),
                    $o->total,
                    $o->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        }, 'reporte-ventas-' . $this->dateFrom . '-' . $this->dateTo . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function render()
    {
        $from = Carbon::parse($this->dateFrom);
        $to = Carbon::parse($this->dateTo);
        $daysDiff = $from->diffInDays($to) + 1;

        // Previous period for comparison
        $prevFrom = $from->copy()->subDays($daysDiff);
        $prevTo = $from->copy()->subDay();

        // KPI: Current period
        $current = Order::whereNotIn('estado', ['cancelado'])
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(total),0) as monto, COALESCE(AVG(total),0) as ticket_promedio')
            ->first();

        // KPI: Previous period
        $previous = Order::whereNotIn('estado', ['cancelado'])
            ->whereDate('created_at', '>=', $prevFrom)
            ->whereDate('created_at', '<=', $prevTo)
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(total),0) as monto')
            ->first();

        $variacionVentas = $previous->monto > 0
            ? round((($current->monto - $previous->monto) / $previous->monto) * 100, 1)
            : ($current->monto > 0 ? 100 : 0);

        // Ventas por día
        $ventasPorDia = Order::whereNotIn('estado', ['cancelado'])
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->selectRaw('DATE(created_at) as fecha, SUM(total) as monto, COUNT(*) as cantidad')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $maxVentaDia = $ventasPorDia->max('monto') ?: 1;

        // Top 10 productos
        $topProductos = OrderItem::select(
                'order_items.nombre_producto',
                DB::raw('SUM(order_items.cantidad) as total_vendido'),
                DB::raw('SUM(order_items.subtotal) as total_ingresos')
            )
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.estado', '!=', 'cancelado')
            ->whereDate('orders.created_at', '>=', $this->dateFrom)
            ->whereDate('orders.created_at', '<=', $this->dateTo)
            ->groupBy('order_items.nombre_producto')
            ->orderByDesc('total_vendido')
            ->limit(10)
            ->get();

        // Top 10 clientes
        $topClientes = Order::select(
                DB::raw('COUNT(*) as total_pedidos'),
                DB::raw('SUM(orders.total) as total_gastado'),
                'customers.nombre',
                'customers.email'
            )
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->where('orders.estado', '!=', 'cancelado')
            ->whereDate('orders.created_at', '>=', $this->dateFrom)
            ->whereDate('orders.created_at', '<=', $this->dateTo)
            ->groupBy('orders.customer_id', 'customers.nombre', 'customers.email')
            ->orderByDesc('total_gastado')
            ->limit(10)
            ->get();

        // Ventas por estado
        $ventasPorEstado = Order::whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->select('estado', DB::raw('COUNT(*) as cantidad'), DB::raw('SUM(total) as monto'))
            ->groupBy('estado')
            ->get();

        $totalEstados = $ventasPorEstado->sum('cantidad') ?: 1;

        $estadoColors = [
            'pendiente' => 'amber',
            'procesando' => 'blue',
            'completado' => 'emerald',
            'cancelado' => 'red',
        ];

        return view('livewire.admin.reportes.ventas', compact(
            'current', 'previous', 'variacionVentas',
            'ventasPorDia', 'maxVentaDia',
            'topProductos', 'topClientes',
            'ventasPorEstado', 'totalEstados', 'estadoColors'
        ));
    }
}
