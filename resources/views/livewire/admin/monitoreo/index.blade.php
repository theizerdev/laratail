<div @if($activeTab === 'servidor') wire:poll.30s @endif>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-100">
                <iconify-icon icon="heroicons:presentation-chart-bar-solid" class="h-6 w-6 text-teal-600"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900">Monitoreo del Sistema</h2>
                <p class="text-sm text-gray-500">Estadísticas en tiempo real, historial de acceso y registro de actividades.</p>
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex gap-6">
            <button wire:click="setTab('servidor')"
                class="flex items-center gap-2 border-b-2 pb-3 text-sm font-medium transition
                {{ $activeTab === 'servidor' ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                <iconify-icon icon="heroicons:server-stack-solid" class="h-4 w-4"></iconify-icon>
                Servidor
            </button>
            <button wire:click="setTab('logins')"
                class="flex items-center gap-2 border-b-2 pb-3 text-sm font-medium transition
                {{ $activeTab === 'logins' ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                <iconify-icon icon="heroicons:arrow-right-on-rectangle-solid" class="h-4 w-4"></iconify-icon>
                Historial de Login
            </button>
            <button wire:click="setTab('actividades')"
                class="flex items-center gap-2 border-b-2 pb-3 text-sm font-medium transition
                {{ $activeTab === 'actividades' ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                <iconify-icon icon="heroicons:clipboard-document-list-solid" class="h-4 w-4"></iconify-icon>
                Actividades
            </button>
        </nav>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- TAB: Servidor --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if($activeTab === 'servidor')
        <div class="space-y-6">
            {{-- Info Cards Row 1 --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {{-- PHP Version --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100">
                            <iconify-icon icon="heroicons:code-bracket-solid" class="h-5 w-5 text-indigo-600"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">PHP Version</p>
                            <p class="text-lg font-bold text-gray-900">{{ $serverStats['php_version'] }}</p>
                        </div>
                    </div>
                </div>
                {{-- Laravel Version --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100">
                            <iconify-icon icon="heroicons:rocket-launch-solid" class="h-5 w-5 text-red-600"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Laravel Version</p>
                            <p class="text-lg font-bold text-gray-900">{{ $serverStats['laravel_version'] }}</p>
                        </div>
                    </div>
                </div>
                {{-- Environment --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100">
                            <iconify-icon icon="heroicons:cog-6-tooth-solid" class="h-5 w-5 text-amber-600"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Entorno</p>
                            <p class="text-lg font-bold text-gray-900">{{ ucfirst($serverStats['environment']) }}</p>
                        </div>
                    </div>
                </div>
                {{-- Debug Mode --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $serverStats['debug_mode'] === 'Activo' ? 'bg-orange-100' : 'bg-emerald-100' }}">
                            <iconify-icon icon="heroicons:bug-ant-solid" class="h-5 w-5 {{ $serverStats['debug_mode'] === 'Activo' ? 'text-orange-600' : 'text-emerald-600' }}"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Debug Mode</p>
                            <p class="text-lg font-bold {{ $serverStats['debug_mode'] === 'Activo' ? 'text-orange-600' : 'text-emerald-600' }}">{{ $serverStats['debug_mode'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info Cards Row 2: Memory & Disk --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {{-- Memory Usage --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                            <iconify-icon icon="heroicons:cpu-chip-solid" class="h-5 w-5 text-blue-600"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Memoria Usada</p>
                            <p class="text-lg font-bold text-gray-900">{{ $serverStats['memory_usage'] }}</p>
                            <p class="text-xs text-gray-400">Pico: {{ $serverStats['memory_peak'] }}</p>
                        </div>
                    </div>
                </div>
                {{-- Memory Limit --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100">
                            <iconify-icon icon="heroicons:arrow-up-tray-solid" class="h-5 w-5 text-purple-600"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Límite Memoria</p>
                            <p class="text-lg font-bold text-gray-900">{{ $serverStats['memory_limit'] }}</p>
                        </div>
                    </div>
                </div>
                {{-- Disk Space --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-100">
                            <iconify-icon icon="heroicons:circle-stack-solid" class="h-5 w-5 text-teal-600"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Disco Usado</p>
                            <p class="text-lg font-bold text-gray-900">{{ $serverStats['disk_percent'] }}%</p>
                            <p class="text-xs text-gray-400">{{ $serverStats['disk_used'] }} / {{ $serverStats['disk_total'] }}</p>
                        </div>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-gray-100">
                        <div class="h-2 rounded-full {{ $serverStats['disk_percent'] > 80 ? 'bg-red-500' : ($serverStats['disk_percent'] > 60 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                             style="width: {{ $serverStats['disk_percent'] }}%"></div>
                    </div>
                </div>
                {{-- Database Size --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-100">
                            <iconify-icon icon="heroicons:circle-stack-solid" class="h-5 w-5 text-cyan-600"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Base de Datos</p>
                            <p class="text-lg font-bold text-gray-900">{{ $serverStats['db_size'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info Cards Row 3: Sessions & Activity --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {{-- Active Sessions --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                            <iconify-icon icon="heroicons:user-group-solid" class="h-5 w-5 text-emerald-600"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Sesiones Activas (30min)</p>
                            <p class="text-lg font-bold text-gray-900">{{ $serverStats['active_sessions'] }}</p>
                        </div>
                    </div>
                </div>
                {{-- Active Users --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
                            <iconify-icon icon="heroicons:users-solid" class="h-5 w-5 text-green-600"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Usuarios en Línea</p>
                            <p class="text-lg font-bold text-gray-900">{{ $serverStats['active_users'] }} <span class="text-xs font-normal text-gray-400">/ {{ $serverStats['total_users'] }}</span></p>
                        </div>
                    </div>
                </div>
                {{-- Cache Driver --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-pink-100">
                            <iconify-icon icon="heroicons:bolt-solid" class="h-5 w-5 text-pink-600"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Cache Driver</p>
                            <p class="text-lg font-bold text-gray-900">{{ ucfirst($serverStats['cache_driver']) }}</p>
                        </div>
                    </div>
                </div>
                {{-- Activity Today --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100">
                            <iconify-icon icon="heroicons:clipboard-document-list-solid" class="h-5 w-5 text-violet-600"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Actividades Hoy</p>
                            <p class="text-lg font-bold text-gray-900">{{ $serverStats['today_activities'] }} <span class="text-xs font-normal text-gray-400">/ {{ $serverStats['total_activities'] }}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- System Info Row --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900">
                        <iconify-icon icon="heroicons:computer-desktop-solid" class="h-4 w-4 text-teal-600"></iconify-icon>
                        Información del Servidor
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Sistema Operativo</span>
                            <span class="font-medium text-gray-900">{{ $serverStats['os'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Carga del Servidor (1/5/15 min)</span>
                            <span class="font-medium text-gray-900">{{ $serverStats['server_load'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Espacio Libre</span>
                            <span class="font-medium text-gray-900">{{ $serverStats['disk_free'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900">
                        <iconify-icon icon="heroicons:information-circle-solid" class="h-4 w-4 text-teal-600"></iconify-icon>
                        Estado de la Aplicación
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Usuarios</span>
                            <span class="font-medium text-gray-900">{{ $serverStats['total_users'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Actividades Registradas</span>
                            <span class="font-medium text-gray-900">{{ $serverStats['total_activities'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Última Actualización</span>
                            <span class="font-medium text-gray-900">{{ now()->format('H:i:s') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- TAB: Historial de Login --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if($activeTab === 'logins')
        <div class="space-y-4">
            {{-- Filters --}}
            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-5">
                    <div>
                        <label class="text-xs text-gray-500">Usuario</label>
                        <input wire:model.live.debounce.300ms="loginFilterUser" type="text" placeholder="Buscar usuario..."
                            class="mt-1 block w-full rounded-lg border-gray-300 py-2 px-3 text-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Acción</label>
                        <select wire:model.live="loginFilterAction"
                            class="mt-1 block w-full rounded-lg border-gray-300 py-2 px-3 text-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500">
                            <option value="">Todas</option>
                            <option value="login">Inicio de sesión</option>
                            <option value="logout">Cierre de sesión</option>
                            <option value="login_failed">Intento fallido</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Desde</label>
                        <input wire:model.live="loginFilterDateFrom" type="date"
                            class="mt-1 block w-full rounded-lg border-gray-300 py-2 px-3 text-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Hasta</label>
                        <input wire:model.live="loginFilterDateTo" type="date"
                            class="mt-1 block w-full rounded-lg border-gray-300 py-2 px-3 text-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500" />
                    </div>
                    <div class="flex items-end">
                        <button wire:click="resetLoginFilters" class="w-full rounded-lg bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200 transition">
                            <iconify-icon icon="heroicons:x-mark" class="h-4 w-4 inline"></iconify-icon>
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500">Fecha</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500">Usuario</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500">Acción</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500">IP</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500">Navegador</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($loginHistory as $entry)
                                @php
                                    $props = $entry->properties ?? collect();
                                    $evento = $props['evento'] ?? '';
                                    $userName = $props['usuario_nombre'] ?? ($entry->causer?->name ?? 'Sistema');
                                    $ip = $props['ip_address'] ?? '-';
                                    $ua = $props['user_agent'] ?? '-';
                                    $browser = strlen($ua) > 50 ? substr($ua, 0, 50) . '...' : $ua;
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-500">
                                        {{ $entry->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm font-medium text-gray-900">{{ $userName }}</span>
                                        @if(isset($props['usuario_email']))
                                            <span class="block text-xs text-gray-400">{{ $props['usuario_email'] }}</span>
                                        @endif
                                        @if(isset($props['email_attemptado']))
                                            <span class="block text-xs text-red-400">{{ $props['email_attemptado'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($evento === 'login')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                                <iconify-icon icon="heroicons:arrow-right-on-rectangle" class="h-3 w-3"></iconify-icon>
                                                Inicio
                                            </span>
                                        @elseif($evento === 'logout')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">
                                                <iconify-icon icon="heroicons:arrow-left-on-rectangle" class="h-3 w-3"></iconify-icon>
                                                Cierre
                                            </span>
                                        @elseif($evento === 'login_failed')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700">
                                                <iconify-icon icon="heroicons:x-circle" class="h-3 w-3"></iconify-icon>
                                                Fallido
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">
                                                {{ $entry->description }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-500 font-mono">{{ $ip }}</td>
                                    <td class="max-w-xs truncate px-4 py-3 text-xs text-gray-400" title="{{ $ua }}">{{ $browser }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">
                                        <iconify-icon icon="heroicons:document-text" class="mx-auto mb-2 h-8 w-8 text-gray-300"></iconify-icon>
                                        No hay registros de login disponibles.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($loginHistory->hasPages())
                    <div class="border-t border-gray-100 px-4 py-3">
                        {{ $loginHistory->links(data: ['scroll' => false]) }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- TAB: Actividades --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if($activeTab === 'actividades')
        <div class="space-y-4">
            {{-- Filters --}}
            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-6">
                    <div>
                        <label class="text-xs text-gray-500">Usuario</label>
                        <input wire:model.live.debounce.300ms="activityFilterUser" type="text" placeholder="Buscar..."
                            class="mt-1 block w-full rounded-lg border-gray-300 py-2 px-3 text-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Modelo</label>
                        <select wire:model.live="activityFilterModel"
                            class="mt-1 block w-full rounded-lg border-gray-300 py-2 px-3 text-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500">
                            <option value="">Todos</option>
                            @foreach($modelTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Evento</label>
                        <select wire:model.live="activityFilterEvent"
                            class="mt-1 block w-full rounded-lg border-gray-300 py-2 px-3 text-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500">
                            <option value="">Todos</option>
                            <option value="created">Creado</option>
                            <option value="updated">Actualizado</option>
                            <option value="deleted">Eliminado</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Desde</label>
                        <input wire:model.live="activityFilterDateFrom" type="date"
                            class="mt-1 block w-full rounded-lg border-gray-300 py-2 px-3 text-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Hasta</label>
                        <input wire:model.live="activityFilterDateTo" type="date"
                            class="mt-1 block w-full rounded-lg border-gray-300 py-2 px-3 text-sm focus:border-teal-500 focus:outline-none focus:ring-teal-500" />
                    </div>
                    <div class="flex items-end">
                        <button wire:click="resetActivityFilters" class="w-full rounded-lg bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200 transition">
                            <iconify-icon icon="heroicons:x-mark" class="h-4 w-4 inline"></iconify-icon>
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 w-8"></th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500">Fecha</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500">Usuario</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500">Modelo</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500">Acción</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500">Descripción</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500">IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($activityLog as $entry)
                                @php
                                    $props = $entry->properties ?? collect();
                                    $modelName = $entry->subject_type ? class_basename($entry->subject_type) : '-';
                                    $userName = $props['usuario_nombre'] ?? ($entry->causer?->name ?? 'Sistema');
                                    $ip = $props['ip_address'] ?? '-';
                                    $event = $entry->event ?? '';
                                    $hasChanges = isset($props['old']) && isset($props['attributes']);
                                @endphp
                                <tr class="hover:bg-gray-50 transition {{ $expandedActivity === $entry->id ? 'bg-gray-50' : '' }}">
                                    <td class="px-4 py-3 text-center">
                                        @if($hasChanges)
                                            <button wire:click="toggleExpand({{ $entry->id }})" class="text-gray-400 hover:text-teal-600 transition">
                                                <iconify-icon icon="{{ $expandedActivity === $entry->id ? 'heroicons:chevron-down' : 'heroicons:chevron-right' }}" class="h-4 w-4"></iconify-icon>
                                            </button>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-500">
                                        {{ $entry->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm font-medium text-gray-900">{{ $userName }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full bg-teal-50 px-2 py-0.5 text-xs font-medium text-teal-700">
                                            {{ $modelName }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($event === 'created')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                                <iconify-icon icon="heroicons:plus-circle" class="h-3 w-3"></iconify-icon>
                                                Creado
                                            </span>
                                        @elseif($event === 'updated')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                                <iconify-icon icon="heroicons:pencil" class="h-3 w-3"></iconify-icon>
                                                Actualizado
                                            </span>
                                        @elseif($event === 'deleted')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700">
                                                <iconify-icon icon="heroicons:trash" class="h-3 w-3"></iconify-icon>
                                                Eliminado
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">
                                                {{ ucfirst($event) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="max-w-xs truncate px-4 py-3 text-sm text-gray-600">
                                        {{ $entry->description }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-500 font-mono">{{ $ip }}</td>
                                </tr>
                                {{-- Expanded Row: Change Details --}}
                                @if($expandedActivity === $entry->id && $hasChanges)
                                    <tr class="bg-gray-50">
                                        <td colspan="7" class="px-6 py-4">
                                            <h4 class="mb-2 text-xs font-semibold text-gray-700">
                                                <iconify-icon icon="heroicons:arrow-path" class="h-3.5 w-3.5 inline"></iconify-icon>
                                                Detalle de Cambios
                                            </h4>
                                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                                <table class="w-full text-left text-xs">
                                                    <thead class="bg-gray-100">
                                                        <tr>
                                                            <th class="px-3 py-2 font-medium text-gray-600">Campo</th>
                                                            <th class="px-3 py-2 font-medium text-red-600">Valor Anterior</th>
                                                            <th class="px-3 py-2 font-medium text-emerald-600">Valor Nuevo</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-100">
                                                        @foreach(($props['attributes'] ?? []) as $field => $newVal)
                                                            @php $oldVal = $props['old'][$field] ?? '-'; @endphp
                                                            <tr>
                                                                <td class="px-3 py-1.5 font-medium text-gray-700">{{ $field }}</td>
                                                                <td class="px-3 py-1.5 text-red-700 bg-red-50/50">{{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}</td>
                                                                <td class="px-3 py-1.5 text-emerald-700 bg-emerald-50/50">{{ is_array($newVal) ? json_encode($newVal) : $newVal }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">
                                        <iconify-icon icon="heroicons:clipboard-document-list" class="mx-auto mb-2 h-8 w-8 text-gray-300"></iconify-icon>
                                        No hay actividades registradas aún. Las acciones sobre modelos se registrarán automáticamente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($activityLog->hasPages())
                    <div class="border-t border-gray-100 px-4 py-3">
                        {{ $activityLog->links(data: ['scroll' => false]) }}
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
