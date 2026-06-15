<?php

namespace App\Livewire\Admin\Catalogo\Categorias;

use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Categorías')]
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
    public ?int $parent_id = null;
    public string $descripcion = '';
    public $imagen = null;
    public ?string $existingImage = null;
    public int $orden = 0;
    public string $meta_title = '';
    public string $meta_description = '';
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
        $category = Category::findOrFail($id);
        $this->editingId = $category->id;
        $this->nombre = $category->nombre;
        $this->parent_id = $category->parent_id;
        $this->descripcion = $category->descripcion ?? '';
        $this->existingImage = $category->imagen;
        $this->orden = $category->orden;
        $this->meta_title = $category->meta_title ?? '';
        $this->meta_description = $category->meta_description ?? '';
        $this->status = $category->status;
        $this->imagen = null;
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
        $this->parent_id = null;
        $this->descripcion = '';
        $this->imagen = null;
        $this->existingImage = null;
        $this->orden = 0;
        $this->meta_title = '';
        $this->meta_description = '';
        $this->status = true;
        $this->editingId = null;
    }

    public function removeImage(): void
    {
        if ($this->existingImage) {
            Storage::disk('public')->delete($this->existingImage);
            if ($this->editingId) {
                Category::find($this->editingId)?->update(['imagen' => null]);
            }
            $this->existingImage = null;
        }
        $this->imagen = null;
    }

    public function save(): void
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'descripcion' => 'nullable|string|max:1000',
            'imagen' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'orden' => 'integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'status' => 'boolean',
        ];

        if ($this->editingId) {
            $rules['parent_id'] = 'nullable|exists:categories,id|not_in:' . $this->editingId;
        }

        $this->validate($rules);

        $imagenPath = $this->existingImage;
        if ($this->imagen) {
            if ($this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }
            $imagenPath = $this->imagen->store('categorias', 'public');
        }

        $data = [
            'nombre' => $this->nombre,
            'parent_id' => $this->parent_id ?: null,
            'descripcion' => $this->descripcion ?: null,
            'imagen' => $imagenPath,
            'orden' => $this->orden,
            'meta_title' => $this->meta_title ?: null,
            'meta_description' => $this->meta_description ?: null,
            'status' => $this->status,
        ];

        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Categoría actualizada correctamente.');
        } else {
            Category::create($data);
            session()->flash('success', 'Categoría creada correctamente.');
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
        $category = Category::findOrFail($this->deletingId);

        if ($category->imagen) {
            Storage::disk('public')->delete($category->imagen);
        }

        // Reassign children to parent
        Category::where('parent_id', $category->id)->update(['parent_id' => $category->parent_id]);

        $category->delete();

        $this->showDeleteModal = false;
        $this->deletingId = null;
        session()->flash('success', 'Categoría eliminada correctamente.');
    }

    public function toggleStatus(int $id): void
    {
        $category = Category::findOrFail($id);
        $category->update(['status' => !$category->status]);
    }

    public function render()
    {
        $query = Category::with(['parent', 'children'])
            ->orderBy('orden')
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

        $categories = $query->paginate(15);

        $stats = [
            'total' => Category::count(),
            'active' => Category::where('status', true)->count(),
            'inactive' => Category::where('status', false)->count(),
        ];

        // Only root categories for parent selector
        $parentCategories = Category::whereNull('parent_id')
            ->where('status', true)
            ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
            ->orderBy('nombre')
            ->get();

        return view('livewire.admin.catalogo.categorias.index', [
            'categories' => $categories,
            'stats' => $stats,
            'parentCategories' => $parentCategories,
        ]);
    }
}
