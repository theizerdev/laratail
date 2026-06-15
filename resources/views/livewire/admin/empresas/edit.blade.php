<div>
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Editar Empresa: {{ $empresa->razon_social }}</h2>
            <p class="mt-1 text-sm text-gray-500">Modifica los datos de la empresa y su ubicacion.</p>
        </div>
        <a href="{{ route('admin.empresas') }}" wire:navigate>
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
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Datos Principales</h3>

            {{-- Logo Upload --}}
            <div class="mb-4">
                <flux:label>Logo / Favicon</flux:label>
                <p class="text-xs text-gray-400 mb-2">Sube una imagen para el logo de la empresa (PNG, JPG, SVG o ICO, max 2MB).</p>
                <div class="flex items-center gap-4">
                    {{-- Preview --}}
                    <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-gray-200 bg-gray-50">
                        @if ($logo)
                            <img src="{{ $logo->temporaryUrl() }}" alt="Preview" class="h-full w-full object-contain p-1" />
                        @elseif ($existingLogo)
                            <img src="{{ asset('storage/' . $existingLogo) }}" alt="Logo actual" class="h-full w-full object-contain p-1" />
                        @else
                            <iconify-icon icon="heroicons:photo" class="h-8 w-8 text-gray-300"></iconify-icon>
                        @endif
                    </div>
                    {{-- Upload --}}
                    <div>
                        <input type="file" wire:model="logo" accept="image/png,image/jpeg,image/svg+xml,image/x-icon" class="block w-full text-xs text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-indigo-600 hover:file:bg-indigo-100" />
                        @if ($logo)
                            <button wire:click="$set('logo', null)" class="mt-1 text-xs text-red-500 hover:text-red-700">
                                <iconify-icon icon="heroicons:x-mark" class="h-3 w-3 inline"></iconify-icon> Quitar nuevo
                            </button>
                        @elseif ($existingLogo)
                            <button wire:click="removeLogo" wire:confirm="Estas seguro de eliminar el logo actual?" class="mt-1 text-xs text-red-500 hover:text-red-700">
                                <iconify-icon icon="heroicons:trash" class="h-3 w-3 inline"></iconify-icon> Eliminar logo
                            </button>
                        @endif
                        <div wire:loading wire:target="logo" class="mt-1 text-xs text-indigo-500">
                            <iconify-icon icon="heroicons:arrow-path" class="h-3 w-3 inline animate-spin"></iconify-icon> Subiendo...
                        </div>
                        @error('logo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input
                    wire:model="razon_social"
                    label="Razon Social"
                    placeholder="Ej: Mi Empresa C.A."
                    :error="$errors->first('razon_social')"
                    required
                />
                <flux:input
                    wire:model="documento"
                    label="Documento / RIF"
                    placeholder="Ej: J-12345678-9"
                    :error="$errors->first('documento')"
                    required
                />
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <flux:input
                    wire:model="telefono"
                    label="Telefono"
                    placeholder="+58 212 1234567"
                    :error="$errors->first('telefono')"
                />
                <flux:input
                    wire:model="email"
                    label="Email"
                    type="email"
                    placeholder="empresa@ejemplo.com"
                    :error="$errors->first('email')"
                />
                <flux:input
                    wire:model="representante_legal"
                    label="Representante Legal"
                    placeholder="Nombre completo"
                    :error="$errors->first('representante_legal')"
                />
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>Pais</flux:label>
                    <select
                        wire:model.live="pais_id"
                        class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                    >
                        <option value="">Sin pais asignado</option>
                        @foreach ($paises as $pais)
                            <option value="{{ $pais->id }}">{{ $pais->nombre }} ({{ $pais->codigo_iso2 }})</option>
                        @endforeach
                    </select>
                </div>
                <flux:input
                    wire:model="direccion"
                    label="Direccion"
                    placeholder="Se obtiene automaticamente del mapa"
                    :error="$errors->first('direccion')"
                />
            </div>

            <div class="mt-4">
                <flux:checkbox wire:model="status" label="Empresa activa" />
            </div>
        </div>

        {{-- Mapa de Ubicacion --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-900 mb-1">Ubicacion en el Mapa</h3>
            <p class="text-xs text-gray-500 mb-4">Haz clic en el mapa para cambiar la ubicacion de la empresa. Se actualizara la direccion automaticamente.</p>

            <div wire:ignore id="map" class="z-0 h-[400px] w-full rounded-xl border border-gray-200"></div>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input
                    wire:model="latitud"
                    label="Latitud"
                    placeholder="Ej: 10.4806"
                    type="number"
                    step="any"
                    readonly
                    :error="$errors->first('latitud')"
                />
                <flux:input
                    wire:model="longitud"
                    label="Longitud"
                    placeholder="Ej: -66.9036"
                    type="number"
                    step="any"
                    readonly
                    :error="$errors->first('longitud')"
                />
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.empresas') }}" wire:navigate>
                <flux:button variant="ghost" type="button">Cancelar</flux:button>
            </a>
            <flux:button wire:click="save" class="!bg-indigo-500 hover:!bg-indigo-600">
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
            // Destroy previous instance if any
            if (mapInstance) {
                mapInstance.remove();
                mapInstance = null;
            }

            // Existing coordinates or default (Caracas)
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

            // Place existing marker
            if (hasCoords) {
                marker = L.marker([existingLat, existingLng]).addTo(mapInstance);
                @if($direccion)
                    marker.bindPopup(@json($direccion)).openPopup();
                @endif
            }

            // Reverse geocoding using Nominatim
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

            // Handle map click
            mapInstance.on('click', async function (e) {
                const { lat, lng } = e.latlng;

                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng).addTo(mapInstance);
                }

                // Update Livewire properties
                @this.set('latitud', parseFloat(lat.toFixed(8)));
                @this.set('longitud', parseFloat(lng.toFixed(8)));

                // Reverse geocode for address
                const address = await reverseGeocode(lat, lng);
                if (address) {
                    @this.set('direccion', address);
                    marker.bindPopup(address).openPopup();
                }
            });

            // Fix map rendering after Livewire navigation
            setTimeout(() => mapInstance.invalidateSize(), 300);

            // Listen for pais-selected event from Livewire
            Livewire.on('pais-selected', (params) => {
                const lat = params.lat || params[0]?.lat;
                const lng = params.lng || params[0]?.lng;
                if (lat && lng && mapInstance) {
                    mapInstance.flyTo([lat, lng], 6, { duration: 1.5 });
                }
            });
        }

        // Init on first load
        document.addEventListener('DOMContentLoaded', initMap);
        // Re-init on Livewire SPA navigation
        document.addEventListener('livewire:navigated', initMap);
    </script>
</div>
