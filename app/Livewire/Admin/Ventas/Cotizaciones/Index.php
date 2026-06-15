<?php

namespace App\Livewire\Admin\Ventas\Cotizaciones;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Cotizaciones')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function convertirAPedido(int $id): void
    {
        $cotizacion = Order::where('tipo', 'cotizacion')->findOrFail($id);
        $cotizacion->update([
            'tipo' => 'venta',
            'numero' => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4)),
            'estado' => 'pendiente',
        ]);
        session()->flash('success', "Cotización convertida a pedido: {$cotizacion->numero}");
    }

    public function delete(int $id): void
    {
        Order::where('tipo', 'cotizacion')->findOrFail($id)->delete();
        session()->flash('success', 'Cotización eliminada correctamente.');
    }

    public function render()
    {
        $query = Order::where('tipo', 'cotizacion')
            ->with(['customer', 'user'])
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

        $cotizaciones = $query->paginate(15);

        $stats = [
            'total' => Order::where('tipo', 'cotizacion')->count(),
            'pendientes' => Order::where('tipo', 'cotizacion')->whereIn('estado', ['borrador', 'pendiente'])->count(),
            'convertidas' => Order::where('tipo', 'cotizacion')->where('estado', 'confirmado')->count(),
        ];

        return view('livewire.admin.ventas.cotizaciones.index', [
            'cotizaciones' => $cotizaciones,
            'stats' => $stats,
        ]);
    }
}
