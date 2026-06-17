<?php

namespace App\Livewire\Admin\Reportes;

use App\Models\InventoryMovement;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Reporte de Inventario')]
class Inventario extends Component
{
    public function render()
    {
        // KPIs
        $totalProductos = Product::count();

        $valorizacionTotal = Product::selectRaw('SUM(stock * COALESCE(precio_compra, 0)) as total')->value('total') ?? 0;

        $bajoStock = Product::where('rastrear_inventario', true)
            ->whereRaw('stock > 0 AND stock <= stock_minimo')
            ->count();

        $sinStock = Product::where('rastrear_inventario', true)
            ->where('stock', 0)
            ->count();

        // Productos bajo stock mínimo
        $productosBajoStock = Product::where('rastrear_inventario', true)
            ->whereRaw('stock > 0 AND stock <= stock_minimo')
            ->select('id', 'nombre', 'sku', 'stock', 'stock_minimo', 'precio', 'precio_compra')
            ->orderByRaw('stock ASC')
            ->limit(15)
            ->get();

        // Valorización por categoría
        $valorizacionPorCategoria = Product::select(
                'categories.nombre as categoria',
                DB::raw('SUM(products.stock * COALESCE(products.precio_compra, 0)) as valor_total'),
                DB::raw('SUM(products.stock) as stock_total'),
                DB::raw('COUNT(*) as total_productos')
            )
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('products.stock', '>', 0)
            ->groupBy('categories.nombre')
            ->orderByDesc('valor_total')
            ->get();

        $maxValorCategoria = $valorizacionPorCategoria->max('valor_total') ?: 1;

        // Top 10 productos con más stock
        $topStock = Product::where('stock', '>', 0)
            ->select('id', 'nombre', 'sku', 'stock', 'precio_compra', DB::raw('stock * COALESCE(precio_compra, 0) as valor'))
            ->orderByDesc('stock')
            ->limit(10)
            ->get();

        // Movimientos recientes
        $movimientosRecientes = InventoryMovement::with('product')
            ->latest()
            ->limit(20)
            ->get();

        // Resumen de movimientos por tipo
        $movimientosPorTipo = InventoryMovement::select(
                'tipo',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(cantidad) as total_unidades')
            )
            ->groupBy('tipo')
            ->get();

        $totalMovimientos = $movimientosPorTipo->sum('cantidad') ?: 1;

        $tipoColors = [
            'entrada' => 'emerald',
            'salida' => 'red',
            'ajuste' => 'amber',
            'transferencia' => 'blue',
        ];

        // Productos sin movimientos en 30+ días (estancados)
        $productIdsWithMovements = InventoryMovement::where('created_at', '>=', Carbon::now()->subDays(30))
            ->distinct('product_id')
            ->pluck('product_id');

        $productosEstancados = Product::where('rastrear_inventario', true)
            ->where('stock', '>', 0)
            ->whereNotIn('id', $productIdsWithMovements)
            ->select('id', 'nombre', 'sku', 'stock', 'precio', 'precio_compra')
            ->orderByDesc('stock')
            ->limit(15)
            ->get();

        return view('livewire.admin.reportes.inventario', compact(
            'totalProductos', 'valorizacionTotal', 'bajoStock', 'sinStock',
            'productosBajoStock',
            'valorizacionPorCategoria', 'maxValorCategoria',
            'topStock',
            'movimientosRecientes',
            'movimientosPorTipo', 'totalMovimientos', 'tipoColors',
            'productosEstancados'
        ));
    }
}
