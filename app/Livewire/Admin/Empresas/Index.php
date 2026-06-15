<?php

namespace App\Livewire\Admin\Empresas;

use App\Models\Empresa;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Empresas')]
class Index extends Component
{
    public string $search = '';
    public string $filter = 'all'; // all, active, inactive

    public function delete(int $id): void
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->delete();

        session()->flash('success', 'Empresa eliminada correctamente.');
    }

    public function toggleStatus(int $id): void
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->update(['status' => ! $empresa->status]);
    }

    public function render()
    {
        $empresas = Empresa::query()
            ->with('pais')
            ->when($this->search, function ($q) {
                $q->where('razon_social', 'like', "%{$this->search}%")
                    ->orWhere('documento', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->when($this->filter === 'active', fn ($q) => $q->where('status', true))
            ->when($this->filter === 'inactive', fn ($q) => $q->where('status', false))
            ->orderBy('razon_social')
            ->get();

        $totalEmpresas = Empresa::count();
        $empresasActivas = Empresa::where('status', true)->count();

        $stats = [
            'total_empresas' => $totalEmpresas,
            'empresas_activas' => $empresasActivas,
            'empresas_inactivas' => $totalEmpresas - $empresasActivas,
        ];

        return view('livewire.admin.empresas.index', [
            'empresas' => $empresas,
            'stats' => $stats,
        ]);
    }
}
