import { useState, useEffect } from 'react';
import axios from '@/lib/axios';
import { LuX, LuStore } from 'react-icons/lu';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import { MapContainer, TileLayer, Marker, useMapEvents, useMap } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

// Fix for default leaflet markers in React
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

const LocationMarker = ({ position, setPosition, setAddress }: any) => {
  const map = useMap();

  useEffect(() => {
    if (position) {
      map.flyTo(position, map.getZoom());
    }
  }, [position, map]);

  useMapEvents({
    click(e) {
      const { lat, lng } = e.latlng;
      setPosition([lat, lng]);

      // Reverse geocoding with Nominatim
      fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
        .then((res) => res.json())
        .then((data) => {
          if (data && data.display_name) {
            setAddress(data.display_name);
          }
        })
        .catch(console.error);
    },
  });

  return position === null ? null : (
    <Marker position={position}></Marker>
  );
};

interface SucursalModalProps {
  isOpen: boolean;
  onClose: () => void;
  sucursal: any | null;
  onSuccess: () => void;
}

export default function SucursalModal({ isOpen, onClose, sucursal, onSuccess }: SucursalModalProps) {
  const [empresas, setEmpresas] = useState<any[]>([]);
  const [formData, setFormData] = useState({
    empresa_id: '',
    nombre: '',
    telefono: '',
    direccion: '',
    latitud: null as number | null,
    longitud: null as number | null,
    status: true,
  });

  const [errors, setErrors] = useState<any>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [mapPosition, setMapPosition] = useState<[number, number] | null>(null);

  // Default center for map if no position (can be adjusted)
  const defaultCenter: [number, number] = [4.6097, -74.0817];

  useEffect(() => {
    if (isOpen) {
      fetchEmpresas();
    }
  }, [isOpen]);

  useEffect(() => {
    if (sucursal) {
      setFormData({
        empresa_id: String(sucursal.empresa_id || ''),
        nombre: sucursal.nombre || '',
        telefono: sucursal.telefono || '',
        direccion: sucursal.direccion || '',
        latitud: sucursal.latitud ? Number(sucursal.latitud) : null,
        longitud: sucursal.longitud ? Number(sucursal.longitud) : null,
        status: sucursal.status !== false,
      });
      if (sucursal.latitud && sucursal.longitud) {
        setMapPosition([Number(sucursal.latitud), Number(sucursal.longitud)]);
      } else {
        setMapPosition(null);
      }
    } else {
      setFormData({
        empresa_id: '',
        nombre: '',
        telefono: '',
        direccion: '',
        latitud: null,
        longitud: null,
        status: true,
      });
      setMapPosition(null);
    }
    setErrors({});
  }, [sucursal, isOpen]);

  const fetchEmpresas = async () => {
    try {
      const res = await axios.get('/api/admin/configuracion/sucursales/empresas');
      setEmpresas(res.data || []);
    } catch (error) {
      console.error("Error loading empresas", error);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData({ ...formData, [name]: value });
    
    if (name === 'empresa_id' && value && !sucursal) {
      const selectedEmp = empresas.find(emp => String(emp.id) === String(value));
      if (selectedEmp && selectedEmp.latitud && selectedEmp.longitud) {
        const lat = Number(selectedEmp.latitud);
        const lng = Number(selectedEmp.longitud);
        setMapPosition([lat, lng]);
        setFormData(prev => ({ ...prev, empresa_id: value, latitud: lat, longitud: lng }));
      }
    }

    if (errors[name]) {
      setErrors({ ...errors, [name]: null });
    }
  };

  const handlePositionChange = (pos: [number, number]) => {
    setMapPosition(pos);
    setFormData(prev => ({
      ...prev,
      latitud: pos[0],
      longitud: pos[1]
    }));
  };

  const handleAddressChange = (address: string) => {
    setFormData(prev => ({
      ...prev,
      direccion: address
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setErrors({});

    try {
      if (sucursal) {
        await axios.put(`/api/admin/configuracion/sucursales/${sucursal.id}`, formData);
      } else {
        await axios.post('/api/admin/configuracion/sucursales', formData);
      }
      onSuccess();
      onClose();
    } catch (error: any) {
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors);
      } else {
        alert('Ocurrió un error inesperado al guardar la sucursal.');
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div className="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-in zoom-in-95 fade-in duration-200">
        
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
          <div className="flex items-center gap-3">
            <div className="p-2 bg-blue-50 text-[#0f3d4c] rounded-lg">
              <LuStore className="size-5" />
            </div>
            <div>
              <h2 className="text-lg font-bold text-gray-800">
                {sucursal ? 'Editar Sucursal' : 'Nueva Sucursal'}
              </h2>
              <p className="text-xs text-gray-500">
                {sucursal ? 'Actualiza los datos de la sucursal seleccionada.' : 'Completa el formulario para crear una nueva sucursal.'}
              </p>
            </div>
          </div>
          <button 
            onClick={onClose}
            className="p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 rounded-full transition-colors"
          >
            <LuX className="size-5" />
          </button>
        </div>

        {/* Form Body */}
        <div className="flex-1 overflow-y-auto p-6">
          <form id="sucursal-form" onSubmit={handleSubmit} className="space-y-6">
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {/* Left Column - Form Fields */}
              <div className="space-y-4">
                
                {/* Empresa Select */}
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-1.5">Empresa <span className="text-red-500">*</span></label>
                  <select 
                    name="empresa_id"
                    value={formData.empresa_id}
                    onChange={handleChange}
                    className="w-full flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    required
                  >
                    <option value="">Selecciona una empresa</option>
                    {empresas.map(emp => (
                      <option key={emp.id} value={emp.id}>{emp.razon_social}</option>
                    ))}
                  </select>
                  {errors.empresa_id && <p className="text-xs text-red-500 mt-1">{errors.empresa_id[0]}</p>}
                </div>

                {/* Nombre */}
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-1.5">Nombre de la Sucursal <span className="text-red-500">*</span></label>
                  <Input 
                    name="nombre" 
                    value={formData.nombre} 
                    onChange={handleChange} 
                    placeholder="Ej. Sucursal Centro"
                    className={errors.nombre ? 'border-red-500 focus-visible:ring-red-500' : ''}
                    required
                  />
                  {errors.nombre && <p className="text-xs text-red-500 mt-1">{errors.nombre[0]}</p>}
                </div>

                {/* Teléfono */}
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-1.5">Teléfono</label>
                  <Input 
                    name="telefono" 
                    value={formData.telefono} 
                    onChange={handleChange} 
                    placeholder="Ej. +1 234 567 8900"
                    className={errors.telefono ? 'border-red-500 focus-visible:ring-red-500' : ''}
                  />
                  {errors.telefono && <p className="text-xs text-red-500 mt-1">{errors.telefono[0]}</p>}
                </div>

                {/* Dirección */}
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-1.5">Dirección</label>
                  <textarea 
                    name="direccion" 
                    value={formData.direccion} 
                    onChange={handleChange} 
                    placeholder="Ej. Av. Principal 123"
                    className="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  />
                  {errors.direccion && <p className="text-xs text-red-500 mt-1">{errors.direccion[0]}</p>}
                </div>

                {/* Coordinates (Read Only) */}
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-xs font-semibold text-gray-500 mb-1">Latitud</label>
                    <Input 
                      value={formData.latitud || ''} 
                      readOnly 
                      className="bg-gray-50 text-gray-500 text-xs" 
                      placeholder="Latitud"
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-gray-500 mb-1">Longitud</label>
                    <Input 
                      value={formData.longitud || ''} 
                      readOnly 
                      className="bg-gray-50 text-gray-500 text-xs"
                      placeholder="Longitud"
                    />
                  </div>
                </div>

                {/* Status Toggle */}
                <div className="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100 mt-2">
                  <div>
                    <label className="text-sm font-bold text-gray-700 block">Estado de la Sucursal</label>
                    <p className="text-xs text-gray-500">¿Esta sucursal está activa y operativa?</p>
                  </div>
                  <Switch 
                    checked={formData.status} 
                    onCheckedChange={(checked) => setFormData({...formData, status: checked})} 
                  />
                </div>

              </div>

              {/* Right Column - Map */}
              <div className="flex flex-col h-[450px] bg-gray-50 rounded-xl overflow-hidden border border-gray-200">
                <div className="p-3 border-b border-gray-200 bg-white">
                  <h3 className="text-sm font-semibold text-gray-700">Ubicación en el Mapa</h3>
                  <p className="text-[11px] text-gray-500">Haz clic en el mapa para ubicar la sucursal y autocompletar la dirección.</p>
                </div>
                <div className="flex-1 relative">
                  <MapContainer 
                    center={mapPosition || defaultCenter} 
                    zoom={mapPosition ? 15 : 6} 
                    style={{ height: "100%", width: "100%" }}
                  >
                    <TileLayer
                      url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                      attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    />
                    <LocationMarker 
                      position={mapPosition} 
                      setPosition={handlePositionChange}
                      setAddress={handleAddressChange}
                    />
                  </MapContainer>
                </div>
              </div>

            </div>

          </form>
        </div>

        {/* Footer */}
        <div className="px-6 py-4 border-t border-gray-100 bg-gray-50/80 flex justify-end gap-3">
          <Button 
            type="button" 
            variant="outline" 
            onClick={onClose}
          >
            Cancelar
          </Button>
          <Button 
            type="submit" 
            form="sucursal-form"
            disabled={isSubmitting}
            className="bg-[#0f3d4c] hover:bg-[#0f3d4c]/90 text-white"
          >
            {isSubmitting ? 'Guardando...' : (sucursal ? 'Actualizar Sucursal' : 'Crear Sucursal')}
          </Button>
        </div>
        
      </div>
    </div>
  );
}
