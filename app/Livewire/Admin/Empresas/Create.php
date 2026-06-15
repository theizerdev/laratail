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
#[Title('Crear Empresa')]
class Create extends Component
{
    use WithFileUploads;

    public ?int $pais_id = null;
    public string $razon_social = '';
    public string $documento = '';
    public $logo = null;
    public string $direccion = '';
    public ?float $latitud = null;
    public ?float $longitud = null;
    public string $representante_legal = '';
    public string $telefono = '';
    public string $email = '';
    public bool $status = true;

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
            'documento' => 'required|string|max:50|unique:empresas,documento',
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

    public function save(): void
    {
        $this->validate();

        $logoPath = null;
        if ($this->logo) {
            $logoPath = $this->logo->store('empresas/logos', 'public');
        }

        Empresa::create([
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

        session()->flash('success', 'Empresa creada correctamente.');

        $this->redirect(route('admin.empresas'), navigate: true);
    }

    public function render()
    {
        $paises = Pais::where('activo', true)->orderBy('nombre')->get();

        return view('livewire.admin.empresas.create', [
            'paises' => $paises,
        ]);
    }
}
