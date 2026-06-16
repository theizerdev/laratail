<?php

namespace App\Livewire\Admin\Inventario\OrdenesCompra;

use App\Models\PurchaseOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Órdenes de Compra')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterEstado = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterEstado(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = PurchaseOrder::with(['supplier', 'user', 'items'])
            ->latest();

        if ($this->filterEstado !== 'all') {
            $query->where('estado', $this->filterEstado);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero', 'like', "%{$this->search}%")
                    ->orWhereHas('supplier', fn($s) => $s->where('nombre', 'like', "%{$this->search}%"));
            });
        }

        $orders = $query->paginate(15);

        $stats = [
            'total' => PurchaseOrder::count(),
            'borrador' => PurchaseOrder::where('estado', 'borrador')->count(),
            'pendientes' => PurchaseOrder::whereIn('estado', ['enviada', 'aprobada'])->count(),
            'recibidas' => PurchaseOrder::where('estado', 'recibida')->count(),
        ];

        return view('livewire.admin.inventario.ordenes-compra.index', [
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }
}
