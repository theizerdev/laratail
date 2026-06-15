<?php

namespace App\Livewire\Admin\Catalogo\Atributos;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Atributos')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    // Attribute Modal
    public bool $showAttrModal = false;
    public ?int $editingAttrId = null;
    public string $attrNombre = '';
    public string $attrTipo = 'select';
    public bool $attrUsadoVariantes = true;
    public bool $attrStatus = true;

    // Values Modal
    public bool $showValuesModal = false;
    public ?int $selectedAttributeId = null;
    public string $selectedAttributeName = '';
    public string $newValue = '';
    public string $newColorCode = '';

    // Delete Modal
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ─── Attribute CRUD ────────────────────────────────────

    public function openCreateAttr(): void
    {
        $this->resetAttrForm();
        $this->editingAttrId = null;
        $this->showAttrModal = true;
    }

    public function openEditAttr(int $id): void
    {
        $attr = Attribute::findOrFail($id);
        $this->editingAttrId = $attr->id;
        $this->attrNombre = $attr->nombre;
        $this->attrTipo = $attr->tipo;
        $this->attrUsadoVariantes = $attr->usado_para_variantes;
        $this->attrStatus = $attr->status;
        $this->showAttrModal = true;
    }

    public function closeAttrModal(): void
    {
        $this->showAttrModal = false;
        $this->resetAttrForm();
    }

    protected function resetAttrForm(): void
    {
        $this->attrNombre = '';
        $this->attrTipo = 'select';
        $this->attrUsadoVariantes = true;
        $this->attrStatus = true;
        $this->editingAttrId = null;
    }

    public function saveAttr(): void
    {
        $this->validate([
            'attrNombre' => 'required|string|max:100',
            'attrTipo' => 'required|in:select,text,color',
            'attrUsadoVariantes' => 'boolean',
            'attrStatus' => 'boolean',
        ]);

        $data = [
            'nombre' => $this->attrNombre,
            'tipo' => $this->attrTipo,
            'usado_para_variantes' => $this->attrUsadoVariantes,
            'status' => $this->attrStatus,
        ];

        if ($this->editingAttrId) {
            Attribute::findOrFail($this->editingAttrId)->update($data);
            session()->flash('success', 'Atributo actualizado correctamente.');
        } else {
            Attribute::create($data);
            session()->flash('success', 'Atributo creado correctamente.');
        }

        $this->closeAttrModal();
    }

    public function confirmDeleteAttr(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteAttr(): void
    {
        $attr = Attribute::findOrFail($this->deletingId);
        $attr->delete(); // cascades to values

        $this->showDeleteModal = false;
        $this->deletingId = null;
        session()->flash('success', 'Atributo eliminado correctamente.');
    }

    public function toggleAttrStatus(int $id): void
    {
        $attr = Attribute::findOrFail($id);
        $attr->update(['status' => !$attr->status]);
    }

    // ─── Values Management ─────────────────────────────────

    public function openValues(int $id): void
    {
        $attr = Attribute::findOrFail($id);
        $this->selectedAttributeId = $attr->id;
        $this->selectedAttributeName = $attr->nombre;
        $this->newValue = '';
        $this->newColorCode = '';
        $this->showValuesModal = true;
    }

    public function closeValuesModal(): void
    {
        $this->showValuesModal = false;
        $this->selectedAttributeId = null;
    }

    public function addValue(): void
    {
        $this->validate([
            'newValue' => 'required|string|max:100',
            'newColorCode' => 'nullable|string|max:7',
        ]);

        AttributeValue::create([
            'attribute_id' => $this->selectedAttributeId,
            'valor' => $this->newValue,
            'codigo_color' => $this->newColorCode ?: null,
        ]);

        $this->newValue = '';
        $this->newColorCode = '';
        $this->dispatch('value-added');
    }

    public function deleteValue(int $id): void
    {
        AttributeValue::findOrFail($id)->delete();
    }

    public function render()
    {
        $query = Attribute::with(['values' => fn($q) => $q->orderBy('orden')])
            ->withCount('values')
            ->orderBy('nombre');

        if ($this->search) {
            $query->where('nombre', 'like', "%{$this->search}%");
        }

        $attributes = $query->paginate(15);

        // Values for the selected attribute modal
        $selectedValues = collect();
        if ($this->selectedAttributeId) {
            $selectedValues = AttributeValue::where('attribute_id', $this->selectedAttributeId)
                ->orderBy('orden')
                ->get();
        }

        return view('livewire.admin.catalogo.atributos.index', [
            'attributes' => $attributes,
            'selectedValues' => $selectedValues,
        ]);
    }
}
