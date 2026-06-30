<div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 p-6">
        <h2 class="text-2xl font-bold text-zinc-900 mb-2">Prueba de Configuración Regional</h2>
        <p class="text-zinc-500 mb-6">Selecciona una empresa para aplicar su configuración regional y ver cómo se formatean los montos y fechas.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Selector -->
            <div>
                <h3 class="text-sm font-semibold text-zinc-700 uppercase tracking-wider mb-4">Empresa / País</h3>
                <div class="space-y-2">
                    @foreach($empresas as $emp)
                        <button wire:click="changeEmpresa({{ $emp->id }})" class="w-full text-left p-4 rounded-xl border {{ session('current_empresa_id') == $emp->id ? 'border-indigo-600 bg-indigo-50/20 text-indigo-900' : 'border-zinc-200 hover:bg-zinc-50 text-zinc-700' }} transition flex items-center justify-between">
                            <div>
                                <p class="font-semibold">{{ $emp->razon_social }}</p>
                                <p class="text-xs text-zinc-500">{{ $emp->pais->nombre ?? 'N/A' }} ({{ $emp->pais->moneda_principal ?? 'N/A' }})</p>
                            </div>
                            <span class="text-xs font-mono bg-zinc-100 px-2 py-1 rounded text-zinc-600">{{ $emp->pais->codigo_iso2 ?? 'N/A' }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Valores actualizados -->
            <div class="space-y-6">
                <div>
                    <h3 class="text-sm font-semibold text-zinc-700 uppercase tracking-wider mb-3">Valores de Configuración</h3>
                    <div class="bg-zinc-50 rounded-xl p-4 space-y-2 font-mono text-sm">
                        <p class="flex justify-between"><span>Moneda:</span> <strong>{{ $config['currency'] }}</strong></p>
                        <p class="flex justify-between"><span>Símbolo:</span> <strong>{{ $config['currency_symbol'] }}</strong></p>
                        <p class="flex justify-between"><span>Zona Horaria:</span> <strong>{{ $config['timezone'] }}</strong></p>
                        <p class="flex justify-between"><span>Formato Fecha:</span> <strong>{{ $config['date_format'] }}</strong></p>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-zinc-700 uppercase tracking-wider mb-3">Demostración de Formateo</h3>
                    <div class="bg-zinc-50 rounded-xl p-4 space-y-4">
                        <div>
                            <p class="text-xs text-zinc-400">Monto Base (USD 1234.56):</p>
                            <p class="text-xl font-bold text-indigo-600">{{ format_money(1234.56) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-zinc-400">Fecha Actual:</p>
                            <p class="text-base font-semibold text-zinc-800">{{ format_date($dateStr) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
