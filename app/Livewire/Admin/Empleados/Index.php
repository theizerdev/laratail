<?php

namespace App\Livewire\Admin\Empleados;

use App\Models\Empleado;
use App\Models\Sucursal;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Empleados')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    
    // Modal properties
    public ?int $empleadoId = null;
    public string $nombre = '';
    public string $apellidos = '';
    public string $documento = '';
    public string $email = '';
    public string $telefono = '';
    public string $cargo = '';
    public ?string $fecha_ingreso = null;
    public ?float $salario = null;
    public string $estado = 'activo';
    public ?int $sucursal_id = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset([
            'empleadoId', 'nombre', 'apellidos', 'documento', 'email', 
            'telefono', 'cargo', 'fecha_ingreso', 'salario', 'estado', 'sucursal_id'
        ]);
        $this->estado = 'activo';
        $this->resetValidation();
        $this->js('Flux.modal("empleado-modal").show()');
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $empleado = Empleado::findOrFail($id);
        
        $this->empleadoId = $empleado->id;
        $this->nombre = $empleado->nombre;
        $this->apellidos = $empleado->apellidos ?? '';
        $this->documento = $empleado->documento;
        $this->email = $empleado->email ?? '';
        $this->telefono = $empleado->telefono ?? '';
        $this->cargo = $empleado->cargo;
        $this->fecha_ingreso = $empleado->fecha_ingreso ? $empleado->fecha_ingreso->format('Y-m-d') : null;
        $this->salario = $empleado->salario;
        $this->estado = $empleado->estado;
        $this->sucursal_id = $empleado->sucursal_id;

        $this->js('Flux.modal("empleado-modal").show()');
    }

    public function save(): void
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'documento' => 'required|string|max:50|unique:empleados,documento,' . $this->empleadoId,
            'email' => 'nullable|email|max:255|unique:empleados,email,' . $this->empleadoId,
            'telefono' => 'nullable|string|max:20',
            'cargo' => 'required|string|max:255',
            'fecha_ingreso' => 'nullable|date',
            'salario' => 'nullable|numeric|min:0',
            'estado' => 'required|in:activo,inactivo',
            'sucursal_id' => 'nullable|integer|exists:sucursales,id',
        ];

        $this->validate($rules);

        Empleado::updateOrCreate(
            ['id' => $this->empleadoId],
            [
                'nombre' => $this->nombre,
                'apellidos' => $this->apellidos ?: null,
                'documento' => $this->documento,
                'email' => $this->email ?: null,
                'telefono' => $this->telefono ?: null,
                'cargo' => $this->cargo,
                'fecha_ingreso' => $this->fecha_ingreso ?: null,
                'salario' => $this->salario !== '' ? $this->salario : null,
                'estado' => $this->estado,
                'sucursal_id' => $this->sucursal_id ?: null,
                'empresa_id' => auth()->user()->empresa_id ?? 1,
            ]
        );

        $this->js('Flux.modal("empleado-modal").close()');
        session()->flash('success', $this->empleadoId ? 'Empleado actualizado correctamente.' : 'Empleado creado correctamente.');
    }

    public function delete(int $id): void
    {
        Empleado::findOrFail($id)->delete();
        session()->flash('success', 'Empleado eliminado correctamente.');
    }

    public function render()
    {
        $query = Empleado::where('empresa_id', auth()->user()->empresa_id ?? 1)
            ->with(['sucursal'])
            ->orderBy('nombre');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('apellidos', 'like', "%{$this->search}%")
                  ->orWhere('documento', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.admin.empleados.index', [
            'empleados' => $query->paginate(15),
            'sucursales' => Sucursal::all(),
        ]);
    }
}
