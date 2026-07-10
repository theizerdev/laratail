<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use App\Models\Order;

new #[Layout('layouts.app', ['meta_robots' => 'noindex, nofollow'])] #[Title('Rastrear Pedido - Abastos Los Trinis')] class extends Component {
    public string $orderNumber = '';
    public string $orderEmail = '';
    public ?Order $trackedOrder = null;
    public ?string $trackingError = null;

    public function trackOrder(): void
    {
        $this->trackingError = null;
        $this->trackedOrder = null;

        if (empty(trim($this->orderNumber))) {
            $this->trackingError = 'Ingresa el número de tu pedido.';
            return;
        }

        $order = Order::with(['items.product', 'customer', 'paisEnvio'])
            ->where('numero', $this->orderNumber)
            ->where('tipo', 'venta')
            ->first();

        if (!$order) {
            $this->trackingError = 'No se encontró un pedido con ese número.';
            return;
        }

        if (!empty(trim($this->orderEmail)) && $order->customer) {
            if (strtolower($order->customer->email) !== strtolower(trim($this->orderEmail))) {
                $this->trackingError = 'El email no coincide con el del pedido.';
                return;
            }
        }

        $this->trackedOrder = $order;
    }
};
?>

<div>
    <div class="bg-white border-b border-zinc-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center gap-2 text-sm">
                <a href="/" wire:navigate class="text-zinc-500 hover:text-zinc-900 transition-colors">Inicio</a>
                <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-zinc-900 font-medium">Rastrear Pedido</span>
            </nav>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
            <h1 class="text-2xl font-extrabold text-zinc-900">Rastrear tu Pedido</h1>
            <p class="text-sm text-zinc-500 mt-2">Ingresa el número de pedido para ver su estado actual</p>
        </div>

        {{-- Search form --}}
        <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 mb-8">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" wire:model="orderNumber" wire:keydown.enter="trackOrder" placeholder="Ej: ORD-20260622-ABCD" class="w-full rounded-xl border-zinc-300 text-sm focus:ring-indigo-500 focus:border-indigo-500 px-4 py-3" />
                </div>
                <div class="flex-1">
                    <input type="email" wire:model="orderEmail" wire:keydown.enter="trackOrder" placeholder="Email del pedido (opcional)" class="w-full rounded-xl border-zinc-300 text-sm focus:ring-indigo-500 focus:border-indigo-500 px-4 py-3" />
                </div>
                <button wire:click="trackOrder" wire:loading.attr="disabled" class="bg-indigo-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-indigo-700 transition-colors text-sm whitespace-nowrap flex items-center justify-center gap-2">
                    <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Rastrear
                </button>
            </div>
            @if($trackingError)
                <div class="mt-3 bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-2.5 rounded-xl">{{ $trackingError }}</div>
            @endif
        </div>

        {{-- Order result --}}
        @if($trackedOrder)
        <div class="space-y-6">
            {{-- Order header --}}
            <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
                    <div>
                        <h2 class="text-lg font-bold text-zinc-900">{{ $trackedOrder->numero }}</h2>
                        <p class="text-sm text-zinc-500">Realizado el {{ $trackedOrder->created_at->format('d/m/Y \a \l\a\s H:i') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            {{ match($trackedOrder->estado) {
                                'pendiente' => 'bg-yellow-100 text-yellow-800',
                                'confirmado' => 'bg-blue-100 text-blue-800',
                                'procesando' => 'bg-indigo-100 text-indigo-800',
                                'enviado' => 'bg-purple-100 text-purple-800',
                                'entregado' => 'bg-green-100 text-green-800',
                                'cancelado' => 'bg-red-100 text-red-800',
                                default => 'bg-zinc-100 text-zinc-800',
                            } }}">
                            {{ $trackedOrder->estado_label }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Visual Timeline --}}
            <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6">
                <h3 class="text-sm font-bold text-zinc-700 uppercase tracking-wide mb-6">Progreso del Pedido</h3>

                @if($trackedOrder->estado === 'cancelado')
                    <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <svg class="w-6 h-6 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="text-sm font-semibold text-red-800">Pedido Cancelado</p>
                            <p class="text-xs text-red-600 mt-0.5">{{ $trackedOrder->fecha_cancelacion ? 'Cancelado el ' . $trackedOrder->fecha_cancelacion->format('d/m/Y H:i') : '' }}</p>
                        </div>
                    </div>
                @else
                    @php
                        $states = [
                            'pendiente'  => ['label' => 'Recibido', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'date' => $trackedOrder->created_at],
                            'confirmado' => ['label' => 'Confirmado', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'date' => $trackedOrder->fecha_confirmacion],
                            'procesando' => ['label' => 'En Preparación', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'date' => null],
                            'enviado'    => ['label' => 'Enviado', 'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h8a1 1 0 001-1zm0 0h4l3-3-3-3h-4m-7 6h.01M9 11h.01', 'date' => $trackedOrder->fecha_envio],
                            'entregado'  => ['label' => 'Entregado', 'icon' => 'M5 13l4 4L19 7', 'date' => $trackedOrder->fecha_entrega],
                        ];
                        $currentIndex = array_search($trackedOrder->estado, array_keys($states));
                        if ($currentIndex === false) $currentIndex = 0;
                    @endphp

                    <div class="space-y-0">
                        @foreach($states as $sIdx => $stateKey => $state)
                            @php $isComplete = $sIdx <= $currentIndex; $isCurrent = $sIdx === $currentIndex; @endphp
                            <div class="flex gap-4">
                                {{-- Vertical line + icon --}}
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 border-2 transition-all
                                        {{ $isComplete ? 'bg-emerald-500 border-emerald-500 text-white' : ($isCurrent ? 'bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-zinc-50 border-zinc-200 text-zinc-400') }}">
                                        @if($isComplete)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $state['icon'] }}"/></svg>
                                        @endif
                                    </div>
                                    @if($sIdx < count($states) - 1)
                                        <div class="w-0.5 flex-1 min-h-[2rem] {{ $isComplete ? 'bg-emerald-500' : 'bg-zinc-200' }}"></div>
                                    @endif
                                </div>
                                {{-- Label --}}
                                <div class="pb-8">
                                    <p class="text-sm font-semibold {{ $isComplete ? 'text-zinc-900' : 'text-zinc-400' }}">{{ $state['label'] }}</p>
                                    @if($state['date'])
                                        <p class="text-xs text-zinc-500 mt-0.5">{{ $state['date']->format('d/m/Y H:i') }}</p>
                                    @elseif($isCurrent)
                                        <p class="text-xs text-indigo-600 font-medium mt-0.5">Estado actual</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Tracking number --}}
                    @if($trackedOrder->numero_seguimiento)
                    <div class="mt-4 p-4 bg-indigo-50 border border-indigo-200 rounded-xl">
                        <p class="text-sm text-indigo-800">
                            <span class="font-semibold">Número de seguimiento:</span> {{ $trackedOrder->numero_seguimiento }}
                        </p>
                    </div>
                    @endif
                @endif
            </div>

            {{-- Order items summary --}}
            <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6">
                <h3 class="text-sm font-bold text-zinc-700 uppercase tracking-wide mb-4">Artículos del Pedido</h3>
                <div class="divide-y divide-zinc-100">
                    @foreach($trackedOrder->items as $item)
                    <div class="flex items-center gap-3 py-3">
                        <img src="{{ optional($item->product)->imagen_principal_url ?? 'https://placehold.co/50' }}" class="w-12 h-12 rounded-lg object-cover bg-zinc-100 flex-shrink-0" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-zinc-900 truncate">{{ optional($item->product)->nombre ?? 'Producto' }}</p>
                            <p class="text-xs text-zinc-500">{{ $item->cantidad }} × {{ format_order_item_price($item) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @php
                    $vesTotal = 0.0;
                    foreach ($trackedOrder->items as $item) {
                        $vesTotal += $item->cantidad * (float)format_order_item_price($item, true);
                    }
                    $latestRate = \App\Models\ExchangeRate::getLatestRate('USD') ?? 1.0;
                    $vesDescuento = $trackedOrder->descuento * $latestRate;
                    $vesEnvio = $trackedOrder->envio * $latestRate;
                    $vesTotal = max(0.0, $vesTotal - $vesDescuento + $vesEnvio);
                @endphp
                <div class="mt-4 pt-4 border-t border-zinc-100 flex justify-between text-lg font-bold text-zinc-900">
                    <span>Total</span>
                    <span>{{ format_display_price($trackedOrder->total, $vesTotal) }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
