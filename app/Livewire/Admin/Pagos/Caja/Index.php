<?php

namespace App\Livewire\Admin\Pagos\Caja;

use App\Models\CashRegister;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Caja')]
class Index extends Component
{
    use WithPagination;

    public string $filterEstado = 'all';
    public bool $showOpenModal = false;
    public float $monto_inicial = 0;

    public function openRegister(): void
    {
        $this->monto_inicial = 0;
        $this->showOpenModal = true;
    }

    public function crearCaja(): void
    {
        // Check if there's already an open register for this user
        $existing = CashRegister::where('user_id', auth()->id())
            ->where('estado', 'abierta')
            ->exists();

        if ($existing) {
            session()->flash('error', 'Ya tienes una caja abierta.');
            $this->showOpenModal = false;
            return;
        }

        CashRegister::create([
            'user_id' => auth()->id(),
            'fecha_apertura' => now(),
            'monto_inicial' => $this->monto_inicial,
            'estado' => 'abierta',
        ]);

        session()->flash('success', 'Caja abierta correctamente.');
        $this->showOpenModal = false;
    }

    public function render()
    {
        $query = CashRegister::with(['user', 'movements'])
            ->latest();

        if ($this->filterEstado !== 'all') {
            $query->where('estado', $this->filterEstado);
        }

        $registers = $query->paginate(15);

        $stats = [
            'abiertas' => CashRegister::where('estado', 'abierta')->count(),
            'cerradas_hoy' => CashRegister::where('estado', 'cerrada')
                ->whereDate('fecha_cierre', today())->count(),
            'ingresos_hoy' => CashRegister::where('estado', 'abierta')
                ->withSum(['movements as ingresos' => fn($q) => $q->where('tipo', 'ingreso')], 'monto')
                ->get()->sum('ingresos'),
            'total_cajas' => CashRegister::count(),
        ];

        return view('livewire.admin.pagos.caja.index', [
            'registers' => $registers,
            'stats' => $stats,
        ]);
    }
}
