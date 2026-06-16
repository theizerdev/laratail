<?php

namespace App\Livewire\Admin\Pagos\Facturas;

use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Facturas')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterEstado = 'all';
    public string $filterTipo = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Invoice::with(['order.customer'])
            ->latest();

        if ($this->filterEstado !== 'all') {
            $query->where('estado', $this->filterEstado);
        }

        if ($this->filterTipo !== 'all') {
            $query->where('tipo', $this->filterTipo);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero', 'like', "%{$this->search}%")
                    ->orWhereHas('order.customer', fn($q2) => $q2->where('nombre', 'like', "%{$this->search}%"));
            });
        }

        $invoices = $query->paginate(15);

        $stats = [
            'total_emitidas' => Invoice::where('estado', 'emitida')->count(),
            'total_anuladas' => Invoice::where('estado', 'anulada')->count(),
            'monto_total' => Invoice::where('estado', 'emitida')->sum('total'),
            'total_facturas' => Invoice::count(),
        ];

        return view('livewire.admin.pagos.facturas.index', [
            'invoices' => $invoices,
            'stats' => $stats,
        ]);
    }
}
