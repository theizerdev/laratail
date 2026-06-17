<?php
use Livewire\Volt\Component;
use App\Models\Brand;

new class extends Component {
    public function with(): array
    {
        return [
            'brands' => Brand::where('status', true)
                ->whereHas('products', fn($q) => $q->where('status', true))
                ->withCount(['products' => fn($q) => $q->where('status', true)])
                ->orderByDesc('products_count')
                ->take(8)
                ->get(),
        ];
    }
};
?>

<div>
@if($brands->count() > 0)
<section class="py-16 md:py-20 bg-zinc-50 border-t border-zinc-100" id="brands-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-12">
            <span class="inline-block text-zinc-500 font-semibold text-sm tracking-widest uppercase mb-3">Marcas de Confianza</span>
            <h2 class="text-3xl md:text-4xl font-bold text-zinc-900 tracking-tight">Nuestras Marcas</h2>
            <p class="mt-4 text-lg text-zinc-500 max-w-2xl mx-auto">Trabajamos con las mejores marcas para garantizar la calidad que mereces.</p>
        </div>

        <!-- Brands Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 md:gap-6 max-w-4xl mx-auto">
            @foreach($brands as $brand)
            <div class="group flex flex-col items-center justify-center p-6 md:p-8 bg-white rounded-2xl border border-zinc-100 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-500/5 transition-all duration-300 cursor-default">
                @if($brand->logo)
                    <img src="{{ $brand->logo }}" alt="{{ $brand->nombre }}" class="h-10 md:h-12 object-contain grayscale group-hover:grayscale-0 opacity-60 group-hover:opacity-100 transition-all duration-300" />
                @else
                    <span class="text-lg md:text-xl font-bold text-zinc-300 group-hover:text-indigo-600 transition-colors duration-300 tracking-tight">{{ $brand->nombre }}</span>
                @endif
                <span class="mt-2 text-xs text-zinc-400 group-hover:text-zinc-500 transition-colors">{{ $brand->products_count }} {{ $brand->products_count === 1 ? 'producto' : 'productos' }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
</div>
