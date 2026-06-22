import { useState, useEffect, useRef } from 'react';
import { useNavigate, Link, useParams } from 'react-router-dom';
import axios from '@/lib/axios';
import PageMeta from '@/components/PageMeta';
import { LuBuilding2, LuSave, LuX, LuImagePlus } from 'react-icons/lu';
import ModuleHeader from '@/components/ui/ModuleHeader';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
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

// Component to recenter map when position changes from API
const RecenterAutomatically = ({ lat, lng }: { lat: number, lng: number }) => {
  const map = useMap();
  useEffect(() => {
    map.setView([lat, lng]);
  }, [lat, lng, map]);
  return null;
}

const LocationMarker = ({ position, setPosition, setAddress }: any) => {
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

export default function EmpresaEdit() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(true);
  const [errors, setErrors] = useState<any>({});

  const [formData, setFormData] = useState({
    razon_social: '',
    documento: '',
    direccion: '',
    latitud: null as number | null,
    longitud: null as number | null,
    representante_legal: '',
    telefono: '',
    email: '',
    pais_id: '' as string,
    status: true
  });

  const [position, setPosition] = useState<[number, number] | null>(null);
  const defaultCenter: [number, number] = [10.4806, -66.9036];

  // Paises state
  const [paises, setPaises] = useState<any[]>([]);

  // Logo drag & drop state
  const [logoFile, setLogoFile] = useState<File | null>(null);
  const [logoPreview, setLogoPreview] = useState<string | null>(null);
  const [isDragging, setIsDragging] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    const fetchEmpresa = async () => {
      try {
        const res = await axios.get(`/api/admin/configuracion/empresas/${id}`);
        const data = res.data.data;
        setFormData({
          razon_social: data.razon_social || '',
          documento: data.documento || '',
          direccion: data.direccion || '',
          latitud: data.latitud,
          longitud: data.longitud,
          representante_legal: data.representante_legal || '',
          telefono: data.telefono || '',
          email: data.email || '',
          pais_id: data.pais_id ? String(data.pais_id) : '',
          status: !!data.status
        });

        // Set existing logo preview
        if (data.logo_url) {
          setLogoPreview(data.logo_url);
        }

        if (data.latitud && data.longitud) {
          setPosition([parseFloat(data.latitud), parseFloat(data.longitud)]);
        }
      } catch (error) {
        console.error('Error fetching empresa:', error);
        navigate('/admin/configuracion/empresas');
      } finally {
        setFetching(false);
      }
    };
    fetchEmpresa();
  }, [id, navigate]);

  // Fetch paises
  useEffect(() => {
    const fetchPaises = async () => {
      try {
        const res = await axios.get('/api/admin/configuracion/paises');
        setPaises(res.data);
      } catch (err) {
        console.error('Error fetching paises:', err);
      }
    };
    fetchPaises();
  }, []);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value, type } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? (e.target as HTMLInputElement).checked : value
    }));
    if (errors[name]) setErrors({ ...errors, [name]: null });
  };

  const handleAddressChange = (address: string) => {
    setFormData(prev => ({ ...prev, direccion: address }));
    if (errors.direccion) setErrors({ ...errors, direccion: null });
  };

  useEffect(() => {
    if (position) {
      setFormData(prev => ({
        ...prev,
        latitud: position[0],
        longitud: position[1]
      }));
    }
  }, [position]);

  // Logo handlers
  const handleLogoFile = (file: File) => {
    if (!file.type.startsWith('image/')) {
      alert('El archivo debe ser una imagen.');
      return;
    }
    if (file.size > 2 * 1024 * 1024) {
      alert('La imagen no debe superar los 2 MB.');
      return;
    }
    setLogoFile(file);
    const reader = new FileReader();
    reader.onload = () => setLogoPreview(reader.result as string);
    reader.readAsDataURL(file);
    if (errors.logo) setErrors({ ...errors, logo: null });
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(true);
  };

  const handleDragLeave = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(false);
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(false);
    const file = e.dataTransfer.files?.[0];
    if (file) handleLogoFile(file);
  };

  const handleFileInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) handleLogoFile(file);
  };

  const removeLogo = () => {
    setLogoFile(null);
    setLogoPreview(null);
    if (fileInputRef.current) fileInputRef.current.value = '';
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});

    try {
      const submitData = new FormData();
      Object.entries(formData).forEach(([key, value]) => {
        if (value !== null && value !== undefined) {
          if (typeof value === 'boolean') {
            submitData.append(key, value ? '1' : '0');
          } else {
            submitData.append(key, String(value));
          }
        }
      });
      if (logoFile) {
        submitData.append('logo', logoFile);
      }

      await axios.post(`/api/admin/configuracion/empresas/${id}?_method=PUT`, submitData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      navigate('/admin/configuracion/empresas');
    } catch (error: any) {
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors);
      } else {
        alert('OcurriÃ³ un error al actualizar la empresa.');
      }
    } finally {
      setLoading(false);
    }
  };

  if (fetching) {
    return <div className="p-8 text-center text-gray-500">Cargando datos de la empresa...</div>;
  }

  return (
    <>
      <PageMeta title="Editar Empresa" />
      <main className="space-y-6">

        <ModuleHeader
          title="Editar Empresa"
          description={`Modificando datos de: ${formData.razon_social}`}
          icon={<LuBuilding2 className="size-8" />}
          breadcrumbs={[
            { label: 'ConfiguraciÃ³n', active: false },
            { label: 'Empresas', active: false },
            { label: 'Editar', active: true }
          ]}
        />

        <Card className="p-6 md:p-8 w-full border-gray-100 shadow-sm">
          <form onSubmit={handleSubmit} className="space-y-6">

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">

              {/* Logotipo â€” drag & drop */}
              <div className="md:col-span-2">
                <label className="block text-sm font-semibold text-gray-700 mb-1.5">Logotipo</label>
                <div
                  className={`relative border-2 border-dashed rounded-xl p-6 flex flex-col items-center justify-center min-h-[180px] cursor-pointer transition-colors ${isDragging
                      ? 'border-indigo-500 bg-indigo-50'
                      : 'border-gray-300 hover:border-indigo-400 hover:bg-gray-50'
                    }`}
                  onDragOver={handleDragOver}
                  onDragLeave={handleDragLeave}
                  onDrop={handleDrop}
                  onClick={() => fileInputRef.current?.click()}
                >
                  <input
                    ref={fileInputRef}
                    type="file"
                    accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
                    className="hidden"
                    onChange={handleFileInputChange}
                  />

                  {logoPreview ? (
                    <div className="relative flex flex-col items-center gap-3">
                      <img
                        src={logoPreview}
                        alt="Vista previa del logotipo"
                        className="max-h-32 max-w-xs object-contain rounded-lg border border-gray-200 shadow-sm"
                      />
                      <p className="text-xs text-gray-500">
                        {logoFile ? logoFile.name : 'Logotipo actual'}
                      </p>
                      <button
                        type="button"
                        onClick={(e) => { e.stopPropagation(); removeLogo(); }}
                        className="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 shadow-md transition-colors"
                        title="Eliminar logotipo"
                      >
                        <LuX className="size-3.5" />
                      </button>
                    </div>
                  ) : (
                    <div className="flex flex-col items-center gap-2 text-gray-400">
                      <LuImagePlus className="size-10" />
                      <p className="text-sm font-medium text-gray-500">
                        <span className="text-indigo-600 font-semibold">Haz clic para seleccionar</span> o arrastra una imagen aquÃ­
                      </p>
                      <p className="text-xs text-gray-400">PNG, JPG, WEBP o SVG â€” MÃ¡x. 2 MB</p>
                    </div>
                  )}
                </div>
                {errors.logo && <p className="mt-1 text-sm text-red-600">{errors.logo[0]}</p>}
              </div>

              {/* RazÃ³n Social */}
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1.5">RazÃ³n Social <span className="text-red-500">*</span></label>
                <Input
                  type="text"
                  name="razon_social"
                  value={formData.razon_social}
                  onChange={handleChange}
                  className={errors.razon_social ? 'border-red-300 focus-visible:ring-red-500' : ''}
                  placeholder="Ej: Mi Empresa S.A."
                />
                {errors.razon_social && <p className="mt-1 text-sm text-red-600">{errors.razon_social[0]}</p>}
              </div>

              {/* Documento */}
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1.5">Documento (RIF/NIT) <span className="text-red-500">*</span></label>
                <Input
                  type="text"
                  name="documento"
                  value={formData.documento}
                  onChange={handleChange}
                  className={errors.documento ? 'border-red-300 focus-visible:ring-red-500' : ''}
                  placeholder="Ej: J-12345678-9"
                />
                {errors.documento && <p className="mt-1 text-sm text-red-600">{errors.documento[0]}</p>}
              </div>

              {/* PaÃ­s */}
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1.5">PaÃ­s</label>
                <div className="relative">
                  <select
                    name="pais_id"
                    value={formData.pais_id}
                    onChange={handleChange}
                    className={`w-full border ${errors.pais_id ? 'border-red-300 focus:ring-red-500' : 'border-gray-300 focus:ring-indigo-500'} rounded-lg px-4 py-2.5 focus:ring-2 focus:outline-none appearance-none bg-white cursor-pointer`}
                  >
                    <option value="">â€” Seleccionar paÃ­s â€”</option>
                    {paises.map((pais) => (
                      <option key={pais.id} value={pais.id}>
                        {pais.nombre}
                      </option>
                    ))}
                  </select>
                  <svg className="absolute right-3 top-1/2 -translate-y-1/2 size-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
                {errors.pais_id && <p className="mt-1 text-sm text-red-600">{errors.pais_id[0]}</p>}
              </div>

              {/* Representante Legal */}
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1.5">Representante Legal</label>
                <Input
                  type="text"
                  name="representante_legal"
                  value={formData.representante_legal}
                  onChange={handleChange}
                  placeholder="Nombre del representante"
                />
              </div>

              {/* TelÃ©fono */}
              <div>
                <label className="block text-sm font-semibold text-gray-700 mb-1.5">TelÃ©fono</label>
                <Input
                  type="text"
                  name="telefono"
                  value={formData.telefono}
                  onChange={handleChange}
                  placeholder="Ej: +58 412 1234567"
                />
              </div>

              {/* Email */}
              <div className="md:col-span-2">
                <label className="block text-sm font-semibold text-gray-700 mb-1.5">Correo ElectrÃ³nico</label>
                <Input
                  type="email"
                  name="email"
                  value={formData.email}
                  onChange={handleChange}
                  className={errors.email ? 'border-red-300 focus-visible:ring-red-500' : ''}
                  placeholder="contacto@empresa.com"
                />
                {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email[0]}</p>}
              </div>

              {/* DirecciÃ³n */}
              <div className="md:col-span-2">
                <label className="block text-sm font-semibold text-gray-700 mb-1.5">DirecciÃ³n</label>
                <textarea
                  name="direccion"
                  value={formData.direccion}
                  onChange={handleChange}
                  rows={2}
                  className="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                  placeholder="Haz clic en el mapa para autocompletar o escribe la direcciÃ³n..."
                ></textarea>
              </div>

              {/* Mapa de Leaflet */}
              <div className="md:col-span-2 h-[350px] relative z-0 border border-gray-200 rounded-lg overflow-hidden">
                <MapContainer center={position || defaultCenter} zoom={13} style={{ height: '100%', width: '100%' }}>
                  <TileLayer
                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                  />
                  {position && <RecenterAutomatically lat={position[0]} lng={position[1]} />}
                  <LocationMarker position={position} setPosition={setPosition} setAddress={handleAddressChange} />
                </MapContainer>
                <div className="absolute top-2 right-2 bg-white px-3 py-1.5 rounded-md shadow-md text-xs font-semibold z-[1000] text-gray-600 pointer-events-none">
                  Haz clic para fijar la ubicaciÃ³n
                </div>
              </div>

              {/* Estado */}
              <div className="md:col-span-2 flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-100">
                <div>
                  <h4 className="text-sm font-semibold text-gray-800">Estado de la Empresa</h4>
                  <p className="text-xs text-gray-500 mt-0.5">Activar o desactivar el acceso de esta empresa al sistema.</p>
                </div>
                <label className="relative inline-flex items-center cursor-pointer select-none">
                  <input
                    type="checkbox"
                    name="status"
                    className="sr-only peer"
                    checked={formData.status}
                    onChange={handleChange}
                  />
                  <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0f3d4c]"></div>
                </label>
              </div>
            </div>

            <div className="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
              <Button
                type="button"
                variant="outline"
                onClick={() => navigate('/admin/configuracion/empresas')}
                className="gap-2"
              >
                <LuX className="size-4" /> Cancelar
              </Button>
              <Button
                type="submit"
                disabled={loading}
                className="gap-2 bg-[#0f3d4c] hover:bg-[#0c313d]"
              >
                {loading ? (
                  <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                ) : (
                  <LuSave className="size-4" />
                )}
                {loading ? 'Guardando...' : 'Guardar Cambios'}
              </Button>
            </div>

          </form>
        </Card>
      </main>
    </>
  );
}
