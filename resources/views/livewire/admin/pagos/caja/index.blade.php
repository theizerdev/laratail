<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Caja</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestiona las cajas registradoras y los movimientos del día.</p>
        </div>
        <button wire:click="openRegister" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
             <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon> Abrir Caja
        </button>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-400">{{ session('error') }}</div>
    @endif

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/30">
            <p class="text-sm text-emerald-700 dark:text-emerald-400">Cajas Abiertas</p>
            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ $stats['abiertas'] }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Cerradas Hoy</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['cerradas_hoy'] }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Ingresos (Abiertas)</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($stats['ingresos_hoy'], 2) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Cajas</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_cajas'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4">
        <select wire:model.live="filterEstado" class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
            <option value="all">Todos los estados</option>
            <option value="abierta">Abierta</option>
            <option value="cerrada">Cerrada</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Usuario</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Apertura</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Monto Inicial</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Ingresos</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Egresos</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($registers as $register)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">#{{ $register->id }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $register->user?->name ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            <span class="inline-flex rounded-full bg-{{ $register->estado_color }}-100 px-2 py-0.5 text-xs font-medium text-{{ $register->estado_color }}-700">
                                {{ ucfirst($register->estado) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $register->fecha_apertura?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-white">${{ number_format($register->monto_inicial, 2) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-emerald-600">${{ number_format($register->movements->where('tipo', 'ingreso')->sum('monto'), 2) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-red-600">${{ number_format($register->movements->where('tipo', 'egreso')->sum('monto'), 2) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                            <a href="{{ route('admin.caja.detalle', $register->id) }}" class="text-cyan-600 hover:text-cyan-700 dark:text-cyan-400">
                                Ver detalle
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay cajas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $registers->links() }}</div>

    {{-- Open Register Modal --}}
    @if ($showOpenModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click.self="$set('showOpenModal', false)">
            <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Abrir Caja</h2>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Monto Inicial</label>
                    <input type="number" wire:model="monto_inicial" step="0.01" min="0"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="0.00" />
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="$set('showOpenModal', false)" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">Cancelar</button>
                    <button wire:click="crearCaja" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Abrir Caja</button>
                </div>
            </div>
        </div>
    @endif
</div>
