<?php

namespace App\Livewire\Admin\Sucursales;

use App\Models\Empresa;
use App\Models\Sucursal;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Sucursales')]
class Index extends Component
{
    public string $search = '';
    public string $filter = 'all'; // all, active, inactive
    public ?int $filterEmpresa = null;

    public function delete(int $id): void
    {
        $sucursal = Sucursal::findOrFail($id);
        $sucursal->delete();

        session()->flash('success', 'Sucursal eliminada correctamente.');
    }

    public function toggleStatus(int $id): void
    {
        $sucursal = Sucursal::findOrFail($id);
        $sucursal->update(['status' => ! $sucursal->status]);
    }

    public function render()
    {
        $sucursales = Sucursal::query()
            ->with('empresa')
            ->when($this->filterEmpresa, fn ($q) => $q->where('empresa_id', $this->filterEmpresa))
            ->when($this->search, function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                    ->orWhere('telefono', 'like', "%{$this->search}%")
                    ->orWhere('direccion', 'like', "%{$this->search}%");
            })
            ->when($this->filter === 'active', fn ($q) => $q->where('status', true))
            ->when($this->filter === 'inactive', fn ($q) => $q->where('status', false))
            ->orderBy('nombre')
            ->get();

        $empresas = Empresa::orderBy('razon_social')->get();

        $totalSucursales = Sucursal::count();
        $sucursalesActivas = Sucursal::where('status', true)->count();

        $stats = [
            'total_sucursales' => $totalSucursales,
            'sucursales_activas' => $sucursalesActivas,
            'sucursales_inactivas' => $totalSucursales - $sucursalesActivas,
        ];

        return view('livewire.admin.sucursales.index', [
            'sucursales' => $sucursales,
            'empresas' => $empresas,
            'stats' => $stats,
        ]);
    }
}
