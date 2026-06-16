<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Caja #{{ $register->id }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Usuario: {{ $register->user?->name ?? '-' }} |
                Abierta: {{ $register->fecha_apertura?->format('d/m/Y H:i') ?? '-' }}
                <span class="ml-2 inline-flex rounded-full bg-{{ $register->estado_color }}-100 px-2 py-0.5 text-xs font-medium text-{{ $register->estado_color }}-700">
                    {{ ucfirst($register->estado) }}
                </span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.caja') }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
                 <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon> Volver
            </a>
            @if ($register->estado === 'abierta')
                <button wire:click="openMovement" class="inline-flex items-center gap-1 rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700">
                     <iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon> Movimiento
                </button>
                <button wire:click="openClose" class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    <x-heroicons:lock-closed class="h-4 w-4" /> Cerrar Caja
                </button>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-400">{{ session('error') }}</div>
    @endif

    {{-- Summary cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-5">
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Monto Inicial</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">${{ number_format($register->monto_inicial, 2) }}</p>
        </div>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/30">
            <p class="text-sm text-emerald-700 dark:text-emerald-400">Ingresos</p>
            <p class="text-xl font-bold text-emerald-700 dark:text-emerald-400">${{ number_format($register->movements->where('tipo', 'ingreso')->sum('monto'), 2) }}</p>
        </div>
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/30">
            <p class="text-sm text-red-700 dark:text-red-400">Egresos</p>
            <p class="text-xl font-bold text-red-700 dark:text-red-400">${{ number_format($register->movements->where('tipo', 'egreso')->sum('monto'), 2) }}</p>
        </div>
        <div class="rounded-lg border border-cyan-200 bg-cyan-50 p-4 dark:border-cyan-800 dark:bg-cyan-900/30">
            <p class="text-sm text-cyan-700 dark:text-cyan-400">Esperado</p>
            <p class="text-xl font-bold text-cyan-700 dark:text-cyan-400">${{ number_format($register->totalActual(), 2) }}</p>
        </div>
        @if ($register->estado === 'cerrada')
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-400">Monto Final</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">${{ number_format($register->monto_final ?? 0, 2) }}</p>
                @if ($register->diferencia !== null)
                    <p class="text-xs {{ $register->diferencia == 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        Dif: ${{ number_format($register->diferencia, 2) }}
                    </p>
                @endif
            </div>
        @endif
    </div>

    {{-- Movements table --}}
    <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Movimientos ({{ $register->movements->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Tipo</th>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Monto</th>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Descripción</th>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Usuario</th>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($register->movements as $mov)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="whitespace-nowrap px-4 py-2 text-sm">
                                <span class="inline-flex rounded-full bg-{{ $mov->tipo_color }}-100 px-2 py-0.5 text-xs font-medium text-{{ $mov->tipo_color }}-700">
                                    {{ $mov->tipo_label }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-2 text-sm font-semibold text-{{ $mov->tipo_color }}-600">
                                {{ $mov->tipo === 'ingreso' ? '+' : '-' }}${{ number_format($mov->monto, 2) }}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $mov->descripcion ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $mov->user?->name ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $mov->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay movimientos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Movement Modal --}}
    @if ($showMovementModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click.self="$set('showMovementModal', false)">
            <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Nuevo Movimiento</h2>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                        <select wire:model="movTipo" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="ingreso">Ingreso</option>
                            <option value="egreso">Egreso</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Monto</label>
                        <input type="number" wire:model="movMonto" step="0.01" min="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                        <input type="text" wire:model="movDescripcion" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Motivo del movimiento" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="$set('showMovementModal', false)" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">Cancelar</button>
                    <button wire:click="saveMovement" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700">Guardar</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Close Modal --}}
    @if ($showCloseModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click.self="$set('showCloseModal', false)">
            <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Cerrar Caja</h2>
                <div class="mb-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                    Monto esperado: <strong>${{ number_format($register->totalActual(), 2) }}</strong>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Monto Final (conteo real)</label>
                        <input type="number" wire:model="monto_final" step="0.01" min="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Notas de Cierre</label>
                        <textarea wire:model="notas_cierre" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Observaciones..."></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="$set('showCloseModal', false)" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">Cancelar</button>
                    <button wire:click="cerrarCaja" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Cerrar Caja</button>
                </div>
            </div>
        </div>
    @endif
</div>
