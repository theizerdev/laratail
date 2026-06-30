<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\RegionalConfigurationService;
use App\Models\Empresa;

class TestRegionalConfiguration extends Component
{
    public array $config = [];
    public float $amount = 1234.56;
    public string $dateStr = '';

    public function mount(): void
    {
        $this->dateStr = now()->toDateTimeString();
        $this->config = RegionalConfigurationService::getCurrentConfiguration();
    }

    public function changeEmpresa(int $empresaId): void
    {
        $empresa = Empresa::with('pais')->find($empresaId);
        if ($empresa) {
            RegionalConfigurationService::setRegionalConfiguration($empresa);
            $this->config = RegionalConfigurationService::getCurrentConfiguration();
            
            // Set currency dynamically in session
            session(['currency' => strtolower($empresa->pais->moneda_principal)]);
        }
    }

    public function render()
    {
        $empresas = Empresa::with('pais')->get();
        return view('livewire.test-regional-configuration', [
            'empresas' => $empresas
        ]);
    }
}
