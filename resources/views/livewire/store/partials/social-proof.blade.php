<?php
use Livewire\Volt\Component;
use App\Models\Order;
use App\Models\Customer;

new class extends Component {
    public ?array $currentPurchase = null;
    public bool $dismissed = false;
    public int $rotationIndex = 0;

    public function mount(): void
    {
        $this->loadNextPurchase();
    }

    public function loadNextPurchase(): void
    {
        $recentOrders = Order::with(['items.product', 'customer'])
            ->where('tipo', 'venta')
            ->where('estado', '!=', 'cancelado')
            ->whereNotNull('customer_id')
            ->latest()
            ->limit(20)
            ->get();

        if ($recentOrders->isEmpty()) {
            $this->currentPurchase = null;
            return;
        }

        $order = $recentOrders->get($this->rotationIndex % $recentOrders->count());
        if (!$order || !$order->customer || $order->items->isEmpty()) {
            $this->currentPurchase = null;
            return;
        }

        $firstItem = $order->items->first();
        $this->currentPurchase = [
            'customer_name' => $order->customer->nombre,
            'city' => $order->ciudad_envio ?? $order->customer->ciudad,
            'product_name' => optional($firstItem?->product)->nombre ?? 'un producto',
            'product_slug' => optional($firstItem?->product)->slug,
            'product_image' => optional($firstItem?->product)->imagen_principal_url ?? 'https://placehold.co/40',
            'time_ago' => $order->created_at->diffForHumans(),
            'items_count' => $order->items->count(),
        ];
        $this->rotationIndex++;
        $this->dismissed = false;
    }

    public function dismiss(): void
    {
        $this->dismissed = true;
    }
};
?>

<div>
    @if($currentPurchase && !$dismissed)
    <div x-data="{ show: false }"
         x-init="setTimeout(() => show = true, 5000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-6 left-6 z-[90] max-w-xs w-full"
         x-effect="if (show) setTimeout(() => { show = false; $wire.loadNextPurchase(); }, 8000)">

        @if($currentPurchase['product_slug'])
        <a href="/producto/{{ $currentPurchase['product_slug'] }}" wire:navigate class="block bg-white rounded-xl shadow-xl border border-zinc-100 p-3 hover:shadow-2xl transition-shadow">
        @else
        <div class="bg-white rounded-xl shadow-xl border border-zinc-100 p-3">
        @endif
            <div class="flex items-start gap-3">
                <img src="{{ $currentPurchase['product_image'] }}" class="w-11 h-11 rounded-lg object-cover bg-zinc-100 flex-shrink-0" alt="">
                <div class="flex-1 min-w-0 pr-4">
                    <p class="text-xs text-zinc-500 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                        Compra reciente
                    </p>
                    <p class="text-sm font-medium text-zinc-900 truncate mt-0.5">{{ $currentPurchase['customer_name'] }}{{ $currentPurchase['city'] ? ' de '.$currentPurchase['city'] : '' }}</p>
                    <p class="text-xs text-zinc-500 truncate">compró {{ $currentPurchase['product_name'] }}</p>
                    <p class="text-xs text-zinc-400 mt-0.5">{{ $currentPurchase['time_ago'] }}</p>
                </div>
                <button wire:click.prevent="dismiss" class="absolute top-2 right-2 text-zinc-300 hover:text-zinc-500 transition-colors p-0.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @if($currentPurchase['product_slug'])
        </a>
        @else
        </div>
        @endif
    </div>
    @endif
</div>
