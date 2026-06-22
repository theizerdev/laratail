<?php
use Livewire\Volt\Component;
use App\Models\ShippingZone;

new class extends Component {
    public string $zipCode = '';
    public ?array $estimate = null;
    public ?string $error = null;
    public float $totalWeight = 0;
    public float $subtotal = 0;

    public function mount(float $totalWeight = 0, float $subtotal = 0): void
    {
        $this->totalWeight = $totalWeight;
        $this->subtotal = $subtotal;
    }

    public function calculateShipping(): void
    {
        $this->error = null;
        $this->estimate = null;

        if (empty(trim($this->zipCode))) {
            $this->error = 'Ingresa un código postal.';
            return;
        }

        $result = ShippingZone::estimate($this->zipCode, $this->totalWeight);

        if (!$result) {
            $this->error = 'No encontramos cobertura para ese código postal.';
            return;
        }

        // Free shipping if subtotal exceeds threshold
        if ($this->subtotal >= ($result['gratis_desde'] ?? 999999)) {
            $result['costo'] = 0;
            $result['gratis'] = true;
        }

        $this->estimate = $result;
        session(['shipping_estimate' => $result, 'shipping_zip' => $this->zipCode]);
    }
};
?>

<div class="mt-6 border-t border-zinc-100 pt-5">
    <h3 class="text-sm font-semibold text-zinc-700 mb-3 flex items-center gap-2">
        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        Estimar Envío
    </h3>

    <div class="flex gap-2">
        <input
            type="text"
            wire:model="zipCode"
            wire:keydown.enter="calculateShipping"
            placeholder="Código postal"
            maxlength="10"
            class="flex-1 rounded-lg border-zinc-300 text-sm focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2"
        >
        <button
            wire:click="calculateShipping"
            wire:loading.attr="disabled"
            class="px-4 py-2 bg-zinc-900 text-white rounded-lg text-sm font-medium hover:bg-black transition-colors disabled:opacity-50"
        >
            <span wire:loading.remove>Calcular</span>
            <span wire:loading>...</span>
        </button>
    </div>

    @if($error)
        <p class="mt-2 text-xs text-red-600">{{ $error }}</p>
    @endif

    @if($estimate)
        <div class="mt-3 bg-indigo-50 border border-indigo-100 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-zinc-900">{{ $estimate['zona'] }}</p>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Entrega estimada: {{ $estimate['dias_min'] }}-{{ $estimate['dias_max'] }} días hábiles
                    </p>
                </div>
                <div class="text-right">
                    @if($estimate['costo'] == 0)
                        <span class="text-sm font-bold text-green-600">¡GRATIS!</span>
                        <p class="text-xs text-green-500">Envío gratis +${{ $estimate['gratis_desde'] ?? 100 }}</p>
                    @else
                        <span class="text-lg font-bold text-indigo-600">${{ number_format($estimate['costo'], 2) }}</span>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
