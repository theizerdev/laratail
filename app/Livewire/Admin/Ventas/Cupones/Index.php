<?php

namespace App\Livewire\Admin\Ventas\Cupones;

use App\Models\Coupon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

#[Layout('components.layouts.admin')]
#[Title('Cupones')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filter = 'all';

    // Modal state
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    // Form fields
    public string $codigo = '';
    public string $nombre = '';
    public string $descripcion = '';
    public string $tipo = 'porcentaje';
    public float $valor = 0;
    public float $compra_minima = 0;
    public ?float $descuento_maximo = null;
    public ?int $usos_maximos = null;
    public ?int $usos_por_cliente = 1;
    public ?string $fecha_inicio = null;
    public ?string $fecha_fin = null;
    public bool $activo = true;
    public bool $acumular = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $coupon = Coupon::findOrFail($id);
        $this->editingId = $coupon->id;
        $this->codigo = $coupon->codigo;
        $this->nombre = $coupon->nombre;
        $this->descripcion = $coupon->descripcion ?? '';
        $this->tipo = $coupon->tipo;
        $this->valor = (float) $coupon->valor;
        $this->compra_minima = (float) $coupon->compra_minima;
        $this->descuento_maximo = $coupon->descuento_maximo ? (float) $coupon->descuento_maximo : null;
        $this->usos_maximos = $coupon->usos_maximos;
        $this->usos_por_cliente = $coupon->usos_por_cliente;
        $this->fecha_inicio = $coupon->fecha_inicio?->format('Y-m-d\TH:i');
        $this->fecha_fin = $coupon->fecha_fin?->format('Y-m-d\TH:i');
        $this->activo = $coupon->activo;
        $this->acumular = $coupon->acumular;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->codigo = '';
        $this->nombre = '';
        $this->descripcion = '';
        $this->tipo = 'porcentaje';
        $this->valor = 0;
        $this->compra_minima = 0;
        $this->descuento_maximo = null;
        $this->usos_maximos = null;
        $this->usos_por_cliente = 1;
        $this->fecha_inicio = null;
        $this->fecha_fin = null;
        $this->activo = true;
        $this->acumular = false;
        $this->editingId = null;
    }

    public function generarCodigo(): void
    {
        $this->codigo = strtoupper(Str::random(8));
    }

    public function save(): void
    {
        $rules = [
            'codigo' => 'required|string|max:255|unique:coupons,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'tipo' => 'required|in:porcentaje,fijo',
            'valor' => 'required|numeric|min:0',
            'compra_minima' => 'nullable|numeric|min:0',
            'descuento_maximo' => 'nullable|numeric|min:0',
            'usos_maximos' => 'nullable|integer|min:0',
            'usos_por_cliente' => 'nullable|integer|min:1',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'activo' => 'boolean',
            'acumular' => 'boolean',
        ];

        if ($this->editingId) {
            $rules['codigo'] = 'required|string|max:255|unique:coupons,codigo,' . $this->editingId;
        }

        $this->validate($rules);

        $data = [
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion ?: null,
            'tipo' => $this->tipo,
            'valor' => $this->valor,
            'compra_minima' => $this->compra_minima,
            'descuento_maximo' => $this->descuento_maximo,
            'usos_maximos' => $this->usos_maximos,
            'usos_por_cliente' => $this->usos_por_cliente,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'activo' => $this->activo,
            'acumular' => $this->acumular,
        ];

        if ($this->editingId) {
            Coupon::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Cupón actualizado correctamente.');
        } else {
            Coupon::create($data);
            session()->flash('success', 'Cupón creado correctamente.');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        Coupon::findOrFail($this->deletingId)->delete();
        $this->showDeleteModal = false;
        $this->deletingId = null;
        session()->flash('success', 'Cupón eliminado correctamente.');
    }

    public function toggleStatus(int $id): void
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['activo' => !$coupon->activo]);
    }

    public function render()
    {
        $query = Coupon::orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('codigo', 'like', "%{$this->search}%")
                    ->orWhere('nombre', 'like', "%{$this->search}%");
            });
        }

        if ($this->filter === 'active') {
            $query->where('activo', true);
        } elseif ($this->filter === 'inactive') {
            $query->where('activo', false);
        }

        $coupons = $query->paginate(15);

        $stats = [
            'total' => Coupon::count(),
            'active' => Coupon::where('activo', true)->count(),
            'inactive' => Coupon::where('activo', false)->count(),
            'total_usos' => Coupon::sum('usos_actuales'),
        ];

        return view('livewire.admin.ventas.cupones.index', [
            'coupons' => $coupons,
            'stats' => $stats,
        ]);
    }
}
