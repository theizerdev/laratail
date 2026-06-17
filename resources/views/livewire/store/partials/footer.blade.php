<?php
use Livewire\Volt\Component;
use App\Models\Category;

new class extends Component {
    public function with(): array
    {
        return [
            'categories' => Category::whereNull('parent_id')
                ->where('status', true)
                ->orderBy('orden')
                ->take(4)
                ->get(['nombre', 'slug']),
        ];
    }
};
?>

<footer class="bg-zinc-900 text-zinc-300 py-12 border-t border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Branding -->
            <div class="space-y-4">
                <a href="/" wire:navigate class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Laratail Store
                </a>
                <p class="text-sm text-zinc-400">
                    Tu destino para encontrar el mejor estilo y calidad. Ofrecemos productos cuidadosamente seleccionados para ti.
                </p>
            </div>

            <!-- Categories -->
            <div>
                <h3 class="text-white font-semibold mb-4">Tienda</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="/catalogo" wire:navigate class="hover:text-white transition-colors">Catálogo Completo</a></li>
                    @foreach($categories as $cat)
                        <li><a href="/catalogo/{{ $cat->slug }}" wire:navigate class="hover:text-white transition-colors">{{ $cat->nombre }}</a></li>
                    @endforeach
                    <li><a href="/catalogo?oferta=1" wire:navigate class="hover:text-white transition-colors">Ofertas Especiales</a></li>
                </ul>
            </div>

            <!-- Help -->
            <div>
                <h3 class="text-white font-semibold mb-4">Ayuda</h3>
                <ul class="space-y-2 text-sm">
                    @auth
                        <li><a href="/mi-cuenta/pedidos" wire:navigate class="hover:text-white transition-colors">Mis Pedidos</a></li>
                    @endauth
                    <li><a href="/catalogo" wire:navigate class="hover:text-white transition-colors">Cómo Comprar</a></li>
                    <li><span class="hover:text-white transition-colors cursor-pointer">Envíos y Devoluciones</span></li>
                    <li><span class="hover:text-white transition-colors cursor-pointer">Contacto</span></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div>
                <h3 class="text-white font-semibold mb-4">Suscríbete</h3>
                <p class="text-sm text-zinc-400 mb-4">Recibe las últimas ofertas y novedades en tu correo.</p>
                <form class="flex" onsubmit="event.preventDefault(); alert('Gracias por suscribirte!');">
                    <input type="email" placeholder="Tu correo electrónico" class="w-full bg-zinc-800 text-white border-zinc-700 focus:border-indigo-500 focus:ring-indigo-500 rounded-l-md text-sm px-4 py-2 outline-none" required>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-r-md hover:bg-indigo-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-zinc-800 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-zinc-500">
            <p>&copy; {{ date('Y') }} Laratail Store. Todos los derechos reservados.</p>
            <div class="flex space-x-4">
                <span class="hover:text-zinc-300 cursor-pointer">Términos de Servicio</span>
                <span class="hover:text-zinc-300 cursor-pointer">Política de Privacidad</span>
            </div>
        </div>
    </div>
</footer>
