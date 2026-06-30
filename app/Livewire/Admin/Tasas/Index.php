<?php

namespace App\Livewire\Admin\Tasas;

use App\Models\ExchangeRate;
use App\Services\ExchangeRateService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Tasas de Cambio')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    
    // Form fields
    public ?int $editingId = null;
    public string $date = '';
    public float $usd_rate = 0.0;
    public ?float $eur_rate = null;
    public string $source = 'manual';

    public function mount(): void
    {
        $this->date = today()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'date' => 'required|date',
            'usd_rate' => 'required|numeric|min:0.0001',
            'eur_rate' => 'nullable|numeric|min:0.0001',
            'source' => 'required|string|max:50',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->dispatch('modal-show', name: 'tasa-form');
    }

    public function openEditModal(int $id): void
    {
        $rate = ExchangeRate::findOrFail($id);

        $this->editingId = $rate->id;
        $this->date = $rate->date->format('Y-m-d');
        $this->usd_rate = (float) $rate->usd_rate;
        $this->eur_rate = $rate->eur_rate ? (float) $rate->eur_rate : null;
        $this->source = $rate->source;

        $this->dispatch('modal-show', name: 'tasa-form');
    }

    public function save(): void
    {
        $this->validate();

        ExchangeRate::updateOrCreate(
            ['id' => $this->editingId],
            [
                'date' => $this->date,
                'usd_rate' => $this->usd_rate,
                'eur_rate' => $this->eur_rate,
                'source' => $this->source,
                'fetch_time' => now()->format('H:i:s'),
                'raw_data' => [
                    'manual' => true,
                    'created_by' => auth()->user()->name,
                    'updated_at' => now()->toISOString()
                ]
            ]
        );

        $this->dispatch('modal-close', name: 'tasa-form');
        $this->resetForm();

        session()->flash('success', $this->editingId
            ? 'Tasa de cambio actualizada correctamente.'
            : 'Tasa de cambio creada correctamente.'
        );
    }

    public function fetchBCVRates(): void
    {
        $service = new ExchangeRateService();
        $success = $service->fetchAndStoreRates();

        if ($success) {
            $latest = ExchangeRate::getTodayRate();
            session()->flash('success', "Tasas sincronizadas exitosamente desde el BCV. USD: {$latest->usd_rate} VES.");
        } else {
            session()->flash('error', 'Error al consultar las tasas de cambio de la API del BCV. Verifique logs.');
        }
    }

    public function delete(int $id): void
    {
        $rate = ExchangeRate::findOrFail($id);
        $rate->delete();

        session()->flash('success', 'Tasa de cambio eliminada correctamente.');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->date = today()->format('Y-m-d');
        $this->usd_rate = 0.0;
        $this->eur_rate = null;
        $this->source = 'manual';
        $this->resetValidation();
    }

    public function render()
    {
        $rates = ExchangeRate::query()
            ->when($this->search, fn($q) => $q->where('source', 'like', "%{$this->search}%")
                ->orWhere('date', 'like', "%{$this->search}%"))
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $latestRate = ExchangeRate::latest('date')->latest('id')->first();

        return view('livewire.admin.tasas.index', [
            'rates' => $rates,
            'latestRate' => $latestRate,
        ]);
    }
}
