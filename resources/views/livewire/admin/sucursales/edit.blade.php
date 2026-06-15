<div>
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Editar Sucursal: {{ $sucursal->nombre }}</h2>
            <p class="mt-1 text-sm text-gray-500">Modifica los datos de la sucursal y su ubicacion.</p>
        </div>
        <a href="{{ route('admin.sucursales') }}" wire:navigate>
            <flux:button variant="ghost">
                <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon>
                Volver
            </flux:button>
        </a>
    </div>

    {{-- Form --}}
    <div class="space-y-6">
        {{-- Datos Principales --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Datos de la Sucursal</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input
                    wire:model="nombre"
                    label="Nombre"
                    placeholder="Ej: Sucursal Centro"
                    :error="$errors->first('nombre')"
                    required
                />
                <flux:input
                    wire:model="telefono"
                    label="Telefono"
                    placeholder="+58 212 1234567"
                    :error="$errors->first('telefono')"
                />
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>Empresa</flux:label>
                    <select
                        wire:model="empresa_id"
                        class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 px-3 text-sm focus:border-amber-500 focus:outline-none focus:ring-amber-500"
                    >
                        <option value="">Seleccionar empresa</option>
                        @foreach ($empresas as $empresa)
                            <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                        @endforeach
                    </select>
                    @error('empresa_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <flux:input
                    wire:model="direccion"
                    label="Direccion"
                    placeholder="Se obtiene automaticamente del mapa"
                    :error="$errors->first('direccion')"
                />
            </div>

            <div class="mt-4">
                <flux:checkbox wire:model="status" label="Sucursal activa" />
            </div>
        </div>

        {{-- Mapa de Ubicacion --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-900 mb-1">Ubicacion en el Mapa</h3>
            <p class="text-xs text-gray-500 mb-4">Haz clic en el mapa para cambiar la ubicacion de la sucursal. Se actualizara la direccion automaticamente.</p>

            <div wire:ignore id="map" class="z-0 h-[400px] w-full rounded-xl border border-gray-200"></div>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input wire:model="latitud" label="Latitud" placeholder="Ej: 10.4806" type="number" step="any" readonly :error="$errors->first('latitud')" />
                <flux:input wire:model="longitud" label="Longitud" placeholder="Ej: -66.9036" type="number" step="any" readonly :error="$errors->first('longitud')" />
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.sucursales') }}" wire:navigate>
                <flux:button variant="ghost" type="button">Cancelar</flux:button>
            </a>
            <flux:button wire:click="save" class="!bg-amber-500 hover:!bg-amber-600">
                <iconify-icon icon="heroicons:check-circle" class="h-4 w-4"></iconify-icon>
                Guardar Cambios
            </flux:button>
        </div>
    </div>

    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let mapInstance = null;

        function initMap() {
            if (mapInstance) {
                mapInstance.remove();
                mapInstance = null;
            }

            const existingLat = @json($latitud);
            const existingLng = @json($longitud);
            const hasCoords = existingLat !== null && existingLng !== null;

            const centerLat = hasCoords ? existingLat : 10.4806;
            const centerLng = hasCoords ? existingLng : -66.9036;
            const zoom = hasCoords ? 16 : 12;

            mapInstance = L.map('map').setView([centerLat, centerLng], zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(mapInstance);

            let marker = null;

            if (hasCoords) {
                marker = L.marker([existingLat, existingLng]).addTo(mapInstance);
                @if($direccion)
                    marker.bindPopup(@json($direccion)).openPopup();
                @endif
            }

            async function reverseGeocode(lat, lng) {
                try {
                    const response = await fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
                    );
                    if (response.ok) {
                        const data = await response.json();
                        return data.display_name || '';
                    }
                } catch (e) {
                    console.warn('Reverse geocoding failed:', e);
                }
                return '';
            }

            mapInstance.on('click', async function (e) {
                const { lat, lng } = e.latlng;

                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng).addTo(mapInstance);
                }

                @this.set('latitud', parseFloat(lat.toFixed(8)));
                @this.set('longitud', parseFloat(lng.toFixed(8)));

                const address = await reverseGeocode(lat, lng);
                if (address) {
                    @this.set('direccion', address);
                    marker.bindPopup(address).openPopup();
                }
            });

            setTimeout(() => mapInstance.invalidateSize(), 300);
        }

        document.addEventListener('DOMContentLoaded', initMap);
        document.addEventListener('livewire:navigated', initMap);
    </script>
</div>
