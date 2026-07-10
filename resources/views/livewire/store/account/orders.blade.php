<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app', ['meta_robots' => 'noindex, nofollow'])] class extends Component {
    public function with(): array
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $orders = Order::where('customer_id', $customer?->id)
            ->with('items.product')
            ->orderByDesc('created_at')
            ->paginate(10);

        return ['orders' => $orders];
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
                <a href="/mi-cuenta" wire:navigate class="text-zinc-500 hover:text-zinc-900 transition-colors">Mi Cuenta</a>
                <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="text-zinc-900 font-medium">Mis Pedidos</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            @include('livewire.store.account.partials.sidebar')

            <main class="flex-1 min-w-0">
                <h1 class="text-2xl font-bold text-zinc-900 mb-6">Mis Pedidos</h1>

                @if($orders->isEmpty())
                    <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-zinc-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        <h3 class="text-lg font-semibold text-zinc-700 mb-2">Aún no tienes pedidos</h3>
                        <p class="text-zinc-500 mb-6">¡Explora nuestro catálogo y haz tu primer pedido!</p>
                        <a href="/catalogo" wire:navigate class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors">
                            Explorar Catálogo
                        </a>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($orders as $order)
                        <a href="/mi-cuenta/pedidos/{{ $order->id }}" wire:navigate class="block bg-white rounded-2xl border border-zinc-100 shadow-sm hover:shadow-md transition-shadow p-5 sm:p-6">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                                <div>
                                    <p class="text-sm font-bold text-zinc-900">{{ $order->numero }}</p>
                                    <p class="text-xs text-zinc-500 mt-0.5">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
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
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $order->estado_pago === 'pagado' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                        Pago: {{ ucfirst($order->estado_pago) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Items preview -->
                            <div class="flex items-center gap-3">
                                <div class="flex -space-x-2">
                                    @foreach($order->items->take(3) as $item)
                                        <img src="{{ optional($item->product)->imagen_principal_url ?? 'https://placehold.co/40' }}" class="w-10 h-10 rounded-lg object-cover border-2 border-white bg-zinc-100" alt="">
                                    @endforeach
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-zinc-600 truncate">
                                        {{ $order->items->count() }} {{ $order->items->count() === 1 ? 'artículo' : 'artículos' }}
                                    </p>
                                </div>
                                <p class="text-lg font-bold text-zinc-900">${{ number_format($order->total, 2) }}</p>
                                <svg class="w-5 h-5 text-zinc-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                        </a>
                        @endforeach
                    </div>

                    @if($orders->hasPages())
                        <div class="mt-6">{{ $orders->links() }}</div>
                    @endif
                @endif
            </main>
        </div>
    </div>
</div>
