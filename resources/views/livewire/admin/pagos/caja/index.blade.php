<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Caja</h1>
            <p class="mt-1 text-sm text-gray-500">Gestiona las cajas registradoras y los movimientos del día.</p>
        </div>
        <flux:button wire:click="openRegister" variant="primary" class="bg-emerald-600 hover:bg-emerald-700"><iconify-icon icon="heroicons:plus" class="h-4 w-4"></iconify-icon> Abrir Caja</flux:button>
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

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                <iconify-icon icon="heroicons:lock-open-solid" class="h-5 w-5 text-emerald-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Cajas Abiertas</p>
            <p class="text-xl font-bold text-gray-900">{{ $stats['abiertas'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-gray-100">
                <iconify-icon icon="heroicons:lock-closed-solid" class="h-5 w-5 text-gray-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Cerradas Hoy</p>
            <p class="text-xl font-bold text-gray-900">{{ $stats['cerradas_hoy'] }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
                <iconify-icon icon="heroicons:arrow-trending-up-solid" class="h-5 w-5 text-blue-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Ingresos (Abiertas)</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($stats['ingresos_hoy'], 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-gray-100">
                <iconify-icon icon="heroicons:calculator-solid" class="h-5 w-5 text-gray-600"></iconify-icon>
            </div>
            <p class="text-xs font-medium text-gray-500">Total Cajas</p>
            <p class="text-xl font-bold text-gray-900">{{ $stats['total_cajas'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4">
        <flux:select wire:model.live="filterEstado">
            <flux:select.option value="all">Todos los estados</flux:select.option>
            <flux:select.option value="abierta">Abierta</flux:select.option>
            <flux:select.option value="cerrada">Cerrada</flux:select.option>
        </flux:select>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Usuario</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Apertura</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Monto Inicial</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Ingresos</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Egresos</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($registers as $register)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-gray-900">#{{ $register->id }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">{{ $register->user?->name ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            @php $color = $register->estado_color; @endphp
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-{{ $color }}-50 px-2.5 py-1 text-xs font-semibold text-{{ $color }}-700 ring-1 ring-inset ring-{{ $color }}-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-{{ $color }}-500"></span>
                                {{ ucfirst($register->estado) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $register->fecha_apertura?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900">${{ number_format($register->monto_inicial, 2) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-emerald-600">${{ number_format($register->movements->where('tipo', 'ingreso')->sum('monto'), 2) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-red-600">${{ number_format($register->movements->where('tipo', 'egreso')->sum('monto'), 2) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                            <a href="{{ route('admin.caja.detalle', $register->id) }}">
                                <flux:button variant="ghost" size="sm" class="text-cyan-600"><iconify-icon icon="heroicons:eye" class="h-4 w-4"></iconify-icon> Ver detalle</flux:button>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <iconify-icon icon="heroicons:cash-register" class="h-10 w-10 text-gray-300"></iconify-icon>
                                <p class="text-sm text-gray-500">No hay cajas registradas.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $registers->links() }}</div>

    {{-- Open Register Modal --}}
    <flux:modal name="modal-abrir-caja" class="max-w-sm">
        <div class="p-1">
            <div class="mb-5 flex items-center gap-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                    <iconify-icon icon="heroicons:lock-open-solid" class="h-5 w-5 text-emerald-600"></iconify-icon>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Abrir Caja</h2>
            </div>
            <div>
                <flux:label>Monto Inicial</flux:label>
                <flux:input wire:model="monto_inicial" type="number" step="0.01" min="0" placeholder="0.00" />
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <flux:button variant="outline" onclick="Flux.modal('modal-abrir-caja').close()">Cancelar</flux:button>
                <flux:button wire:click="crearCaja" variant="primary" class="bg-emerald-600 hover:bg-emerald-700">Abrir Caja</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
