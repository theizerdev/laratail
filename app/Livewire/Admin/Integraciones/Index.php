<?php

namespace App\Livewire\Admin\Integraciones;

use App\Models\Empresa;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Integraciones')]
class Index extends Component
{
    public function render()
    {
        // Get empresas with WhatsApp configured
        $empresasConWhatsapp = Empresa::whereNotNull('whatsapp_api_key')
            ->where('whatsapp_active', true)
            ->count();

        return view('livewire.admin.integraciones.index', [
            'empresasConWhatsapp' => $empresasConWhatsapp,
        ]);
    }
}
