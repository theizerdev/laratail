<?php

namespace App\Livewire\Admin\Sucursales;

use App\Models\Empresa;
use App\Models\Sucursal;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Crear Sucursal')]
class Create extends Component
{
    public ?int $empresa_id = null;
    public string $nombre = '';
    public string $telefono = '';
    public string $direccion = '';
    public ?float $latitud = null;
    public ?float $longitud = null;
    public bool $status = true;

    protected function rules(): array
    {
        return [
            'empresa_id' => 'required|exists:empresas,id',
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'status' => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        Sucursal::create([
            'empresa_id' => $this->empresa_id,
            'nombre' => $this->nombre,
            'telefono' => $this->telefono ?: null,
            'direccion' => $this->direccion ?: null,
            'latitud' => $this->latitud,
            'longitud' => $this->longitud,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Sucursal creada correctamente.');

        $this->redirect(route('admin.sucursales'), navigate: true);
    }

    public function render()
    {
        $empresas = Empresa::where('status', true)->orderBy('razon_social')->get();

        return view('livewire.admin.sucursales.create', [
            'empresas' => $empresas,
        ]);
    }
}
