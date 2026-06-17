<?php

namespace App\Livewire\Admin\Pagos\Caja;

use App\Models\CashMovement;
use App\Models\CashRegister;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Detalle de Caja')]
class Detalle extends Component
{
    public int $registerId;

    public function mount(int $id): void
    {
        $this->registerId = $id;
    }

    // Movement form
    public string $movTipo = 'ingreso';
    public float $movMonto = 0;
    public string $movDescripcion = '';

    // Close form
    public float $monto_final = 0;
    public string $notas_cierre = '';

    public function openMovement(): void
    {
        $this->movTipo = 'ingreso';
        $this->movMonto = 0;
        $this->movDescripcion = '';
        $this->js('Flux.modal("modal-movimiento").show()');
    }

    public function saveMovement(): void
    {
        $this->validate([
            'movTipo' => 'required|in:ingreso,egreso',
            'movMonto' => 'required|numeric|min:0.01',
        ]);

        $register = CashRegister::findOrFail($this->registerId);
        if ($register->estado !== 'abierta') {
            session()->flash('error', 'La caja no está abierta.');
            return;
        }

        CashMovement::create([
            'cash_register_id' => $register->id,
            'tipo' => $this->movTipo,
            'monto' => $this->movMonto,
            'descripcion' => $this->movDescripcion,
            'user_id' => auth()->id(),
        ]);

        session()->flash('success', 'Movimiento registrado.');
        $this->js('Flux.modal("modal-movimiento").close()');
    }

    public function openClose(): void
    {
        $register = CashRegister::findOrFail($this->registerId);
        $this->monto_final = $register->totalActual();
        $this->js('Flux.modal("modal-cerrar-caja").show()');
    }

    public function cerrarCaja(): void
    {
        $this->validate([
            'monto_final' => 'required|numeric|min:0',
        ]);

        $register = CashRegister::findOrFail($this->registerId);
        if ($register->estado !== 'abierta') {
            session()->flash('error', 'La caja no está abierta.');
            return;
        }

        $register->notas = $this->notas_cierre;
        $register->cerrar($this->monto_final);

        session()->flash('success', 'Caja cerrada correctamente.');
        $this->js('Flux.modal("modal-cerrar-caja").close()');
        $this->redirect(route('admin.caja'), navigate: true);
    }

    public function render()
    {
        $register = CashRegister::with(['user', 'movements.user', 'movements.payment'])
            ->findOrFail($this->registerId);

        return view('livewire.admin.pagos.caja.detalle', [
            'register' => $register,
        ]);
    }
}
