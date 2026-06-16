<?php

namespace App\Livewire\Admin\Pagos\NotasCredito;

use App\Models\CreditNote;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Notas de Crédito')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterEstado = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = CreditNote::with(['invoice.order.customer'])
            ->latest();

        if ($this->filterEstado !== 'all') {
            $query->where('estado', $this->filterEstado);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero', 'like', "%{$this->search}%")
                    ->orWhere('motivo', 'like', "%{$this->search}%");
            });
        }

        $creditNotes = $query->paginate(15);

        $stats = [
            'total_emitidas' => CreditNote::where('estado', 'emitida')->count(),
            'total_anuladas' => CreditNote::where('estado', 'anulada')->count(),
            'monto_total' => CreditNote::where('estado', 'emitida')->sum('monto'),
            'total_notas' => CreditNote::count(),
        ];

        return view('livewire.admin.pagos.notas-credito.index', [
            'creditNotes' => $creditNotes,
            'stats' => $stats,
        ]);
    }
}
