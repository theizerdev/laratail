<?php

namespace App\Livewire\Admin\Inventario\Proveedores;

use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Proveedores')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filter = 'all';

    // Modal state
    public ?int $editingId = null;
    public ?int $deletingId = null;

    // Form fields
    public string $nombre = '';
    public string $contacto = '';
    public string $email = '';
    public string $telefono = '';
    public string $rif = '';
    public string $direccion = '';
    public string $notas = '';
    public bool $status = true;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->dispatch('open-modal', 'modal-proveedor');
    }

    public function openEdit(int $id): void
    {
        $supplier = Supplier::findOrFail($id);
        $this->editingId = $supplier->id;
        $this->nombre = $supplier->nombre;
        $this->contacto = $supplier->contacto ?? '';
        $this->email = $supplier->email ?? '';
        $this->telefono = $supplier->telefono ?? '';
        $this->rif = $supplier->rif ?? '';
        $this->direccion = $supplier->direccion ?? '';
        $this->notas = $supplier->notas ?? '';
        $this->status = $supplier->status;
        $this->dispatch('open-modal', 'modal-proveedor');
    }

    public function closeModal(): void
    {
        $this->dispatch('close-modal', 'modal-proveedor');
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->nombre = '';
        $this->contacto = '';
        $this->email = '';
        $this->telefono = '';
        $this->rif = '';
        $this->direccion = '';
        $this->notas = '';
        $this->status = true;
        $this->editingId = null;
    }

    public function save(): void
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'contacto' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'rif' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:500',
            'notas' => 'nullable|string|max:2000',
            'status' => 'boolean',
        ];

        $this->validate($rules);

        $data = [
            'nombre' => $this->nombre,
            'contacto' => $this->contacto ?: null,
            'email' => $this->email ?: null,
            'telefono' => $this->telefono ?: null,
            'rif' => $this->rif ?: null,
            'direccion' => $this->direccion ?: null,
            'notas' => $this->notas ?: null,
            'status' => $this->status,
        ];

        if ($this->editingId) {
            $supplier = Supplier::findOrFail($this->editingId);
            $supplier->update($data);
            session()->flash('success', 'Proveedor actualizado correctamente.');
        } else {
            Supplier::create($data);
            session()->flash('success', 'Proveedor creado correctamente.');
        }

        $this->dispatch('close-modal', 'modal-proveedor');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->dispatch('open-modal', 'modal-delete-proveedor');
    }

    public function delete(): void
    {
        $supplier = Supplier::findOrFail($this->deletingId);
        $supplier->delete();
        $this->dispatch('close-modal', 'modal-delete-proveedor');
        $this->deletingId = null;
        session()->flash('success', 'Proveedor eliminado correctamente.');
    }

    public function toggleStatus(int $id): void
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update(['status' => !$supplier->status]);
    }

    public function render()
    {
        $query = Supplier::withCount('purchaseOrders')
            ->orderBy('nombre');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                    ->orWhere('contacto', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('telefono', 'like', "%{$this->search}%")
                    ->orWhere('rif', 'like', "%{$this->search}%");
            });
        }

        if ($this->filter === 'active') {
            $query->where('status', true);
        } elseif ($this->filter === 'inactive') {
            $query->where('status', false);
        }

        $suppliers = $query->paginate(15);

        $stats = [
            'total' => Supplier::count(),
            'active' => Supplier::where('status', true)->count(),
            'inactive' => Supplier::where('status', false)->count(),
            'with_orders' => Supplier::has('purchaseOrders')->count(),
        ];

        return view('livewire.admin.inventario.proveedores.index', [
            'suppliers' => $suppliers,
            'stats' => $stats,
        ]);
    }
}
