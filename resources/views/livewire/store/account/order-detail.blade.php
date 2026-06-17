<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    public Order $order;

    public function mount(Order $order)
    {
        // Verify the order belongs to the current customer
        $customer = Customer::where('user_id', Auth::id())->first();
        if ($order->customer_id !== $customer?->id) {
            abort(403, 'Este pedido no te pertenece.');
        }

        $this->order = $order->load(['items.product', 'paisEnvio', 'payments', 'shipment']);
    }
};
?>

<div>
    <!-- Breadcrumb -->
    <div class="bg-white border-b border-zinc-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center gap-2 text-sm">
                <a href="/" wire:navigate class="text-zinc-500 hover:text-zinc-900 transition-colors">Inicio</a>
                <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <a href="/mi-cuenta/pedidos" wire:navigate class="text-zinc-500 hover:text-zinc-900 transition-colors">Mis Pedidos</a>
                <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="text-zinc-900 font-medium">{{ $order->numero }}</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            @include('livewire.store.account.partials.sidebar')

            <main class="flex-1 min-w-0">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-zinc-900">Pedido {{ $order->numero }}</h1>
                        <p class="text-sm text-zinc-500 mt-1">Realizado el {{ $order->created_at->format('d/m/Y \a \l\a\s H:i') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            {{ match($order->estado) {
                                'pendiente' => 'bg-yellow-100 text-yellow-800',
                                'confirmado' => 'bg-blue-100 text-blue-800',
                                'procesando' => 'bg-indigo-100 text-indigo-800',
                                'enviado' => 'bg-purple-100 text-purple-800',
                                'entregado' => 'bg-green-100 text-green-800',
                                'cancelado' => 'bg-red-100 text-red-800',
                                default => 'bg-zinc-100 text-zinc-800',
                            } }}">
                            {{ $order->estado_label }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            {{ $order->estado_pago === 'pagado' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                            {{ ucfirst($order->estado_pago) }}
                        </span>
                    </div>
                </div>

                <!-- Status Timeline -->
                <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 mb-6">
                    <h3 class="text-sm font-bold text-zinc-700 uppercase tracking-wide mb-4">Estado del Pedido</h3>
                    <div class="flex items-center gap-1 overflow-x-auto pb-2">
                        @php
                            $states = ['pendiente', 'confirmado', 'procesando', 'enviado', 'entregado'];
                            $currentIndex = array_search($order->estado, $states);
                            if ($order->estado === 'cancelado') $currentIndex = -1;
                        @endphp
                        @foreach($states as $i => $state)
                            <div class="flex items-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                                        {{ $i <= $currentIndex ? 'bg-green-500 text-white' : 'bg-zinc-100 text-zinc-400' }}">
                                        @if($i <= $currentIndex)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        @else
                                            {{ $i + 1 }}
                                        @endif
                                    </div>
                                    <span class="text-xs mt-1 text-zinc-500 whitespace-nowrap">{{ ucfirst($state) }}</span>
                                </div>
                                @if($i < count($states) - 1)
                                    <div class="w-8 sm:w-12 h-0.5 mx-1 {{ $i < $currentIndex ? 'bg-green-500' : 'bg-zinc-200' }}"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @if($order->estado === 'cancelado')
                        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                            Este pedido fue cancelado{{ $order->fecha_cancelacion ? ' el '.$order->fecha_cancelacion->format('d/m/Y H:i') : '' }}.
                        </div>
                    @endif
                </div>

                <!-- Order Items -->
                <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 mb-6">
                    <h3 class="text-sm font-bold text-zinc-700 uppercase tracking-wide mb-4">Artículos</h3>
                    <div class="divide-y divide-zinc-100">
                        @foreach($order->items as $item)
                        <div class="flex items-center gap-4 py-4">
                            <a href="{{ route('store.product.detail', optional($item->product)->slug ?? '#') }}" wire:navigate class="flex-shrink-0">
                                <img src="{{ optional($item->product)->imagen_principal ?? 'https://via.placeholder.com/80' }}" class="w-16 h-20 rounded-lg object-cover bg-zinc-100" alt="">
                            </a>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900">
                                    <a href="{{ route('store.product.detail', optional($item->product)->slug ?? '#') }}" wire:navigate class="hover:text-indigo-600 transition-colors">
                                        {{ optional($item->product)->nombre ?? 'Producto' }}
                                    </a>
                                </p>
                                <p class="text-xs text-zinc-500 mt-0.5">
                                    {{ $item->cantidad }} × ${{ number_format($item->precio, 2) }}
                                </p>
                            </div>
                            <p class="text-sm font-bold text-zinc-900">${{ number_format($item->subtotal ?? ($item->cantidad * $item->precio), 2) }}</p>
                        </div>
                        @endforeach
                    </div>

                    <!-- Totals -->
                    <div class="mt-4 pt-4 border-t border-zinc-100 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Subtotal</span>
                            <span>${{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($order->descuento > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Descuento{{ $order->codigo_cupon ? ' ('.$order->codigo_cupon.')' : '' }}</span>
                            <span>-${{ number_format($order->descuento, 2) }}</span>
                        </div>
                        @endif
                        @if($order->envio > 0)
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Envío</span>
                            <span>${{ number_format($order->envio, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-lg font-bold text-zinc-900 pt-2 border-t border-zinc-100">
                            <span>Total</span>
                            <span>${{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Shipping + Payment Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6">
                        <h3 class="text-sm font-bold text-zinc-700 uppercase tracking-wide mb-4">Dirección de Envío</h3>
                        <div class="text-sm text-zinc-600 space-y-1">
                            <p class="font-medium text-zinc-900">{{ $order->direccion_envio }}</p>
                            <p>{{ $order->ciudad_envio }}{{ $order->estado_envio ? ', '.$order->estado_envio : '' }}</p>
                            <p>{{ $order->codigo_postal_envio }}</p>
                            @if($order->paisEnvio)
                                <p>{{ $order->paisEnvio->nombre }}</p>
                            @endif
                        </div>
                        @if($order->numero_seguimiento)
                            <div class="mt-4 pt-4 border-t border-zinc-100">
                                <p class="text-sm text-zinc-600">
                                    <span class="font-medium">Seguimiento:</span> {{ $order->numero_seguimiento }}
                                </p>
                            </div>
                        @endif
                    </div>
                    <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6">
                        <h3 class="text-sm font-bold text-zinc-700 uppercase tracking-wide mb-4">Método de Pago</h3>
                        <p class="text-sm text-zinc-900 font-medium">
                            {{ $order->metodo_pago === 'contra_entrega' ? 'Pago contra entrega' : 'Transferencia bancaria' }}
                        </p>
                        <p class="text-sm text-zinc-500 mt-1">
                            Estado: <span class="font-medium {{ $order->estado_pago === 'pagado' ? 'text-green-600' : 'text-orange-600' }}">{{ ucfirst($order->estado_pago) }}</span>
                        </p>

                        @if($order->fecha_pago)
                            <p class="text-xs text-zinc-500 mt-2">
                                Pagado el {{ $order->fecha_pago->format('d/m/Y H:i') }}
                            </p>
                        @endif

                        @if($order->notas_cliente)
                            <div class="mt-4 pt-4 border-t border-zinc-100">
                                <p class="text-xs text-zinc-500 font-medium mb-1">Notas:</p>
                                <p class="text-sm text-zinc-600">{{ $order->notas_cliente }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>
