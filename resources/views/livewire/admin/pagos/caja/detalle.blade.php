<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Caja #{{ $register->id }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                Usuario: {{ $register->user?->name ?? '-' }} |
                Abierta: {{ $register->fecha_apertura?->format('d/m/Y H:i') ?? '-' }}
                @php $color = $register->estado_color; @endphp
                <span class="ml-2 inline-flex items-center gap-1.5 rounded-full bg-{{ $color }}-50 px-2.5 py-1 text-xs font-semibold text-{{ $color }}-700 ring-1 ring-inset ring-{{ $color }}-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-{{ $color }}-500"></span>
                    {{ ucfirst($register->estado) }}
                </span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.caja') }}">
                <flux:button variant="outline"><iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon> Volver</flux:button>
            </a>
            @if ($register->estado === 'abierta')
                <flux:button wire:click="openMovement" variant="primary"><iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon> Movimiento</flux:button>
                <flux:button wire:click="openClose" variant="primary" class="bg-red-600 hover:bg-red-700"><iconify-icon icon="heroicons:lock-closed-solid" class="h-4 w-4"></iconify-icon> Cerrar Caja</flux:button>
            @endif
        </div>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            <iconify-icon icon="heroicons:x-circle-solid" class="h-5 w-5"></iconify-icon>
            {{ session('error') }}
        </div>
    @endif

    {{-- Summary cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-5">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-gray-100">
                <iconify-icon icon="heroicons:currency-dollar-solid" class="h-5 w-5 text-gray-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Monto Inicial</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($register->monto_inicial, 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                <iconify-icon icon="heroicons:arrow-trending-up-solid" class="h-5 w-5 text-emerald-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Ingresos</p>
            <p class="text-xl font-bold text-emerald-600">${{ number_format($register->movements->where('tipo', 'ingreso')->sum('monto'), 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-red-100">
                <iconify-icon icon="heroicons:arrow-trending-down-solid" class="h-5 w-5 text-red-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Egresos</p>
            <p class="text-xl font-bold text-red-600">${{ number_format($register->movements->where('tipo', 'egreso')->sum('monto'), 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-cyan-100">
                <iconify-icon icon="heroicons:calculator-solid" class="h-5 w-5 text-cyan-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Esperado</p>
            <p class="text-xl font-bold text-cyan-600">${{ number_format($register->totalActual(), 2) }}</p>
        </div>
        @if ($register->estado === 'cerrada')
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-gray-100">
                    <iconify-icon icon="heroicons:flag-solid" class="h-5 w-5 text-gray-600"></iconify-icon>
                </div>
                <p class="text-xs font-medium text-gray-500">Monto Final</p>
                <p class="text-xl font-bold text-gray-900">${{ number_format($register->monto_final ?? 0, 2) }}</p>
                @if ($register->diferencia !== null)
                    <p class="mt-1 text-xs font-semibold {{ $register->diferencia == 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        Dif: ${{ number_format($register->diferencia, 2) }}
                    </p>
                @endif
            </div>
        @endif
    </div>

    {{-- Movements table --}}
    <div class="rounded-2xl bg-white shadow-sm">
        <div class="flex items-center gap-2 border-b border-gray-200 px-5 py-4">
            <iconify-icon icon="heroicons:arrows-right-left-solid" class="h-5 w-5 text-cyan-600"></iconify-icon>
            <h3 class="text-sm font-bold text-gray-900">Movimientos ({{ $register->movements->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Monto</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Descripción</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Usuario</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($register->movements as $mov)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                @php $mColor = $mov->tipo_color; @endphp
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-{{ $mColor }}-50 px-2.5 py-1 text-xs font-semibold text-{{ $mColor }}-700 ring-1 ring-inset ring-{{ $mColor }}-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-{{ $mColor }}-500"></span>
                                    {{ $mov->tipo_label }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-bold text-{{ $mColor }}-600">
                                {{ $mov->tipo === 'ingreso' ? '+' : '-' }}${{ number_format($mov->monto, 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $mov->descripcion ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">{{ $mov->user?->name ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $mov->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <iconify-icon icon="heroicons:arrows-right-left" class="h-10 w-10 text-gray-300"></iconify-icon>
                                    <p class="text-sm text-gray-500">No hay movimientos registrados.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Movement Modal --}}
    <flux:modal name="modal-movimiento" class="max-w-sm">
        <div class="p-1">
            <div class="mb-5 flex items-center gap-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-cyan-100">
                    <iconify-icon icon="heroicons:arrows-right-left-solid" class="h-5 w-5 text-cyan-600"></iconify-icon>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Nuevo Movimiento</h2>
            </div>
            <div class="space-y-4">
                <div>
                    <flux:label>Tipo</flux:label>
                    <flux:select wire:model="movTipo">
                        <flux:select.option value="ingreso">Ingreso</flux:select.option>
                        <flux:select.option value="egreso">Egreso</flux:select.option>
                    </flux:select>
                </div>
                <div>
                    <flux:label>Monto</flux:label>
                    <flux:input wire:model="movMonto" type="number" step="0.01" min="0" />
                </div>
                <div>
                    <flux:label>Descripción</flux:label>
                    <flux:input wire:model="movDescripcion" placeholder="Motivo del movimiento" />
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <flux:button variant="outline" x-on:click="Flux.modal('modal-movimiento').close()">Cancelar</flux:button>
                <flux:button wire:click="saveMovement" variant="primary">Guardar</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Close Modal --}}
    <flux:modal name="modal-cerrar-caja" class="max-w-sm">
        <div class="p-1">
            <div class="mb-5 flex items-center gap-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100">
                    <iconify-icon icon="heroicons:lock-closed-solid" class="h-5 w-5 text-red-600"></iconify-icon>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Cerrar Caja</h2>
            </div>
            <div class="mb-4 flex items-center gap-2 rounded-xl bg-amber-50 px-3 py-2.5 text-sm font-medium text-amber-700 ring-1 ring-inset ring-amber-200">
                <iconify-icon icon="heroicons:information-circle-solid" class="h-5 w-5"></iconify-icon>
                Monto esperado: <strong>${{ number_format($register->totalActual(), 2) }}</strong>
            </div>
            <div class="space-y-4">
                <div>
                    <flux:label>Monto Final (conteo real)</flux:label>
                    <flux:input wire:model="monto_final" type="number" step="0.01" min="0" />
                </div>
                <div>
                    <flux:label>Notas de Cierre</flux:label>
                    <flux:textarea wire:model="notas_cierre" rows="2" placeholder="Observaciones..." />
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <flux:button variant="outline" x-on:click="Flux.modal('modal-cerrar-caja').close()">Cancelar</flux:button>
                <flux:button wire:click="cerrarCaja" variant="primary" class="bg-red-600 hover:bg-red-700">Cerrar Caja</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
