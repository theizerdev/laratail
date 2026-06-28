<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Order;

new #[Layout('layouts.app')] class extends Component {
    public Order $order;

    public function mount(Order $order)
    {
        $this->order = $order->load(['items.product', 'paisEnvio']);
    }
};
?>

<div>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
        <!-- Success Header -->
        <div class="text-center mb-10">
            <div class="w-20 h-20 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h1 class="text-3xl font-bold text-zinc-900 mb-2">¡Pedido Confirmado!</h1>
            <p class="text-zinc-500">Tu pedido ha sido recibido y está siendo procesado.</p>
        </div>

        <!-- Order Info Card -->
        <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 sm:p-8 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-6 border-b border-zinc-100">
                <div>
                    <p class="text-xs text-zinc-500 uppercase font-medium">Número de pedido</p>
                    <p class="text-lg font-bold text-zinc-900 mt-0.5">{{ $order->numero }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-zinc-500 uppercase font-medium">Fecha</p>
                    <p class="text-sm text-zinc-700 mt-0.5">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <!-- Status -->
            <div class="flex items-center gap-3 mb-6">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    {{ $order->estado_label }}
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                    {{ $order->estado_pago === 'pagado' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                    Pago: {{ ucfirst($order->estado_pago) }}
                </span>
            </div>

            <!-- Items -->
            <h3 class="text-sm font-bold text-zinc-700 uppercase tracking-wide mb-4">Artículos</h3>
            <div class="divide-y divide-zinc-100 mb-6">
                @foreach($order->items as $item)
                <div class="flex items-center gap-4 py-3">
                    <img src="{{ optional($item->product)->imagen_principal_url ?? 'https://placehold.co/60' }}" class="w-14 h-14 rounded-lg object-cover bg-zinc-100" alt="">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-zinc-900 truncate">{{ optional($item->product)->nombre ?? 'Producto' }}</p>
                        <p class="text-xs text-zinc-500">{{ $item->cantidad }} × ${{ number_format($item->precio, 2) }}</p>
                    </div>
                    <p class="text-sm font-bold text-zinc-900">${{ number_format($item->subtotal, 2) }}</p>
                </div>
                @endforeach
            </div>

            <!-- Totals -->
            <div class="bg-zinc-50 rounded-xl p-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-zinc-500">Subtotal</span>
                    <span class="text-zinc-900">${{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->descuento > 0)
                <div class="flex justify-between text-green-600">
                    <span>Descuento</span>
                    <span>-${{ number_format($order->descuento, 2) }}</span>
                </div>
                @endif
                @if($order->envio > 0)
                <div class="flex justify-between">
                    <span class="text-zinc-500">Envío</span>
                    <span class="text-zinc-900">${{ number_format($order->envio, 2) }}</span>
                </div>
                @endif
                <hr class="border-zinc-200">
                <div class="flex justify-between text-lg font-bold text-zinc-900 pt-1">
                    <span>Total</span>
                    <span>${{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Shipping Info -->
        <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 sm:p-8 mb-8">
            <h3 class="text-sm font-bold text-zinc-700 uppercase tracking-wide mb-4">Dirección de Envío</h3>
            <div class="text-sm text-zinc-600 space-y-1">
                <p class="font-medium text-zinc-900">{{ $order->direccion_envio }}</p>
                <p>{{ $order->ciudad_envio }}{{ $order->estado_envio ? ', '.$order->estado_envio : '' }} {{ $order->codigo_postal_envio }}</p>
                @if($order->paisEnvio)
                    <p>{{ $order->paisEnvio->nombre }}</p>
                @endif
            </div>
            <div class="mt-4 pt-4 border-t border-zinc-100">
                <p class="text-sm text-zinc-600">
                    <span class="font-medium">Método de pago:</span>
                    {{ $order->metodo_pago === 'contra_entrega' ? 'Pago contra entrega' : 'Transferencia bancaria' }}
                </p>
            </div>
        </div>

        <!-- CTAs -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            @auth
                <a href="/mi-cuenta/pedidos" wire:navigate class="inline-flex items-center justify-center px-8 py-4 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">
                    Ver Mis Pedidos
                </a>
            @endif
            <a href="/catalogo" wire:navigate class="inline-flex items-center justify-center px-8 py-4 border border-zinc-300 text-zinc-700 rounded-xl font-semibold hover:bg-zinc-50 transition-colors">
                Seguir Comprando
            </a>
        </div>
    </div>
</div>
