<?php
use Livewire\Volt\Component;
use App\Models\Product;

new class extends Component {
    public string $query = '';
    public $results = [];
    public bool $isOpen = false;

    public function updatedQuery(): void
    {
        if (strlen($this->query) < 2) {
            $this->results = [];
            return;
        }

        $this->results = Product::where('status', true)
            ->where('nombre', 'like', '%' . $this->query . '%')
            ->select(['id', 'nombre', 'slug', 'imagen_principal', 'precio', 'precio_oferta', 'stock'])
            ->limit(6)
            ->get()
            ->toArray();
    }

    public function open(): void
    {
        $this->isOpen = true;
        $this->query = '';
        $this->results = [];
    }

    public function close(): void
    {
        $this->isOpen = false;
    }
};
?>

<div x-on:open-search.window="$wire.open()" x-on:keydown.escape.window="$wire.close()">
    <div x-show="$wire.isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm"
         x-on:click="$wire.close()"
         style="display: none;">

        <div class="max-w-2xl mx-auto mt-[10vh] px-4" x-on:click.stop>
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Search Input -->
                <div class="flex items-center gap-3 px-5 border-b border-zinc-100">
                    <svg class="w-5 h-5 text-zinc-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text"
                           wire:model.live.debounce.300ms="query"
                           placeholder="Buscar productos..."
                           class="w-full py-4 text-base border-0 focus:ring-0 text-zinc-900 placeholder-zinc-400 outline-none"
                           autofocus>
                    <button x-on:click="$wire.close()" class="text-zinc-400 hover:text-zinc-600 transition-colors p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Results -->
                @if(!empty($results))
                <div class="max-h-[60vh] overflow-y-auto divide-y divide-zinc-50">
                    @foreach($results as $result)
                    <a href="/producto/{{ $result['slug'] }}" wire:navigate class="flex items-center gap-4 px-5 py-3 hover:bg-zinc-50 transition-colors" x-on:click="$wire.close()">
                        <img src="{{ $result['imagen_principal'] ?? 'https://via.placeholder.com/60' }}" class="w-14 h-14 rounded-lg object-cover bg-zinc-100 flex-shrink-0" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-zinc-900 truncate">{{ $result['nombre'] }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                @if($result['precio_oferta'] && $result['precio_oferta'] < $result['precio'])
                                    <span class="text-sm font-bold text-red-600">${{ number_format($result['precio_oferta'], 2) }}</span>
                                    <span class="text-xs text-zinc-400 line-through">${{ number_format($result['precio'], 2) }}</span>
                                @else
                                    <span class="text-sm font-bold text-zinc-900">${{ number_format($result['precio'], 2) }}</span>
                                @endif
                                @if($result['stock'] <= 0)
                                    <span class="text-xs text-red-500">Agotado</span>
                                @endif
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-zinc-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                    @endforeach
                </div>
                @elseif(strlen($query) >= 2)
                <div class="px-5 py-8 text-center">
                    <p class="text-sm text-zinc-500">No se encontraron productos para "{{ $query }}"</p>
                </div>
                @elseif(strlen($query) > 0)
                <div class="px-5 py-8 text-center">
                    <p class="text-sm text-zinc-400">Escribe al menos 2 caracteres...</p>
                </div>
                @else
                <div class="px-5 py-8 text-center">
                    <p class="text-sm text-zinc-400">Escribe para buscar productos...</p>
                </div>
                @endif

                <!-- Footer -->
                <div class="px-5 py-3 bg-zinc-50 border-t border-zinc-100 flex items-center justify-between text-xs text-zinc-400">
                    <span>ESC para cerrar</span>
                    <span>↵ para seleccionar</span>
                </div>
            </div>
        </div>
    </div>
</div>
