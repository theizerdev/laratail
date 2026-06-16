<?php

namespace App\Livewire\Admin\Pagos\Conciliacion;

use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Conciliación Bancaria')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterMetodo = 'all';
    public string $filterConciliado = 'all';
    public ?int $conciliarId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterMetodo(): void
    {
        $this->resetPage();
    }

    public function updatingFilterConciliado(): void
    {
        $this->resetPage();
    }

    public function conciliar(int $id): void
    {
        $payment = Payment::findOrFail($id);
        $payment->update(['notas' => ($payment->notas ? $payment->notas . ' | ' : '') . 'CONCILIADO:' . now()->format('Y-m-d H:i')]);
        session()->flash('success', 'Pago marcado como conciliado.');
    }

    public function render()
    {
        $query = Payment::with(['order.customer', 'user'])
            ->where('estado', 'completado')
            ->whereIn('metodo_pago', ['transferencia', 'tarjeta', 'zelle', 'binance'])
            ->latest();

        if ($this->filterMetodo !== 'all') {
            $query->where('metodo_pago', $this->filterMetodo);
        }

        if ($this->filterConciliado === 'conciliado') {
            $query->where('notas', 'like', '%CONCILIADO%');
        } elseif ($this->filterConciliado === 'no_conciliado') {
            $query->where(function ($q) {
                $q->whereNull('notas')->orWhere('notas', 'not like', '%CONCILIADO%');
            });
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('referencia', 'like', "%{$this->search}%")
                    ->orWhereHas('order', fn($q2) => $q2->where('numero', 'like', "%{$this->search}%"));
            });
        }

        $payments = $query->paginate(15);

        $stats = [
            'total_transferencias' => Payment::where('estado', 'completado')->where('metodo_pago', 'transferencia')->sum('amount'),
            'total_tarjetas' => Payment::where('estado', 'completado')->where('metodo_pago', 'tarjeta')->sum('amount'),
            'total_digital' => Payment::whereIn('estado', ['completado'])->whereIn('metodo_pago', ['zelle', 'binance'])->sum('amount'),
            'conciliados' => Payment::where('estado', 'completado')->where('notas', 'like', '%CONCILIADO%')->count(),
        ];

        return view('livewire.admin.pagos.conciliacion.index', [
            'payments' => $payments,
            'stats' => $stats,
        ]);
    }
}
