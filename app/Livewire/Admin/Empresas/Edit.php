<?php

namespace App\Livewire\Admin\Empresas;

use App\Models\Empresa;
use App\Models\Pais;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
#[Title('Editar Empresa')]
class Edit extends Component
{
    use WithFileUploads;

    public Empresa $empresa;
    public ?int $pais_id = null;
    public string $razon_social = '';
    public string $documento = '';
    public $logo = null;
    public ?string $existingLogo = null;
    public string $direccion = '';
    public ?float $latitud = null;
    public ?float $longitud = null;
    public string $representante_legal = '';
    public string $telefono = '';
    public string $email = '';
    public bool $status = true;

    public function mount(int $id): void
    {
        $this->empresa = Empresa::findOrFail($id);
        $this->pais_id = $this->empresa->pais_id;
        $this->razon_social = $this->empresa->razon_social;
        $this->documento = $this->empresa->documento;
        $this->existingLogo = $this->empresa->logo;
        $this->direccion = $this->empresa->direccion ?? '';
        $this->latitud = $this->empresa->latitud ? (float) $this->empresa->latitud : null;
        $this->longitud = $this->empresa->longitud ? (float) $this->empresa->longitud : null;
        $this->representante_legal = $this->empresa->representante_legal ?? '';
        $this->telefono = $this->empresa->telefono ?? '';
        $this->email = $this->empresa->email ?? '';
        $this->status = $this->empresa->status;
    }

    public function updatedPaisId(): void
    {
        if ($this->pais_id) {
            $pais = Pais::find($this->pais_id);
            if ($pais && $pais->latitud && $pais->longitud) {
                $this->dispatch('pais-selected', lat: (float) $pais->latitud, lng: (float) $pais->longitud);
            }
        }
    }

    protected function rules(): array
    {
        return [
            'pais_id' => 'nullable|exists:pais,id',
            'razon_social' => 'required|string|max:255',
            'documento' => 'required|string|max:50|unique:empresas,documento,' . $this->empresa->id,
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,ico|max:2048',
            'direccion' => 'nullable|string|max:500',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'representante_legal' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'boolean',
        ];
    }

    public function removeLogo(): void
    {
        if ($this->empresa->logo) {
            Storage::disk('public')->delete($this->empresa->logo);
            $this->empresa->update(['logo' => null]);
            $this->existingLogo = null;
        }
    }

    public function save(): void
    {
        $this->validate();

        $logoPath = $this->empresa->logo;
        if ($this->logo) {
            // Delete old logo if exists
            if ($this->empresa->logo) {
                Storage::disk('public')->delete($this->empresa->logo);
            }
            $logoPath = $this->logo->store('empresas/logos', 'public');
        }

        $this->empresa->update([
            'pais_id' => $this->pais_id ?: null,
            'razon_social' => $this->razon_social,
            'documento' => $this->documento,
            'logo' => $logoPath,
            'direccion' => $this->direccion ?: null,
            'latitud' => $this->latitud,
            'longitud' => $this->longitud,
            'representante_legal' => $this->representante_legal ?: null,
            'telefono' => $this->telefono ?: null,
            'email' => $this->email ?: null,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Empresa actualizada correctamente.');

        $this->redirect(route('admin.empresas'), navigate: true);
    }

    public function render()
    {
        $paises = Pais::where('activo', true)->orderBy('nombre')->get();

        return view('livewire.admin.empresas.edit', [
            'paises' => $paises,
        ]);
    }
}
