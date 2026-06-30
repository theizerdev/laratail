<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="flex items-center gap-2 px-3 py-1.5 text-sm font-semibold text-zinc-700 bg-white border border-zinc-200 rounded-xl hover:bg-zinc-50 transition shadow-sm">
        <iconify-icon icon="heroicons:globe-alt-solid" class="w-4 h-4 text-indigo-600"></iconify-icon>
        <span>{{ $config['currency'] ?? 'USD' }} ({{ $config['currency_symbol'] ?? '$' }})</span>
        <iconify-icon icon="heroicons:chevron-down-solid" class="w-3.5 h-3.5 text-zinc-400"></iconify-icon>
    </button>
    
    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-56 rounded-xl bg-white border border-zinc-100 shadow-lg py-1 z-50" style="display: none;">
        <div class="px-4 py-2 border-b border-zinc-100">
            <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Región de la Empresa</p>
        </div>
        @foreach($empresas as $emp)
            <button wire:click="changeEmpresa({{ $emp->id }})" class="w-full text-left px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 flex items-center justify-between {{ $selectedEmpresaId == $emp->id ? 'bg-indigo-50/50 text-indigo-700 font-semibold' : '' }}">
                <div class="flex flex-col">
                    <span>{{ $emp->razon_social }}</span>
                    <span class="text-xs text-zinc-400 font-normal">{{ $emp->pais->nombre ?? 'Sin país' }}</span>
                </div>
                @if($selectedEmpresaId == $emp->id)
                    <iconify-icon icon="heroicons:check-circle-solid" class="w-4 h-4 text-indigo-600"></iconify-icon>
                @endif
            </button>
        @endforeach
    </div>
</div>
