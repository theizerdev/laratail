<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\RegionalConfigurationService;
use App\Models\Empresa;

class RegionalConfigurationIndicator extends Component
{
    public array $config = [];
    public $empresas = [];
    public $selectedEmpresaId = null;

    public function mount(): void
    {
        $this->loadConfig();
        $this->empresas = Empresa::with('pais')->where('status', true)->get();
        $this->selectedEmpresaId = session('current_empresa_id') ?? ($this->empresas->first()->id ?? null);
    }

    public function loadConfig(): void
    {
        $this->config = RegionalConfigurationService::getCurrentConfiguration();
    }

    public function changeEmpresa(int $empresaId): void
    {
        $empresa = Empresa::with('pais')->find($empresaId);
        if ($empresa) {
            RegionalConfigurationService::setRegionalConfiguration($empresa);
            $this->selectedEmpresaId = $empresa->id;
            $this->loadConfig();
            $this->dispatch('regional-config-updated');
            
            // Set currency dynamically in session
            session(['currency' => strtolower($empresa->pais->moneda_principal)]);
            
            $this->redirect(request()->header('Referer') ?? '/', navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.regional-configuration-indicator');
    }
}
