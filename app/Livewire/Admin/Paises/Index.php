<?php

namespace App\Livewire\Admin\Paises;

use App\Models\Pais;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Países')]
class Index extends Component
{
    public string $search = '';
    public string $filter = 'all'; // all, active, inactive

    // Form fields
    public ?int $editingId = null;
    public string $nombre = '';
    public string $codigo_iso2 = '';
    public string $codigo_iso3 = '';
    public string $codigo_telefonico = '';
    public string $moneda_principal = '';
    public string $idioma_principal = '';
    public string $continente = '';
    public string $zona_horaria = '';
    public string $formato_fecha = 'dd/mm/yyyy';
    public string $formato_moneda = '1.234,56';
    public float $impuesto_predeterminado = 0.00;
    public string $separador_miles = '.';
    public string $separador_decimales = ',';
    public int $decimales_moneda = 2;
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'codigo_iso2' => 'required|string|max:2|unique:pais,codigo_iso2,' . ($this->editingId ?? 'null'),
            'codigo_iso3' => 'required|string|max:3|unique:pais,codigo_iso3,' . ($this->editingId ?? 'null'),
            'codigo_telefonico' => 'nullable|string|max:10',
            'moneda_principal' => 'nullable|string|max:10',
            'idioma_principal' => 'nullable|string|max:10',
            'continente' => 'nullable|string|max:50',
            'zona_horaria' => 'nullable|string|max:50',
            'formato_fecha' => 'nullable|string|max:20',
            'formato_moneda' => 'nullable|string|max:20',
            'impuesto_predeterminado' => 'nullable|numeric|min:0|max:100',
            'separador_miles' => 'nullable|string|max:1',
            'separador_decimales' => 'nullable|string|max:1',
            'decimales_moneda' => 'nullable|integer|min:0|max:4',
            'is_active' => 'boolean',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->dispatch('modal-show', name: 'pais-form');
    }

    public function openEditModal(int $id): void
    {
        $pais = Pais::findOrFail($id);

        $this->editingId = $pais->id;
        $this->nombre = $pais->nombre;
        $this->codigo_iso2 = $pais->codigo_iso2;
        $this->codigo_iso3 = $pais->codigo_iso3;
        $this->codigo_telefonico = $pais->codigo_telefonico ?? '';
        $this->moneda_principal = $pais->moneda_principal ?? '';
        $this->idioma_principal = $pais->idioma_principal ?? '';
        $this->continente = $pais->continente ?? '';
        $this->zona_horaria = $pais->zona_horaria ?? '';
        $this->formato_fecha = $pais->formato_fecha ?? 'dd/mm/yyyy';
        $this->formato_moneda = $pais->formato_moneda ?? '1.234,56';
        $this->impuesto_predeterminado = $pais->impuesto_predeterminado ?? 0.00;
        $this->separador_miles = $pais->separador_miles ?? '.';
        $this->separador_decimales = $pais->separador_decimales ?? ',';
        $this->decimales_moneda = $pais->decimales_moneda ?? 2;
        $this->is_active = $pais->activo;

        $this->dispatch('modal-show', name: 'pais-form');
    }

    public function save(): void
    {
        $this->validate();

        Pais::updateOrCreate(
            ['id' => $this->editingId],
            [
                'nombre' => $this->nombre,
                'codigo_iso2' => strtoupper($this->codigo_iso2),
                'codigo_iso3' => strtoupper($this->codigo_iso3),
                'codigo_telefonico' => $this->codigo_telefonico ?: null,
                'moneda_principal' => $this->moneda_principal ?: null,
                'idioma_principal' => $this->idioma_principal ?: null,
                'continente' => $this->continente ?: null,
                'zona_horaria' => $this->zona_horaria ?: null,
                'formato_fecha' => $this->formato_fecha,
                'formato_moneda' => $this->formato_moneda,
                'impuesto_predeterminado' => $this->impuesto_predeterminado,
                'separador_miles' => $this->separador_miles,
                'separador_decimales' => $this->separador_decimales,
                'decimales_moneda' => $this->decimales_moneda,
                'activo' => $this->is_active,
            ]
        );

        $this->dispatch('modal-close', name: 'pais-form');
        $this->resetForm();

        session()->flash('success', $this->editingId
            ? 'País actualizado correctamente.'
            : 'País creado correctamente.'
        );
    }

    public function delete(int $id): void
    {
        $pais = Pais::findOrFail($id);
        $pais->delete();

        session()->flash('success', 'País eliminado correctamente.');
    }

    public function toggleStatus(int $id): void
    {
        $pais = Pais::findOrFail($id);
        $pais->update(['activo' => ! $pais->activo]);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->nombre = '';
        $this->codigo_iso2 = '';
        $this->codigo_iso3 = '';
        $this->codigo_telefonico = '';
        $this->moneda_principal = '';
        $this->idioma_principal = '';
        $this->continente = '';
        $this->zona_horaria = '';
        $this->formato_fecha = 'dd/mm/yyyy';
        $this->formato_moneda = '1.234,56';
        $this->impuesto_predeterminado = 0.00;
        $this->separador_miles = '.';
        $this->separador_decimales = ',';
        $this->decimales_moneda = 2;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $paises = Pais::query()
            ->when($this->search, fn ($q) => $q->where('nombre', 'like', "%{$this->search}%")
                ->orWhere('codigo_iso2', 'like', "%{$this->search}%"))
            ->when($this->filter === 'active', fn ($q) => $q->where('activo', true))
            ->when($this->filter === 'inactive', fn ($q) => $q->where('activo', false))
            ->orderBy('nombre')
            ->get();

        $totalPaises = Pais::count();
        $paisesActivos = Pais::where('activo', true)->count();

        $stats = [
            'total_paises' => $totalPaises,
            'paises_activos' => $paisesActivos,
            'paises_inactivos' => $totalPaises - $paisesActivos,
        ];

        return view('livewire.admin.paises.index', [
            'paises' => $paises,
            'stats' => $stats,
        ]);
    }
}