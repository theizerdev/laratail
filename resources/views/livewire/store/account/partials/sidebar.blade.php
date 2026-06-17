{{-- Account Sidebar Navigation --}}
<aside class="w-full lg:w-64 flex-shrink-0">
    <nav class="bg-white rounded-2xl border border-zinc-100 shadow-sm overflow-hidden lg:sticky lg:top-24">
        <div class="p-4 bg-zinc-50 border-b border-zinc-100">
            <p class="text-sm font-semibold text-zinc-900 truncate">{{ auth()->user()->name }}</p>
            <p class="text-xs text-zinc-500 truncate">{{ auth()->user()->email }}</p>
        </div>
        <div class="p-2">
            @php
                $currentPath = request()->path();
            @endphp
            <a href="/mi-cuenta" wire:navigate class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-colors {{ $currentPath === 'mi-cuenta' ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-zinc-600 hover:bg-zinc-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Mi Perfil
            </a>
            <a href="/mi-cuenta/pedidos" wire:navigate class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-colors {{ str_starts_with($currentPath, 'mi-cuenta/pedidos') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-zinc-600 hover:bg-zinc-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Mis Pedidos
            </a>
            <a href="/mi-cuenta/direcciones" wire:navigate class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-colors {{ str_starts_with($currentPath, 'mi-cuenta/direcciones') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-zinc-600 hover:bg-zinc-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Direcciones
            </a>
            <hr class="my-2 border-zinc-100">
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 rounded-xl text-sm text-red-600 hover:bg-red-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </nav>
</aside>
