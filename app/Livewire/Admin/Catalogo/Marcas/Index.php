<?php

namespace App\Livewire\Admin\Catalogo\Marcas;

use App\Models\Brand;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Marcas')]
class Index extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';
    public string $filter = 'all';

    // Modal state
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    // Form fields
    public string $nombre = '';
    public string $descripcion = '';
    public $logo = null;
    public ?string $existingLogo = null;
    public string $website = '';
    public bool $status = true;

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
        $brand = Brand::findOrFail($id);
        $this->editingId = $brand->id;
        $this->nombre = $brand->nombre;
        $this->descripcion = $brand->descripcion ?? '';
        $this->existingLogo = $brand->logo;
        $this->website = $brand->website ?? '';
        $this->status = $brand->status;
        $this->logo = null;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->nombre = '';
        $this->descripcion = '';
        $this->logo = null;
        $this->existingLogo = null;
        $this->website = '';
        $this->status = true;
        $this->editingId = null;
    }

    public function removeLogo(): void
    {
        if ($this->existingLogo) {
            Storage::disk('public')->delete($this->existingLogo);
            if ($this->editingId) {
                Brand::find($this->editingId)?->update(['logo' => null]);
            }
            $this->existingLogo = null;
        }
        $this->logo = null;
    }

    public function save(): void
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
            'website' => 'nullable|url|max:255',
            'status' => 'boolean',
        ]);

        $logoPath = $this->existingLogo;
        if ($this->logo) {
            if ($this->existingLogo) {
                Storage::disk('public')->delete($this->existingLogo);
            }
            $logoPath = $this->logo->store('marcas', 'public');
        }

        $data = [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion ?: null,
            'logo' => $logoPath,
            'website' => $this->website ?: null,
            'status' => $this->status,
        ];

        if ($this->editingId) {
            Brand::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Marca actualizada correctamente.');
        } else {
            Brand::create($data);
            session()->flash('success', 'Marca creada correctamente.');
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
        $brand = Brand::findOrFail($this->deletingId);

        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();

        $this->showDeleteModal = false;
        $this->deletingId = null;
        session()->flash('success', 'Marca eliminada correctamente.');
    }

    public function toggleStatus(int $id): void
    {
        $brand = Brand::findOrFail($id);
        $brand->update(['status' => !$brand->status]);
    }

    public function render()
    {
        $query = Brand::withCount('products')
            ->orderBy('nombre');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                    ->orWhere('descripcion', 'like', "%{$this->search}%");
            });
        }

        if ($this->filter === 'active') {
            $query->where('status', true);
        } elseif ($this->filter === 'inactive') {
            $query->where('status', false);
        }

        $brands = $query->paginate(15);

        $stats = [
            'total' => Brand::count(),
            'active' => Brand::where('status', true)->count(),
            'inactive' => Brand::where('status', false)->count(),
            'total_products' => Brand::withCount('products')->get()->sum('products_count'),
            'with_products' => Brand::has('products')->count(),
        ];

        return view('livewire.admin.catalogo.marcas.index', [
            'brands' => $brands,
            'stats' => $stats,
        ]);
    }
}
