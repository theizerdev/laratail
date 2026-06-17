<?php

namespace App\Livewire\Admin\Inventario\Envios;

use App\Models\Shipment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Envíos')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterEstado = 'all';
    public string $filterCarrier = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Shipment::with(['order.customer', 'empleado'])
            ->latest();

        if ($this->filterEstado !== 'all') {
            $query->where('estado', $this->filterEstado);
        }

        if ($this->filterCarrier) {
            $query->where('carrier_name', 'like', "%{$this->filterCarrier}%");
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero', 'like', "%{$this->search}%")
                    ->orWhere('tracking_number', 'like', "%{$this->search}%")
                    ->orWhereHas('order', fn($o) => $o->where('numero', 'like', "%{$this->search}%"));
            });
        }

        $shipments = $query->paginate(15);

        $stats = [
            'total' => Shipment::count(),
            'preparando' => Shipment::where('estado', 'preparando')->count(),
            'en_transito' => Shipment::whereIn('estado', ['enviado', 'en_transito'])->count(),
            'entregados' => Shipment::where('estado', 'entregado')->count(),
        ];

        $carriers = Shipment::whereNotNull('carrier_name')
            ->distinct()->pluck('carrier_name')->sort()->values();

        return view('livewire.admin.inventario.envios.index', [
            'shipments' => $shipments,
            'stats' => $stats,
            'carriers' => $carriers,
        ]);
    }
}
