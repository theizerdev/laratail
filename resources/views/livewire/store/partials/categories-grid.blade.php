<?php
use Livewire\Volt\Component;
use App\Models\Category;

new class extends Component {
    public function with(): array
    {
        return [
            'categories' => Category::whereNull('parent_id')
                ->where('status', true)
                ->withCount(['products' => fn($q) => $q->where('status', true)])
                ->orderBy('orden')
                ->take(6)
                ->get(),
        ];
    }
};
?>

<div>
@if($categories->count() > 0)
<section class="py-16 md:py-20 bg-white" id="categories-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-12">
            <span class="inline-block text-indigo-600 font-semibold text-sm tracking-widest uppercase mb-3">Explora</span>
            <h2 class="text-3xl md:text-4xl font-bold text-zinc-900 tracking-tight">Categorías Populares</h2>
            <p class="mt-4 text-lg text-zinc-500 max-w-2xl mx-auto">Navega por nuestras colecciones cuidadosamente curadas para encontrar exactamente lo que necesitas.</p>
        </div>

        <!-- Categories Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 md:gap-6">
            @foreach($categories as $category)
            <a href="{{ route('store.catalog.category', $category->slug) }}" wire:navigate
               class="group relative flex flex-col items-center text-center p-6 rounded-2xl bg-gradient-to-br from-zinc-50 to-zinc-100/50 border border-zinc-100 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-500/10 transition-all duration-300 hover:-translate-y-1">
                <!-- Category Icon -->
                <div class="w-16 h-16 mb-4 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/25 group-hover:scale-110 transition-transform duration-300">
                    @if($category->imagen)
                        <img src="{{ $category->imagen }}" alt="{{ $category->nombre }}" class="w-10 h-10 object-contain rounded-lg" />
                    @else
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    @endif
                </div>
                <h3 class="text-sm font-semibold text-zinc-800 group-hover:text-indigo-600 transition-colors mb-1">{{ $category->nombre }}</h3>
                <span class="text-xs text-zinc-400">{{ $category->products_count }} {{ $category->products_count === 1 ? 'producto' : 'productos' }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
</div>
