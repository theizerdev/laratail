<?php

namespace App\Livewire\Admin\Inventario\AlertasStock;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Alertas de Stock Bajo')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $severity = 'all'; // all, critical, warning
    public string $sortBy = 'stock_asc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::with(['category', 'brand'])
            ->where('rastrear_inventario', true)
            ->whereColumn('stock', '<=', 'stock_minimo');

        if ($this->severity === 'critical') {
            $query->where('stock', 0);
        } elseif ($this->severity === 'warning') {
            $query->where('stock', '>', 0);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%");
            });
        }

        $query = match ($this->sortBy) {
            'stock_desc' => $query->orderBy('stock', 'desc'),
            'nombre' => $query->orderBy('nombre'),
            'stock_asc' => $query->orderBy('stock', 'asc'),
            default => $query->orderBy('stock', 'asc'),
        };

        $products = $query->paginate(20);

        $stats = [
            'total_alertas' => Product::where('rastrear_inventario', true)->whereColumn('stock', '<=', 'stock_minimo')->count(),
            'criticos' => Product::where('rastrear_inventario', true)->whereColumn('stock', '<=', 'stock_minimo')->where('stock', 0)->count(),
            'advertencia' => Product::where('rastrear_inventario', true)->whereColumn('stock', '<=', 'stock_minimo')->where('stock', '>', 0)->count(),
            'sin_inventario' => Product::where('rastrear_inventario', false)->where('stock', 0)->count(),
        ];

        return view('livewire.admin.inventario.alertas-stock.index', [
            'products' => $products,
            'stats' => $stats,
        ]);
    }
}
