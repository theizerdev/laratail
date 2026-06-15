<div
    @if(in_array($connectionStatus, ['connecting', 'qr_ready']))
        wire:poll.5s="checkConnection"
    @elseif($connectionStatus === 'connected')
        wire:poll.30s="refreshDashboard"
    @endif
>
    {{-- Toast Notification --}}
    <div x-data="{ show: false, type: 'success', message: '' }"
         x-on:show-toast.window="show = true; type = $event.detail.type; message = $event.detail.message; setTimeout(() => show = false, 4000)"
         x-show="show" x-transition
         class="fixed top-4 right-4 z-50 max-w-sm rounded-xl px-4 py-3 text-sm font-medium shadow-lg"
         :class="type === 'success' ? 'bg-emerald-500 text-white' : type === 'warning' ? 'bg-amber-500 text-white' : 'bg-red-500 text-white'"
         style="display: none;">
        <span x-text="message"></span>
    </div>

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100">
                <iconify-icon icon="mdi:whatsapp" class="h-6 w-6 text-green-600"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900">WhatsApp CRM</h2>
                <p class="text-sm text-gray-500">Gestiona tu conexion, monitorea mensajes y revisa estadisticas en tiempo real.</p>
            </div>
        </div>
        <a href="{{ route('admin.integraciones') }}" wire:navigate>
            <flux:button variant="ghost">
                <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon>
                Volver
            </flux:button>
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- LEFT COLUMN: Config + Connection --}}
        <div class="space-y-6 xl:col-span-1">

            {{-- Section: Configuracion --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                    <iconify-icon icon="heroicons:cog-6-tooth" class="h-4 w-4 text-green-600"></iconify-icon>
                    Configuracion
                </h3>

                <div class="space-y-4">
                    <div>
                        <flux:label>Empresa</flux:label>
                        <select wire:model.live="empresaId" class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 px-3 text-sm focus:border-green-500 focus:outline-none focus:ring-green-500">
                            <option value="">Seleccionar empresa...</option>
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <flux:label>API Key</flux:label>
                        <div class="mt-1 flex gap-2">
                            <div class="relative flex-1" x-data="{ show: false }">
                                <input wire:model="apiKey" :type="show ? 'text' : 'password'"
                                    class="block w-full rounded-lg border-gray-300 py-2.5 px-3 pr-10 text-sm focus:border-green-500 focus:outline-none focus:ring-green-500"
                                    placeholder="Tu API Key de WhatsApp" />
                                <button type="button" @click="show = !show" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <iconify-icon :icon="show ? 'heroicons:eye-slash' : 'heroicons:eye'" class="h-4 w-4"></iconify-icon>
                                </button>
                            </div>
                            <button type="button" wire:click="generateApiKey"
                                class="flex items-center gap-1.5 rounded-lg bg-green-50 px-3 py-2 text-xs font-medium text-green-700 hover:bg-green-100 transition whitespace-nowrap">
                                <iconify-icon icon="heroicons:key" class="h-3.5 w-3.5"></iconify-icon>
                                Generar
                            </button>
                        </div>
                    </div>

                    <div>
                        <flux:label>URL del Servidor</flux:label>
                        <input wire:model="apiUrl" type="url"
                            class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 px-3 text-sm focus:border-green-500 focus:outline-none focus:ring-green-500"
                            placeholder="http://servidor:puerto" />
                    </div>

                    <flux:button wire:click="saveConfig" class="w-full !bg-green-600 hover:!bg-green-700">
                        <iconify-icon icon="heroicons:check-circle" class="h-4 w-4"></iconify-icon>
                        Guardar Configuracion
                    </flux:button>
                </div>
            </div>

            {{-- Section: Estado de Conexion --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                        <iconify-icon icon="heroicons:signal" class="h-4 w-4 text-green-600"></iconify-icon>
                        Estado de Conexion
                    </h3>
                    <button wire:click="refreshStatus" class="text-xs text-green-600 hover:text-green-800 transition">
                        <iconify-icon icon="heroicons:arrow-path" class="h-4 w-4 inline"></iconify-icon>
                        Actualizar
                    </button>
                </div>

                {{-- Status Badge --}}
                <div class="mb-4 flex items-center gap-3 rounded-xl p-3
                    @if($connectionStatus === 'connected') bg-emerald-50
                    @elseif($connectionStatus === 'connecting' || $connectionStatus === 'qr_ready') bg-amber-50
                    @elseif($connectionStatus === 'not_configured') bg-gray-50
                    @elseif($connectionStatus === 'service_unavailable') bg-orange-50
                    @else bg-red-50
                    @endif
                ">
                    @if($connectionStatus === 'connected')
                        <span class="h-3 w-3 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-sm font-medium text-emerald-700">Conectado</span>
                    @elseif($connectionStatus === 'connecting')
                        <iconify-icon icon="heroicons:arrow-path" class="h-4 w-4 text-amber-600 animate-spin"></iconify-icon>
                        <span class="text-sm font-medium text-amber-700">Conectando...</span>
                    @elseif($connectionStatus === 'qr_ready')
                        <iconify-icon icon="heroicons:qr-code" class="h-4 w-4 text-amber-600"></iconify-icon>
                        <span class="text-sm font-medium text-amber-700">QR listo para escanear</span>
                    @elseif($connectionStatus === 'not_configured')
                        <span class="h-3 w-3 rounded-full bg-gray-400"></span>
                        <span class="text-sm font-medium text-gray-600">Sin configurar</span>
                    @elseif($connectionStatus === 'service_unavailable')
                        <iconify-icon icon="heroicons:wifi" class="h-4 w-4 text-orange-500"></iconify-icon>
                        <span class="text-sm font-medium text-orange-700">Servicio no disponible</span>
                    @elseif($connectionStatus === 'error')
                        <iconify-icon icon="heroicons:exclamation-triangle" class="h-4 w-4 text-red-500"></iconify-icon>
                        <span class="text-sm font-medium text-red-700">Error</span>
                    @else
                        <span class="h-3 w-3 rounded-full bg-red-500"></span>
                        <span class="text-sm font-medium text-red-700">Desconectado</span>
                    @endif
                </div>

                @if($connectedPhone)
                    <div class="mb-4 flex items-center gap-2 text-sm text-gray-600">
                        <iconify-icon icon="heroicons:phone" class="h-4 w-4"></iconify-icon>
                        <span>{{ $connectedPhone }}</span>
                    </div>
                @endif

                @if($lastSeen)
                    <div class="mb-4 flex items-center gap-2 text-xs text-gray-500">
                        <iconify-icon icon="heroicons:eye" class="h-3.5 w-3.5"></iconify-icon>
                        <span>Visto por ultima vez: {{ $lastSeen }}</span>
                    </div>
                @endif

                @if($statusMessage)
                    <p class="mb-4 text-xs text-gray-500">{{ $statusMessage }}</p>
                @endif

                {{-- Action Buttons --}}
                <div class="grid grid-cols-2 gap-2">
                    <button wire:click="connectWhatsApp" wire:loading.attr="disabled"
                        class="flex items-center justify-center gap-1.5 rounded-lg bg-green-600 px-3 py-2 text-xs font-medium text-white hover:bg-green-700 disabled:opacity-50 transition">
                        <iconify-icon icon="heroicons:link" class="h-3.5 w-3.5"></iconify-icon>
                        Conectar
                    </button>
                    <button wire:click="disconnectWhatsApp" wire:loading.attr="disabled"
                        class="flex items-center justify-center gap-1.5 rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-200 disabled:opacity-50 transition">
                        <iconify-icon icon="heroicons:link-slash" class="h-3.5 w-3.5"></iconify-icon>
                        Desconectar
                    </button>
                    <button wire:click="reconnectWhatsApp" wire:loading.attr="disabled"
                        class="flex items-center justify-center gap-1.5 rounded-lg bg-amber-500 px-3 py-2 text-xs font-medium text-white hover:bg-amber-600 disabled:opacity-50 transition">
                        <iconify-icon icon="heroicons:arrow-path" class="h-3.5 w-3.5"></iconify-icon>
                        Reconectar
                    </button>
                    <button wire:click="removeSession" wire:confirm="Esta seguro de eliminar la sesion de WhatsApp?" wire:loading.attr="disabled"
                        class="flex items-center justify-center gap-1.5 rounded-lg bg-red-50 px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-100 disabled:opacity-50 transition">
                        <iconify-icon icon="heroicons:trash" class="h-3.5 w-3.5"></iconify-icon>
                        Eliminar Sesion
                    </button>
                </div>

                {{-- QR Code --}}
                <div class="mt-4">
                    <button wire:click="fetchQR" class="w-full flex items-center justify-center gap-1.5 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-xs font-medium text-green-700 hover:bg-green-100 transition">
                        <iconify-icon icon="heroicons:qr-code" class="h-3.5 w-3.5"></iconify-icon>
                        Obtener Codigo QR
                    </button>

                    @if($qrCodeData)
                        <div class="mt-3 flex flex-col items-center rounded-xl bg-white p-4 border border-gray-100">
                            @if(str_starts_with($qrCodeData, 'http'))
                                <img src="{{ $qrCodeData }}" alt="QR Code" class="h-48 w-48" />
                            @elseif(str_starts_with($qrCodeData, 'data:'))
                                <img src="{{ $qrCodeData }}" alt="QR Code" class="h-48 w-48" />
                            @else
                                <img src="data:image/png;base64,{{ $qrCodeData }}" alt="QR Code" class="h-48 w-48" />
                            @endif
                            <p class="mt-2 text-center text-xs text-gray-500">Escanea con tu telefono para vincular WhatsApp</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Dashboard + Stats + History --}}
        <div class="space-y-6 xl:col-span-2">

            {{-- Stats Cards --}}
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                        <iconify-icon icon="heroicons:chart-bar" class="h-4 w-4 text-green-600"></iconify-icon>
                        Estadisticas en Tiempo Real
                    </h3>
                    <button wire:click="refreshDashboard" class="text-xs text-green-600 hover:text-green-800 transition">
                        <iconify-icon icon="heroicons:arrow-path" class="h-4 w-4 inline"></iconify-icon>
                        Actualizar
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    {{-- Total --}}
                    <div class="rounded-xl bg-white p-4 shadow-sm text-center">
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                        <div class="mt-1 flex items-center justify-center gap-1 text-xs text-gray-500">
                            <iconify-icon icon="heroicons:envelope" class="h-3 w-3"></iconify-icon>
                            Total
                        </div>
                    </div>
                    {{-- Enviados --}}
                    <div class="rounded-xl bg-white p-4 shadow-sm text-center">
                        <div class="text-2xl font-bold text-emerald-600">{{ $stats['sent'] }}</div>
                        <div class="mt-1 flex items-center justify-center gap-1 text-xs text-emerald-600">
                            <iconify-icon icon="heroicons:check-circle" class="h-3 w-3"></iconify-icon>
                            Enviados
                        </div>
                    </div>
                    {{-- Entregados --}}
                    <div class="rounded-xl bg-white p-4 shadow-sm text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $stats['delivered'] }}</div>
                        <div class="mt-1 flex items-center justify-center gap-1 text-xs text-blue-600">
                            <iconify-icon icon="heroicons:check-badge" class="h-3 w-3"></iconify-icon>
                            Entregados
                        </div>
                    </div>
                    {{-- Fallidos --}}
                    <div class="rounded-xl bg-white p-4 shadow-sm text-center">
                        <div class="text-2xl font-bold text-red-600">{{ $stats['failed'] }}</div>
                        <div class="mt-1 flex items-center justify-center gap-1 text-xs text-red-600">
                            <iconify-icon icon="heroicons:x-circle" class="h-3 w-3"></iconify-icon>
                            Fallidos
                        </div>
                    </div>
                    {{-- Pendientes --}}
                    <div class="rounded-xl bg-white p-4 shadow-sm text-center">
                        <div class="text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</div>
                        <div class="mt-1 flex items-center justify-center gap-1 text-xs text-amber-600">
                            <iconify-icon icon="heroicons:clock" class="h-3 w-3"></iconify-icon>
                            Pendientes
                        </div>
                    </div>
                    {{-- Hoy --}}
                    <div class="rounded-xl bg-green-50 p-4 shadow-sm text-center ring-1 ring-green-200">
                        <div class="text-2xl font-bold text-green-700">{{ $stats['today'] }}</div>
                        <div class="mt-1 flex items-center justify-center gap-1 text-xs text-green-700">
                            <iconify-icon icon="heroicons:calendar-days" class="h-3 w-3"></iconify-icon>
                            Hoy
                        </div>
                    </div>
                </div>
            </div>

            {{-- Daily Stats --}}
            @if(count($dailyStats) > 0)
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                        <iconify-icon icon="heroicons:calendar" class="h-4 w-4 text-green-600"></iconify-icon>
                        Actividad Ultimos 7 Dias
                    </h3>
                    <div class="overflow-x-auto">
                        <div class="flex gap-2 min-w-max">
                            @foreach($dailyStats as $day)
                                <div class="flex flex-col items-center rounded-lg bg-gray-50 px-3 py-2 min-w-[70px]">
                                    <span class="text-xs font-medium text-gray-500">{{ $day['date'] ?? $day['day'] ?? '-' }}</span>
                                    <div class="mt-1 flex items-end gap-1">
                                        <div class="flex flex-col items-center">
                                            <div class="w-4 rounded bg-emerald-500" style="height: {{ max(4, min(40, ($day['sent'] ?? 0) * 4)) }}px"></div>
                                            <span class="mt-0.5 text-[10px] text-emerald-600">{{ $day['sent'] ?? 0 }}</span>
                                        </div>
                                        <div class="flex flex-col items-center">
                                            <div class="w-4 rounded bg-red-400" style="height: {{ max(4, min(40, ($day['failed'] ?? 0) * 4)) }}px"></div>
                                            <span class="mt-0.5 text-[10px] text-red-500">{{ $day['failed'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <span class="inline-block h-2 w-2 rounded bg-emerald-500"></span> Enviados
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="inline-block h-2 w-2 rounded bg-red-400"></span> Fallidos
                        </span>
                    </div>
                </div>
            @endif

            {{-- Top Recipients --}}
            @if(count($topRecipients) > 0)
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                        <iconify-icon icon="heroicons:user-group" class="h-4 w-4 text-green-600"></iconify-icon>
                        Principales Destinatarios
                    </h3>
                    <div class="space-y-2">
                        @foreach(array_slice($topRecipients, 0, 5) as $recipient)
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100">
                                        <iconify-icon icon="heroicons:user" class="h-4 w-4 text-green-600"></iconify-icon>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $recipient['name'] ?? $recipient['recipient_name'] ?? 'Sin nombre' }}</div>
                                        <div class="text-xs text-gray-500">{{ $recipient['phone'] ?? $recipient['recipient_phone'] ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-green-700">{{ $recipient['total_messages'] ?? $recipient['total'] ?? 0 }}</div>
                                    <div class="text-xs text-gray-400">mensajes</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Recent Messages --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                        <iconify-icon icon="heroicons:clock" class="h-4 w-4 text-green-600"></iconify-icon>
                        Mensajes Recientes
                    </h3>
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse"></span>
                        En vivo
                    </span>
                </div>

                @if(count($recentMessages) > 0)
                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-xs font-medium text-gray-500">Fecha</th>
                                    <th class="px-3 py-2 text-xs font-medium text-gray-500">Destinatario</th>
                                    <th class="px-3 py-2 text-xs font-medium text-gray-500">Mensaje</th>
                                    <th class="px-3 py-2 text-xs font-medium text-gray-500">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($recentMessages as $msg)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="whitespace-nowrap px-3 py-2 text-xs text-gray-500">
                                            {{ $msg['createdAt'] ?? $msg['date'] ?? $msg['created_at'] ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-sm text-gray-900">
                                            <div class="flex items-center gap-1.5">
                                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100">
                                                    <iconify-icon icon="heroicons:user" class="h-3 w-3 text-gray-500"></iconify-icon>
                                                </div>
                                                <span class="text-xs">{{ $msg['to'] ?? $msg['recipient'] ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td class="max-w-xs truncate px-3 py-2 text-sm text-gray-600">
                                            {{ $msg['message'] ?? $msg['body'] ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2">
                                            @php $msgStatus = $msg['status'] ?? 'unknown'; @endphp
                                            @if($msgStatus === 'delivered')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">
                                                    <iconify-icon icon="heroicons:check-badge" class="h-3 w-3"></iconify-icon>
                                                    Entregado
                                                </span>
                                            @elseif($msgStatus === 'sent')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                                    <iconify-icon icon="heroicons:check-circle" class="h-3 w-3"></iconify-icon>
                                                    Enviado
                                                </span>
                                            @elseif($msgStatus === 'failed')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700">
                                                    <iconify-icon icon="heroicons:x-circle" class="h-3 w-3"></iconify-icon>
                                                    Fallido
                                                </span>
                                            @elseif($msgStatus === 'pending')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">
                                                    <iconify-icon icon="heroicons:clock" class="h-3 w-3"></iconify-icon>
                                                    Pendiente
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                                                    {{ ucfirst($msgStatus) }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex flex-col items-center py-8 text-gray-400">
                        <iconify-icon icon="heroicons:chat-bubble-left-right" class="mb-2 h-8 w-8"></iconify-icon>
                        @if($connectionStatus === 'connected')
                            <p class="text-sm">Esperando mensajes... Se actualizaran automaticamente.</p>
                        @else
                            <p class="text-sm">Conecta WhatsApp para ver los mensajes en tiempo real.</p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Recent Activity --}}
            @if(count($recentActivity) > 0)
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                        <iconify-icon icon="heroicons:bell" class="h-4 w-4 text-green-600"></iconify-icon>
                        Actividad Reciente (24h)
                    </h3>
                    <div class="space-y-2">
                        @foreach(array_slice($recentActivity, 0, 8) as $activity)
                            <div class="flex items-center gap-3 text-sm">
                                @php $actStatus = $activity['status'] ?? ''; @endphp
                                @if($actStatus === 'sent' || $actStatus === 'delivered')
                                    <span class="h-2 w-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                                @elseif($actStatus === 'failed')
                                    <span class="h-2 w-2 rounded-full bg-red-500 flex-shrink-0"></span>
                                @else
                                    <span class="h-2 w-2 rounded-full bg-gray-300 flex-shrink-0"></span>
                                @endif
                                <span class="flex-1 text-gray-700">{{ $activity['action'] ?? $activity['message'] ?? '-' }}</span>
                                <span class="text-xs text-gray-400 whitespace-nowrap">{{ $activity['time'] ?? $activity['user'] ?? '' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
