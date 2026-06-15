<div>
    {{-- Hero Section --}}
    <div class="mb-6 rounded-2xl bg-gradient-to-r from-teal-600 to-indigo-600 p-6 text-white shadow-lg">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="flex items-center gap-2 text-xl font-bold">
                    <iconify-icon icon="heroicons:circle-stack-solid" class="h-6 w-6"></iconify-icon>
                    Exportar e Importar Base de Datos
                </h2>
                <p class="mt-1 text-sm text-white/80">Gestiona respaldos y restauración del sistema de forma segura</p>
            </div>
            <button type="button" wire:click="switchTab('{{ $activeTab }}')"
                class="inline-flex items-center gap-1 rounded-lg bg-white/20 px-3 py-1.5 text-xs font-medium text-white backdrop-blur-sm transition hover:bg-white/30">
                <iconify-icon icon="heroicons:arrow-path-solid" class="h-3.5 w-3.5"></iconify-icon>
                Reiniciar
            </button>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if($successMessage)
        <div class="mb-4 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 p-4">
            <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5 shrink-0 text-green-500"></iconify-icon>
            <div class="flex-1">
                <p class="text-sm font-semibold text-green-800">¡Éxito!</p>
                <p class="text-sm text-green-700">{{ $successMessage }}</p>
            </div>
            <button wire:click="$set('successMessage', '')" class="text-green-400 hover:text-green-600">
                <iconify-icon icon="heroicons:x-mark-solid" class="h-4 w-4"></iconify-icon>
            </button>
        </div>
    @endif

    @if($errorMessage)
        <div class="mb-4 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4">
            <iconify-icon icon="heroicons:exclamation-circle-solid" class="h-5 w-5 shrink-0 text-red-500"></iconify-icon>
            <div class="flex-1">
                <p class="text-sm font-semibold text-red-800">Error</p>
                <p class="text-sm text-red-700">{{ $errorMessage }}</p>
            </div>
            <button wire:click="$set('errorMessage', '')" class="text-red-400 hover:text-red-600">
                <iconify-icon icon="heroicons:x-mark-solid" class="h-4 w-4"></iconify-icon>
            </button>
        </div>
    @endif

    {{-- Stat Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100">
                <iconify-icon icon="heroicons:circle-stack-solid" class="h-5 w-5 text-blue-600"></iconify-icon>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Total tablas</p>
                <p class="text-lg font-bold text-gray-900">{{ $totalTables }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-green-100">
                <iconify-icon icon="heroicons:server-stack-solid" class="h-5 w-5 text-green-600"></iconify-icon>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Tamaño estimado</p>
                <p class="text-lg font-bold text-gray-900">{{ $estimatedFileSize }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100">
                <iconify-icon icon="heroicons:arrow-up-tray-solid" class="h-5 w-5 text-amber-600"></iconify-icon>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Exportar</p>
                <p class="text-lg font-bold text-gray-900">SQL</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-100">
                <iconify-icon icon="heroicons:arrow-down-tray-solid" class="h-5 w-5 text-red-600"></iconify-icon>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Importar</p>
                <p class="text-lg font-bold text-gray-900">.sql</p>
            </div>
        </div>
    </div>

    {{-- Main Tabs --}}
    <div class="mb-6 rounded-2xl bg-white p-2 shadow-sm">
        <div class="flex gap-2">
            <button wire:click="switchTab('export')"
                class="flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium transition
                {{ $activeTab === 'export' ? 'bg-gradient-to-r from-teal-600 to-indigo-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-50' }}">
                <iconify-icon icon="heroicons:arrow-up-tray-solid" class="h-4 w-4"></iconify-icon>
                Exportar Base de Datos
            </button>
            <button wire:click="switchTab('import')"
                class="flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium transition
                {{ $activeTab === 'import' ? 'bg-gradient-to-r from-teal-600 to-indigo-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-50' }}">
                <iconify-icon icon="heroicons:arrow-down-tray-solid" class="h-4 w-4"></iconify-icon>
                Importar Base de Datos
            </button>
        </div>
    </div>

    @if($activeTab === 'export')
        {{-- ==================== EXPORT TAB ==================== --}}

        {{-- Wizard Steps Indicator --}}
        <div class="mb-6 grid grid-cols-4 gap-3">
            @foreach([['step' => 1, 'label' => 'Opciones'], ['step' => 2, 'label' => 'Vista previa'], ['step' => 3, 'label' => 'Confirmación'], ['step' => 4, 'label' => 'Progreso']] as $stepInfo)
                <div class="text-center">
                    <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold transition
                        {{ $exportStep > $stepInfo['step'] ? 'bg-green-500 text-white' : ($exportStep >= $stepInfo['step'] ? 'bg-gradient-to-r from-teal-600 to-indigo-600 text-white' : 'bg-gray-200 text-gray-500') }}">
                        @if($exportStep > $stepInfo['step'])
                            <iconify-icon icon="heroicons:check-solid" class="h-5 w-5"></iconify-icon>
                        @else
                            {{ $stepInfo['step'] }}
                        @endif
                    </div>
                    <span class="text-xs font-semibold text-gray-600">{{ $stepInfo['label'] }}</span>
                </div>
            @endforeach
        </div>

        {{-- Step 1: Configuration Options --}}
        @if($exportStep === 1)
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-5 flex items-center gap-2 text-base font-semibold text-gray-900">
                    <iconify-icon icon="heroicons:cog-6-tooth-solid" class="h-5 w-5 text-teal-600"></iconify-icon>
                    Configuración de Exportación
                </h3>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    {{-- Incluir estructura --}}
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 p-4 transition hover:border-teal-200 hover:bg-teal-50/30 {{ $exportOptions['include_structure'] ? 'border-teal-300 bg-teal-50/50' : '' }}">
                        <input type="checkbox" wire:model="exportOptions.include_structure" class="mt-0.5 rounded border-gray-300 text-teal-600 focus:ring-teal-500" />
                        <div>
                            <p class="flex items-center gap-1 text-sm font-medium text-gray-900">
                                <iconify-icon icon="heroicons:building-office-solid" class="h-4 w-4 text-gray-400"></iconify-icon>
                                Incluir estructura
                            </p>
                            <p class="text-xs text-gray-500">CREATE TABLE statements con definiciones completas</p>
                        </div>
                    </label>
                    {{-- Incluir datos --}}
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 p-4 transition hover:border-teal-200 hover:bg-teal-50/30 {{ $exportOptions['include_data'] ? 'border-teal-300 bg-teal-50/50' : '' }}">
                        <input type="checkbox" wire:model="exportOptions.include_data" class="mt-0.5 rounded border-gray-300 text-teal-600 focus:ring-teal-500" />
                        <div>
                            <p class="flex items-center gap-1 text-sm font-medium text-gray-900">
                                <iconify-icon icon="heroicons:circle-stack-solid" class="h-4 w-4 text-gray-400"></iconify-icon>
                                Incluir datos
                            </p>
                            <p class="text-xs text-gray-500">INSERT statements con todos los registros</p>
                        </div>
                    </label>
                    {{-- Agregar DROP TABLE --}}
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 p-4 transition hover:border-teal-200 hover:bg-teal-50/30 {{ $exportOptions['add_drop_table'] ? 'border-teal-300 bg-teal-50/50' : '' }}">
                        <input type="checkbox" wire:model="exportOptions.add_drop_table" class="mt-0.5 rounded border-gray-300 text-teal-600 focus:ring-teal-500" />
                        <div>
                            <p class="flex items-center gap-1 text-sm font-medium text-gray-900">
                                <iconify-icon icon="heroicons:trash-solid" class="h-4 w-4 text-gray-400"></iconify-icon>
                                Agregar DROP TABLE
                            </p>
                            <p class="text-xs text-gray-500">Eliminar tablas existentes antes de crearlas</p>
                        </div>
                    </label>
                    {{-- IF NOT EXISTS --}}
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 p-4 transition hover:border-teal-200 hover:bg-teal-50/30 {{ $exportOptions['add_if_not_exists'] ? 'border-teal-300 bg-teal-50/50' : '' }}">
                        <input type="checkbox" wire:model="exportOptions.add_if_not_exists" class="mt-0.5 rounded border-gray-300 text-teal-600 focus:ring-teal-500" />
                        <div>
                            <p class="flex items-center gap-1 text-sm font-medium text-gray-900">
                                <iconify-icon icon="heroicons:shield-check-solid" class="h-4 w-4 text-gray-400"></iconify-icon>
                                IF NOT EXISTS
                            </p>
                            <p class="text-xs text-gray-500">Solo crear si la tabla no existe</p>
                        </div>
                    </label>
                    {{-- Comprimir archivo --}}
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 p-4 transition hover:border-teal-200 hover:bg-teal-50/30 {{ $exportOptions['compress'] ? 'border-teal-300 bg-teal-50/50' : '' }}">
                        <input type="checkbox" wire:model="exportOptions.compress" class="mt-0.5 rounded border-gray-300 text-teal-600 focus:ring-teal-500" />
                        <div>
                            <p class="flex items-center gap-1 text-sm font-medium text-gray-900">
                                <iconify-icon icon="heroicons:archive-box-solid" class="h-4 w-4 text-gray-400"></iconify-icon>
                                Comprimir archivo
                            </p>
                            <p class="text-xs text-gray-500">Generar archivo .sql.gz (gzip)</p>
                        </div>
                    </label>
                </div>

                <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-500">
                        <iconify-icon icon="heroicons:information-circle-solid" class="inline h-3.5 w-3.5"></iconify-icon>
                        Se exportarán {{ $totalTables }} tablas ({{ $estimatedFileSize }} estimado)
                    </p>
                    <button wire:click="nextExportStep"
                        class="inline-flex items-center gap-1 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                        Siguiente
                        <iconify-icon icon="heroicons:chevron-right-solid" class="h-4 w-4"></iconify-icon>
                    </button>
                </div>
            </div>
        @endif

        {{-- Step 2: Preview --}}
        @if($exportStep === 2)
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-5 flex items-center gap-2 text-base font-semibold text-gray-900">
                    <iconify-icon icon="heroicons:eye-solid" class="h-5 w-5 text-teal-600"></iconify-icon>
                    Vista Previa de Exportación
                </h3>

                <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 p-4">
                    <div class="flex items-start gap-2">
                        <iconify-icon icon="heroicons:information-circle-solid" class="h-5 w-5 shrink-0 text-blue-500"></iconify-icon>
                        <div>
                            <p class="text-sm font-semibold text-blue-800">Resumen de configuración:</p>
                            <ul class="mt-2 space-y-1 text-sm text-blue-700">
                                <li>Estructura: {{ $exportOptions['include_structure'] ? 'Sí' : 'No' }}</li>
                                <li>Datos: {{ $exportOptions['include_data'] ? 'Sí' : 'No' }}</li>
                                <li>Tablas a exportar: {{ $totalTables }}</li>
                                <li>Tamaño estimado: {{ $estimatedFileSize }}</li>
                                <li>Compresión: {{ $exportOptions['compress'] ? 'Sí (.sql.gz)' : 'No (.sql)' }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <h4 class="mb-3 text-sm font-semibold text-gray-900">Tablas incluidas:</h4>
                <div class="max-h-72 overflow-y-auto rounded-xl border border-gray-100">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-gray-50">
                            <tr class="border-b border-gray-100">
                                <th class="px-4 py-2 text-left font-medium text-gray-500">Tabla</th>
                                <th class="px-4 py-2 text-center font-medium text-gray-500">Estructura</th>
                                <th class="px-4 py-2 text-center font-medium text-gray-500">Datos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($availableTables as $table => $label)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-4 py-2">
                                        <code class="rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-teal-700">{{ $table }}</code>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        @if($exportOptions['include_structure'])
                                            <iconify-icon icon="heroicons:check-circle-solid" class="h-4 w-4 text-green-500"></iconify-icon>
                                        @else
                                            <iconify-icon icon="heroicons:x-circle-solid" class="h-4 w-4 text-red-400"></iconify-icon>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        @if($exportOptions['include_data'])
                                            <iconify-icon icon="heroicons:check-circle-solid" class="h-4 w-4 text-green-500"></iconify-icon>
                                        @else
                                            <iconify-icon icon="heroicons:x-circle-solid" class="h-4 w-4 text-red-400"></iconify-icon>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4">
                    <button wire:click="previousExportStep"
                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                        <iconify-icon icon="heroicons:chevron-left-solid" class="h-4 w-4"></iconify-icon>
                        Anterior
                    </button>
                    <button wire:click="nextExportStep"
                        class="inline-flex items-center gap-1 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                        Siguiente
                        <iconify-icon icon="heroicons:chevron-right-solid" class="h-4 w-4"></iconify-icon>
                    </button>
                </div>
            </div>
        @endif

        {{-- Step 3: Confirmation --}}
        @if($exportStep === 3)
            <div class="rounded-2xl bg-white p-8 shadow-sm">
                <div class="mx-auto max-w-md text-center">
                    <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-r from-teal-600 to-indigo-600">
                        <iconify-icon icon="heroicons:shield-check-solid" class="h-10 w-10 text-white"></iconify-icon>
                    </div>
                    <h3 class="mb-2 text-lg font-bold text-gray-900">¿Confirmar exportación?</h3>
                    <p class="text-sm text-gray-500">Esta operación generará un respaldo completo de la base de datos</p>
                </div>

                <div class="mx-auto mt-6 max-w-md rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <div class="flex items-start gap-2">
                        <iconify-icon icon="heroicons:exclamation-triangle-solid" class="h-5 w-5 shrink-0 text-amber-500"></iconify-icon>
                        <div>
                            <p class="text-sm font-semibold text-amber-800">Información importante:</p>
                            <ul class="mt-2 space-y-1 text-sm text-amber-700">
                                <li>Se solicitará tu contraseña para confirmar</li>
                                <li>La acción será registrada en el log de auditoría</li>
                                <li>El archivo se descargará automáticamente</li>
                                <li>Tiempo estimado: 1-5 minutos</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between border-t border-gray-100 pt-4">
                    <button wire:click="previousExportStep"
                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                        <iconify-icon icon="heroicons:chevron-left-solid" class="h-4 w-4"></iconify-icon>
                        Anterior
                    </button>
                    <button wire:click="requestPasswordVerification('export')"
                        class="inline-flex items-center gap-1 rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-600">
                        <iconify-icon icon="heroicons:lock-closed-solid" class="h-4 w-4"></iconify-icon>
                        Confirmar con Contraseña
                    </button>
                </div>
            </div>
        @endif

        {{-- Step 4: Progress & Completion --}}
        @if($exportStep === 4)
            <div class="rounded-2xl bg-white p-8 shadow-sm"
                 x-data="{
                     progress: 0,
                     timer: null,
                     done: false,
                     init() {
                         // Animate progress 0→90% over ~4 seconds
                         this.timer = setInterval(() => {
                             if (this.progress < 90) {
                                 this.progress += Math.floor(Math.random() * 6) + 5;
                                 if (this.progress > 90) this.progress = 90;
                             }
                         }, 400);
                         // After animation completes, snap to 100% and show success
                         setTimeout(() => {
                             clearInterval(this.timer);
                             this.progress = 100;
                             setTimeout(() => { this.done = true; }, 800);
                         }, 4000);
                     }
                 }">
                {{-- Progress State --}}
                <div x-show="!done" x-transition class="mx-auto max-w-md text-center">
                    <div class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-gray-200 border-t-teal-600"></div>
                    <h3 class="mb-2 text-lg font-bold text-gray-900">Exportando base de datos...</h3>
                    <p class="mb-5 text-sm text-gray-500">Esto puede tomar varios minutos dependiendo del tamaño</p>

                    <div class="mx-auto mb-3 h-3 max-w-sm overflow-hidden rounded-full bg-gray-200">
                        <div class="h-full rounded-full bg-gradient-to-r from-teal-600 to-indigo-600 transition-all duration-500 ease-out"
                             :style="'width: ' + progress + '%'"></div>
                    </div>
                    <p class="text-xs font-semibold text-gray-500" x-text="progress + '% completado'"></p>

                    <p class="mt-5 text-xs text-gray-400">
                        <iconify-icon icon="heroicons:clock-solid" class="inline h-3.5 w-3.5"></iconify-icon>
                        Por favor no cierres esta ventana
                    </p>
                </div>

                {{-- Completed State --}}
                <div x-show="done" x-transition class="mx-auto max-w-md text-center">
                    <iconify-icon icon="heroicons:check-circle-solid" class="mx-auto mb-3 h-16 w-16 text-green-500"></iconify-icon>
                    <h3 class="mb-2 text-lg font-bold text-gray-900">Exportación completada</h3>
                    <p class="mb-5 text-sm text-gray-500">El archivo se ha descargado exitosamente</p>
                    <button wire:click="resetExport"
                        class="inline-flex items-center gap-1 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                        <iconify-icon icon="heroicons:arrow-path-solid" class="h-4 w-4"></iconify-icon>
                        Nueva exportación
                    </button>
                </div>
            </div>
        @endif

        {{-- Recent Backups History --}}
        @if(count($recentBackups) > 0)
            <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-4 flex items-center gap-2 text-base font-semibold text-gray-900">
                    <iconify-icon icon="heroicons:clock-solid" class="h-5 w-5 text-teal-600"></iconify-icon>
                    Historial de Respaldos
                    <span class="ml-auto text-xs font-normal text-gray-400">Últimos 5</span>
                </h3>

                <div class="space-y-2">
                    @foreach($recentBackups as $backup)
                        <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50/50 px-4 py-3 transition hover:bg-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $backup['compressed'] ? 'bg-amber-100' : 'bg-teal-100' }}">
                                    <iconify-icon icon="{{ $backup['compressed'] ? 'heroicons:archive-box-solid' : 'heroicons:document-text-solid' }}" class="h-4 w-4 {{ $backup['compressed'] ? 'text-amber-600' : 'text-teal-600' }}"></iconify-icon>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $backup['filename'] }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $backup['human_size'] }} • {{ $backup['user_name'] }} • {{ $backup['created_at_diff'] }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.monitoreo.backup-download', ['backup' => $backup['id']]) }}"
                                   class="inline-flex items-center gap-1 rounded-lg bg-teal-50 px-3 py-1.5 text-xs font-medium text-teal-700 transition hover:bg-teal-100">
                                    <iconify-icon icon="heroicons:arrow-down-tray-solid" class="h-3.5 w-3.5"></iconify-icon>
                                    Descargar
                                </a>
                                <button wire:click="deleteBackup({{ $backup['id'] }})"
                                    wire:confirm="¿Estás seguro de eliminar este respaldo?"
                                    class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100">
                                    <iconify-icon icon="heroicons:trash-solid" class="h-3.5 w-3.5"></iconify-icon>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    @if($activeTab === 'import')
        {{-- ==================== IMPORT TAB ==================== --}}

        {{-- Wizard Steps Indicator --}}
        <div class="mb-6 grid grid-cols-4 gap-3">
            @foreach([['step' => 1, 'label' => 'Subir archivo'], ['step' => 2, 'label' => 'Validación'], ['step' => 3, 'label' => 'Confirmación'], ['step' => 4, 'label' => 'Progreso']] as $stepInfo)
                <div class="text-center">
                    <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold transition
                        {{ $importStep > $stepInfo['step'] ? 'bg-green-500 text-white' : ($importStep >= $stepInfo['step'] ? 'bg-gradient-to-r from-teal-600 to-indigo-600 text-white' : 'bg-gray-200 text-gray-500') }}">
                        @if($importStep > $stepInfo['step'])
                            <iconify-icon icon="heroicons:check-solid" class="h-5 w-5"></iconify-icon>
                        @else
                            {{ $stepInfo['step'] }}
                        @endif
                    </div>
                    <span class="text-xs font-semibold text-gray-600">{{ $stepInfo['label'] }}</span>
                </div>
            @endforeach
        </div>

        {{-- Step 1: Upload File --}}
        @if($importStep === 1)
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-5 flex items-center gap-2 text-base font-semibold text-gray-900">
                    <iconify-icon icon="heroicons:cloud-arrow-up-solid" class="h-5 w-5 text-teal-600"></iconify-icon>
                    Subir Archivo SQL
                </h3>

                <div class="rounded-xl border-2 border-dashed border-indigo-300 bg-indigo-50/30 p-10 text-center transition hover:border-teal-300 hover:bg-teal-50/20">
                    <iconify-icon icon="heroicons:document-arrow-up-solid" class="mx-auto mb-3 h-12 w-12 text-indigo-400"></iconify-icon>
                    <h4 class="mb-1 text-sm font-semibold text-gray-700">Arrastra tu archivo .sql aquí</h4>
                    <p class="mb-4 text-xs text-gray-500">o</p>
                    <input type="file" wire:model="uploadedFile" accept=".sql,.sql.gz"
                        class="mx-auto block text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-teal-700 hover:file:bg-teal-100" />
                    <p class="mt-3 text-xs text-gray-400">
                        <iconify-icon icon="heroicons:information-circle-solid" class="inline h-3.5 w-3.5"></iconify-icon>
                        Formatos aceptados: .sql, .sql.gz (máx. 100MB)
                    </p>
                </div>

                @if($uploadedFile)
                    <div class="mt-4 flex items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 p-3">
                        <iconify-icon icon="heroicons:document-text-solid" class="h-5 w-5 shrink-0 text-blue-500"></iconify-icon>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Archivo seleccionado: {{ $importFileName }}</p>
                            <p class="text-xs text-gray-500">Tamaño: {{ number_format($importFileSize / 1024 / 1024, 2) }} MB</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <button wire:click="$set('uploadedFile', null)"
                            class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                            <iconify-icon icon="heroicons:x-mark-solid" class="h-4 w-4"></iconify-icon>
                            Cancelar
                        </button>
                        <button wire:click="validateImportFile"
                            class="inline-flex items-center gap-1 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                            Validar Archivo
                            <iconify-icon icon="heroicons:check-solid" class="h-4 w-4"></iconify-icon>
                        </button>
                    </div>
                @endif

                @error('uploadedFile')
                    <div class="mt-4 flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 p-3">
                        <iconify-icon icon="heroicons:exclamation-circle-solid" class="h-4 w-4 text-red-500"></iconify-icon>
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    </div>
                @enderror
            </div>
        @endif

        {{-- Step 2: Validation Results --}}
        @if($importStep === 2 && $importValidationResults)
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-5 flex items-center gap-2 text-base font-semibold text-gray-900">
                    <iconify-icon icon="heroicons:shield-check-solid" class="h-5 w-5 text-green-500"></iconify-icon>
                    Resultados de Validación
                </h3>

                <div class="mb-4 flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 p-3">
                    <iconify-icon icon="heroicons:check-circle-solid" class="h-5 w-5 text-green-500"></iconify-icon>
                    <p class="text-sm font-medium text-green-800">Archivo válido: El archivo SQL ha sido validado exitosamente</p>
                </div>

                <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-xl border border-gray-100 p-4 text-center">
                        <iconify-icon icon="heroicons:circle-stack-solid" class="mx-auto mb-2 h-6 w-6 text-teal-600"></iconify-icon>
                        <p class="text-xs font-semibold text-gray-500">Tablas detectadas</p>
                        <p class="text-2xl font-bold text-teal-600">{{ $importValidationResults['total_tables'] }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 p-4 text-center">
                        <iconify-icon icon="heroicons:code-bracket-solid" class="mx-auto mb-2 h-6 w-6 text-green-600"></iconify-icon>
                        <p class="text-xs font-semibold text-gray-500">Sentencias</p>
                        <p class="text-2xl font-bold text-green-600">{{ $importValidationResults['total_statements'] }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 p-4 text-center">
                        <iconify-icon icon="heroicons:server-stack-solid" class="mx-auto mb-2 h-6 w-6 text-blue-600"></iconify-icon>
                        <p class="text-xs font-semibold text-gray-500">Tamaño archivo</p>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($importFileSize / 1024 / 1024, 2) }} MB</p>
                    </div>
                </div>

                @if($importValidationResults['has_structure'])
                    <div class="mb-2 flex items-center gap-2 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-sm text-blue-700">
                        <iconify-icon icon="heroicons:building-office-solid" class="h-4 w-4"></iconify-icon>
                        <strong>Estructura:</strong> El archivo contiene definiciones de tablas (CREATE TABLE)
                    </div>
                @endif

                @if($importValidationResults['has_data'])
                    <div class="mb-2 flex items-center gap-2 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-sm text-blue-700">
                        <iconify-icon icon="heroicons:circle-stack-solid" class="h-4 w-4"></iconify-icon>
                        <strong>Datos:</strong> El archivo contiene registros de datos (INSERT INTO)
                    </div>
                @endif

                @if(count($importValidationResults['warnings']) > 0)
                    <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-3">
                        <p class="mb-2 flex items-center gap-1 text-sm font-semibold text-amber-800">
                            <iconify-icon icon="heroicons:exclamation-triangle-solid" class="h-4 w-4"></iconify-icon>
                            Advertencias:
                        </p>
                        <ul class="space-y-1 text-sm text-amber-700">
                            @foreach($importValidationResults['warnings'] as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4">
                    <button wire:click="previousImportStep"
                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                        <iconify-icon icon="heroicons:chevron-left-solid" class="h-4 w-4"></iconify-icon>
                        Anterior
                    </button>
                    <button wire:click="nextImportStep"
                        class="inline-flex items-center gap-1 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                        Siguiente
                        <iconify-icon icon="heroicons:chevron-right-solid" class="h-4 w-4"></iconify-icon>
                    </button>
                </div>
            </div>
        @endif

        {{-- Step 3: Confirmation --}}
        @if($importStep === 3)
            <div class="rounded-2xl bg-white p-8 shadow-sm">
                <div class="mx-auto max-w-md text-center">
                    <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-r from-red-500 to-orange-500">
                        <iconify-icon icon="heroicons:exclamation-triangle-solid" class="h-10 w-10 text-white"></iconify-icon>
                    </div>
                    <h3 class="mb-2 text-lg font-bold text-gray-900">¿Confirmar importación?</h3>
                    <p class="text-sm text-gray-500">Esta operación restaurará la base de datos con el archivo seleccionado</p>
                </div>

                <div class="mx-auto mt-6 max-w-md rounded-xl border border-red-200 bg-red-50 p-4">
                    <div class="flex items-start gap-2">
                        <iconify-icon icon="heroicons:exclamation-triangle-solid" class="h-5 w-5 shrink-0 text-red-500"></iconify-icon>
                        <div>
                            <p class="text-sm font-bold text-red-800">¡ADVERTENCIA CRÍTICA!</p>
                            <ul class="mt-2 space-y-1 text-sm text-red-700">
                                <li>Se solicitará tu contraseña para confirmar</li>
                                <li>La acción será registrada en el log de auditoría</li>
                                <li>Las tablas existentes serán reemplazadas</li>
                                <li>Esta operación NO se puede deshacer</li>
                                <li>Asegúrate de tener un respaldo antes de continuar</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between border-t border-gray-100 pt-4">
                    <button wire:click="previousImportStep"
                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                        <iconify-icon icon="heroicons:chevron-left-solid" class="h-4 w-4"></iconify-icon>
                        Anterior
                    </button>
                    <button wire:click="requestPasswordVerification('import')"
                        class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700">
                        <iconify-icon icon="heroicons:lock-closed-solid" class="h-4 w-4"></iconify-icon>
                        Confirmar Importación
                    </button>
                </div>
            </div>
        @endif

        {{-- Step 4: Progress & Completion --}}
        @if($importStep === 4)
            <div class="rounded-2xl bg-white p-8 shadow-sm"
                 x-data="{
                     progress: 0,
                     timer: null,
                     done: false,
                     init() {
                         // Animate progress 0→90% over ~4 seconds
                         this.timer = setInterval(() => {
                             if (this.progress < 90) {
                                 this.progress += Math.floor(Math.random() * 6) + 5;
                                 if (this.progress > 90) this.progress = 90;
                             }
                         }, 400);
                         // After animation completes, snap to 100% and show success
                         setTimeout(() => {
                             clearInterval(this.timer);
                             this.progress = 100;
                             setTimeout(() => { this.done = true; }, 800);
                         }, 4000);
                     }
                 }">
                {{-- Progress State --}}
                <div x-show="!done" x-transition class="mx-auto max-w-md text-center">
                    <div class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-gray-200 border-t-red-500"></div>
                    <h3 class="mb-2 text-lg font-bold text-gray-900">Importando base de datos...</h3>
                    <p class="mb-5 text-sm text-gray-500">Ejecutando sentencias SQL</p>

                    <div class="mx-auto mb-3 h-3 max-w-sm overflow-hidden rounded-full bg-gray-200">
                        <div class="h-full rounded-full bg-red-500 transition-all duration-500 ease-out"
                             :style="'width: ' + progress + '%'"></div>
                    </div>
                    <p class="text-xs font-semibold text-gray-500" x-text="progress + '% completado'"></p>

                    <p class="mt-5 text-xs text-gray-400">
                        <iconify-icon icon="heroicons:clock-solid" class="inline h-3.5 w-3.5"></iconify-icon>
                        Por favor no cierres esta ventana
                    </p>
                </div>

                {{-- Completed State --}}
                <div x-show="done" x-transition class="mx-auto max-w-md text-center">
                    <iconify-icon icon="heroicons:check-circle-solid" class="mx-auto mb-3 h-16 w-16 text-green-500"></iconify-icon>
                    <h3 class="mb-2 text-lg font-bold text-gray-900">Importación completada</h3>
                    <p class="mb-5 text-sm text-gray-500">La base de datos ha sido restaurada exitosamente</p>
                    <button wire:click="resetImport"
                        class="inline-flex items-center gap-1 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
                        <iconify-icon icon="heroicons:arrow-path-solid" class="h-4 w-4"></iconify-icon>
                        Nueva importación
                    </button>
                </div>
            </div>
        @endif
    @endif

    {{-- Password Confirmation Modal --}}
    <div wire:ignore.self
         x-data="{ show: false }"
         x-on:show-password-modal.window="show = true"
         x-on:hide-password-modal.window="show = false"
         x-show="show"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/50 p-4"
         role="dialog">
        <div x-show="show" x-transition class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
            {{-- Header --}}
            <div class="rounded-t-2xl bg-amber-500 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-base font-bold text-white">
                        <iconify-icon icon="heroicons:lock-closed-solid" class="h-5 w-5"></iconify-icon>
                        Confirmación de Seguridad
                    </h3>
                    <button @click="show = false" class="text-white/80 hover:text-white">
                        <iconify-icon icon="heroicons:x-mark-solid" class="h-5 w-5"></iconify-icon>
                    </button>
                </div>
            </div>
            {{-- Body --}}
            <div class="p-6">
                <div class="mb-4 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3">
                    <iconify-icon icon="heroicons:exclamation-triangle-solid" class="h-5 w-5 shrink-0 text-amber-500"></iconify-icon>
                    <p class="text-sm text-amber-700">Esta es una operación crítica del sistema que modificará la base de datos.</p>
                </div>

                <div class="mb-4">
                    <label class="mb-1 flex items-center gap-1 text-sm font-semibold text-gray-700">
                        <iconify-icon icon="heroicons:key-solid" class="h-4 w-4"></iconify-icon>
                        Ingresa tu contraseña para continuar
                    </label>
                    <input type="password" wire:model="password"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-teal-300 focus:outline-none focus:ring-2 focus:ring-teal-200"
                        placeholder="Tu contraseña de administrador"
                        id="passwordInput"
                        x-on:keydown.enter="$wire.verifyPassword()" />
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-2">
                    <p class="text-xs text-blue-700">
                        <iconify-icon icon="heroicons:information-circle-solid" class="inline h-3.5 w-3.5"></iconify-icon>
                        Esta acción será registrada en el log de auditoría.
                    </p>
                </div>
            </div>
            {{-- Footer --}}
            <div class="flex items-center justify-end gap-2 border-t border-gray-100 px-6 py-4">
                <button @click="show = false"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    Cancelar
                </button>
                <button wire:click="verifyPassword"
                    class="inline-flex items-center gap-1 rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-600">
                    <iconify-icon icon="heroicons:check-solid" class="h-4 w-4"></iconify-icon>
                    Confirmar y Continuar
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('trigger-download', ({ url }) => {
                const link = document.createElement('a');
                link.href = url;
                link.download = '';
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
    @endpush
</div>
