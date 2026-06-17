<?php

namespace App\Livewire\Admin\Reportes;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Reporte de Pagos')]
class Pagos extends Component
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

    public function render()
    {
        // Total cobrado en el período
        $totalCobrado = Payment::where('estado', 'completado')
            ->whereDate('fecha_pago', '>=', $this->dateFrom)
            ->whereDate('fecha_pago', '<=', $this->dateTo)
            ->sum('amount');

        // Total pendiente (orders with estado_pago pendiente or parcial)
        $pendientePorCobrar = Order::whereIn('estado_pago', ['pendiente', 'parcial'])
            ->whereNotIn('estado', ['cancelado'])
            ->sum(DB::raw('total - COALESCE((SELECT SUM(amount) FROM payments WHERE payments.order_id = orders.id AND payments.estado = "completado" AND payments.deleted_at IS NULL), 0)'));

        // Métodos activos en el período
        $metodosActivos = Payment::where('estado', 'completado')
            ->whereDate('fecha_pago', '>=', $this->dateFrom)
            ->whereDate('fecha_pago', '<=', $this->dateTo)
            ->distinct('metodo_pago')
            ->count('metodo_pago');

        // Tasa de cobro
        $totalOrdersPeriod = Order::whereNotIn('estado', ['cancelado'])
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->sum('total');

        $tasaCobro = $totalOrdersPeriod > 0 ? round(($totalCobrado / $totalOrdersPeriod) * 100, 1) : 0;

        // Ingresos por método de pago
        $ingresosPorMetodo = Payment::where('estado', 'completado')
            ->whereDate('fecha_pago', '>=', $this->dateFrom)
            ->whereDate('fecha_pago', '<=', $this->dateTo)
            ->select('metodo_pago', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as cantidad'))
            ->groupBy('metodo_pago')
            ->orderByDesc('total')
            ->get();

        $totalIngresosMetodo = $ingresosPorMetodo->sum('total') ?: 1;

        // Cobros por día
        $cobrosPorDia = Payment::where('estado', 'completado')
            ->whereDate('fecha_pago', '>=', $this->dateFrom)
            ->whereDate('fecha_pago', '<=', $this->dateTo)
            ->selectRaw('DATE(fecha_pago) as fecha, SUM(amount) as monto, COUNT(*) as cantidad')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $maxCobroDia = $cobrosPorDia->max('monto') ?: 1;

        // Resumen por estado
        $pagosPorEstado = Payment::whereDate('fecha_pago', '>=', $this->dateFrom)
            ->whereDate('fecha_pago', '<=', $this->dateTo)
            ->select('estado', DB::raw('COUNT(*) as cantidad'), DB::raw('SUM(amount) as monto'))
            ->groupBy('estado')
            ->get();

        $totalPagosEstados = $pagosPorEstado->sum('cantidad') ?: 1;

        $pagoEstadoColors = [
            'pendiente' => 'amber',
            'completado' => 'emerald',
            'fallido' => 'red',
            'reembolsado' => 'blue',
        ];

        // Últimos pagos
        $ultimosPagos = Payment::with('order')
            ->whereDate('fecha_pago', '>=', $this->dateFrom)
            ->whereDate('fecha_pago', '<=', $this->dateTo)
            ->latest('fecha_pago')
            ->limit(15)
            ->get();

        // Facturación stats
        $facturasEmitidas = Invoice::where('tipo', 'factura')
            ->where('estado', 'emitida')
            ->whereDate('fecha_emision', '>=', $this->dateFrom)
            ->whereDate('fecha_emision', '<=', $this->dateTo)
            ->count();

        $facturasAnuladas = Invoice::where('tipo', 'factura')
            ->where('estado', 'anulada')
            ->whereDate('fecha_emision', '>=', $this->dateFrom)
            ->whereDate('fecha_emision', '<=', $this->dateTo)
            ->count();

        $proformas = Invoice::where('tipo', 'proforma')
            ->whereDate('fecha_emision', '>=', $this->dateFrom)
            ->whereDate('fecha_emision', '<=', $this->dateTo)
            ->count();

        $totalFacturado = Invoice::where('tipo', 'factura')
            ->where('estado', 'emitida')
            ->whereDate('fecha_emision', '>=', $this->dateFrom)
            ->whereDate('fecha_emision', '<=', $this->dateTo)
            ->sum('total');

        return view('livewire.admin.reportes.pagos', compact(
            'totalCobrado', 'pendientePorCobrar', 'metodosActivos', 'tasaCobro',
            'ingresosPorMetodo', 'totalIngresosMetodo',
            'cobrosPorDia', 'maxCobroDia',
            'pagosPorEstado', 'totalPagosEstados', 'pagoEstadoColors',
            'ultimosPagos',
            'facturasEmitidas', 'facturasAnuladas', 'proformas', 'totalFacturado'
        ));
    }
}
