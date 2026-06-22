import { useState, useEffect } from 'react';
import axios from '@/lib/axios';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Camera, Fingerprint } from 'lucide-react';
import { WebcamCapture } from '@/components/WebcamCapture';

interface EstudianteModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
  estudiante: any | null;
}

interface EstudianteFormData {
  nombre: string;
  apellido: string;
  dni: string;
  grado: string;
  seccion: string;
  codigo_acceso: string;
  fecha_nacimiento: string;
  edad: number | null;
  genero: string;
  email: string;
  telefono: string;
  foto_base64?: string;
  huella_template?: string;
  representantes: {
    id?: number;
    nombre: string;
    dni: string;
    telefono: string;
    telefono_secundario: string;
    email: string;
    relacion: string;
  }[];
}

export default function EstudianteModal({ isOpen, onClose, onSuccess, estudiante }: EstudianteModalProps) {
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState<EstudianteFormData>({
    nombre: '',
    apellido: '',
    dni: '',
    fecha_nacimiento: '',
    genero: '',
    grado: '',
    seccion: '',
    codigo_acceso: '',
    email: '',
    telefono: '',
    edad: null,
    foto_base64: '',
    huella_template: '',
    representantes: [{
      nombre: '',
      dni: '',
      telefono: '',
      telefono_secundario: '',
      email: '',
      relacion: 'Madre/Padre'
    }]
  });
  
  const [calculatedAge, setCalculatedAge] = useState<number | null>(null);

  useEffect(() => {
    if (isOpen) {
      if (estudiante) {
        setFormData({
          nombre: estudiante.nombre || '',
          apellido: estudiante.apellido || '',
          dni: estudiante.dni || '',
          fecha_nacimiento: estudiante.fecha_nacimiento || '',
          genero: estudiante.genero || '',
          grado: estudiante.grado || '',
          seccion: estudiante.seccion || '',
          codigo_acceso: estudiante.codigo_acceso || '',
          email: estudiante.email || '',
          telefono: estudiante.telefono || '',
          representantes: estudiante.representantes?.length > 0 ? estudiante.representantes : [{
            nombre: '',
            dni: '',
            telefono: '',
            telefono_secundario: '',
            email: '',
            relacion: 'Madre/Padre'
          }]
        });
        if (estudiante.fecha_nacimiento) {
          calculateAge(estudiante.fecha_nacimiento);
        } else {
          setCalculatedAge(estudiante.edad || null);
        }
      } else {
        setFormData({
          nombre: '',
          apellido: '',
          dni: '',
          fecha_nacimiento: '',
          genero: '',
          grado: '',
          seccion: '',
          codigo_acceso: '',
          email: '',
          telefono: '',
          representantes: [{
            nombre: '',
            dni: '',
            telefono: '',
            telefono_secundario: '',
            email: '',
            relacion: 'Madre/Padre'
          }]
        });
        setCalculatedAge(null);
      }
    }
  }, [isOpen, estudiante]);

  const calculateAge = (dob: string) => {
    if (!dob) {
      setCalculatedAge(null);
      return;
    }
    const birthDate = new Date(dob);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
      age--;
    }
    setCalculatedAge(age);
  };

  const handleDateChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const val = e.target.value;
    setFormData({ ...formData, fecha_nacimiento: val });
    calculateAge(val);
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleRepChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const reps = [...formData.representantes];
    reps[0] = { ...reps[0], [e.target.name]: e.target.value };
    setFormData({ ...formData, representantes: reps });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      // Prepare payload based on age
      const payload: any = { ...formData };
      
      // Calculate real age and send it just in case
      if (calculatedAge !== null) {
        payload.edad = calculatedAge;
      }

      if (calculatedAge !== null && calculatedAge < 18) {
        // Require representative, clear student email/phone (optional)
      } else {
        // Adult student, do not send empty representative array so it doesn't try to create a blank one
        payload.representantes = [];
      }

      if (estudiante?.id) {
        await axios.put(`/api/estudiantes/${estudiante.id}`, payload);
      } else {
        await axios.post('/api/estudiantes', payload);
      }
      
      onSuccess();
      onClose();
    } catch (error: any) {
      console.error(error);
      alert('Error guardando estudiante. Revisa la consola o los datos ingresados.');
    } finally {
      setLoading(false);
    }
  };

  const generateRandomCode = () => {
    const randomCode = Math.floor(100000 + Math.random() * 900000).toString();
    setFormData({ ...formData, codigo_acceso: randomCode });
  };

  const handlePhotoCapture = (base64Image: string) => {
    setFormData((prev) => ({ ...prev, foto_base64: base64Image }));
  };

  const handleFingerprintCapture = () => {
    // Simulador de huella
    alert("Iniciando captura de huella... Asegúrese de que el lector esté conectado.");
    setTimeout(() => {
      setFormData((prev) => ({ ...prev, huella_template: 'huella_mock_' + Math.random().toString(36).substr(2, 9) }));
      alert("Huella capturada correctamente (Modo Simulación).");
    }, 1500);
  };

  const isMinor = calculatedAge !== null && calculatedAge < 18;
  const isAdult = calculatedAge !== null && calculatedAge >= 18;

  return (
    <Dialog open={isOpen} onOpenChange={(open) => !open && onClose()}>
      <DialogContent className="sm:max-w-4xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>{estudiante ? 'Editar Estudiante' : 'Nuevo Estudiante'}</DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="space-y-6 mt-4">
          
          {/* Estudiante Info */}
          <div>
            <h3 className="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Datos del Estudiante</h3>
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium leading-none text-gray-700">Nombres</label>
                <Input name="nombre" value={formData.nombre} onChange={handleChange} required />
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium leading-none text-gray-700">Apellidos</label>
                <Input name="apellido" value={formData.apellido} onChange={handleChange} required />
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium leading-none text-gray-700">Documento de Identidad (Opcional si es menor)</label>
                <Input name="dni" value={formData.dni} onChange={handleChange} />
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium leading-none text-gray-700">Fecha de Nacimiento</label>
                <Input type="date" name="fecha_nacimiento" value={formData.fecha_nacimiento} onChange={handleDateChange} required />
                {calculatedAge !== null && (
                  <p className="text-xs text-gray-500 mt-1">Edad calculada: {calculatedAge} años</p>
                )}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium leading-none text-gray-700">Género</label>
                <select 
                  name="genero" 
                  value={formData.genero} 
                  onChange={handleChange}
                  className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  <option value="">Seleccione...</option>
                  <option value="Masculino">Masculino</option>
                  <option value="Femenino">Femenino</option>
                  <option value="Otro">Otro</option>
                </select>
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium leading-none text-gray-700">Grado</label>
                <Input name="grado" value={formData.grado} onChange={handleChange} placeholder="Ej. 1er Año" />
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium leading-none text-gray-700">Sección</label>
                <Input name="seccion" value={formData.seccion} onChange={handleChange} placeholder="Ej. A" />
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium leading-none text-gray-700">Código Acceso (Opcional)</label>
                <div className="flex gap-2">
                  <Input name="codigo_acceso" value={formData.codigo_acceso} onChange={handleChange} placeholder="Ej. 123456" />
                  <Button type="button" variant="outline" onClick={generateRandomCode} title="Generar código aleatorio">
                    Generar
                  </Button>
                </div>
              </div>
            </div>

            <div className="border-t pt-4 mt-6">
              <h3 className="font-semibold text-lg mb-4 text-gray-800">Biometría y Fotografía</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                  <label className="text-sm font-medium leading-none text-gray-700 mb-2 block">Fotografía del Estudiante</label>
                  <WebcamCapture onCapture={handlePhotoCapture} currentPhoto={estudiante?.foto_url} />
                </div>
                <div>
                  <label className="text-sm font-medium leading-none text-gray-700 mb-2 block">Huella Dactilar</label>
                  <div className="flex flex-col items-center justify-center p-8 border-2 border-dashed rounded-lg bg-gray-50 h-full max-h-64">
                    <Fingerprint className={`size-16 mb-4 ${formData.huella_template ? 'text-green-500' : 'text-gray-300'}`} />
                    <p className="text-sm text-gray-500 text-center mb-4">
                      {formData.huella_template ? 'Huella registrada correctamente.' : 'No hay huella registrada.'}
                    </p>
                    <Button type="button" variant="outline" onClick={handleFingerprintCapture}>
                      Capturar Huella
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Adult Contact Info */}
          {isAdult && (
            <div>
              <h3 className="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Contacto del Estudiante (Mayor de edad)</h3>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-1">
                  <label className="text-sm font-medium leading-none text-gray-700">Email</label>
                  <Input type="email" name="email" value={formData.email} onChange={handleChange} required />
                </div>
                <div className="space-y-1">
                  <label className="text-sm font-medium leading-none text-gray-700">Teléfono</label>
                  <Input name="telefono" value={formData.telefono} onChange={handleChange} required />
                </div>
              </div>
            </div>
          )}

          {/* Representative Info */}
          {isMinor && (
            <div>
              <h3 className="text-lg font-medium text-blue-700 border-b pb-2 mb-4">Datos del Representante (Obligatorio)</h3>
              <div className="bg-blue-50/50 p-4 rounded-lg border border-blue-100 grid grid-cols-2 gap-4">
                <div className="space-y-1">
                  <label className="text-sm font-medium leading-none text-gray-700">Nombre Completo</label>
                  <Input name="nombre" value={formData.representantes[0].nombre} onChange={handleRepChange} required />
                </div>
                <div className="space-y-1">
                  <label className="text-sm font-medium leading-none text-gray-700">Documento de Identidad</label>
                  <Input name="dni" value={formData.representantes[0].dni} onChange={handleRepChange} required />
                </div>
                <div className="space-y-1">
                  <label className="text-sm font-medium leading-none text-gray-700">Teléfono Principal</label>
                  <Input name="telefono" value={formData.representantes[0].telefono} onChange={handleRepChange} required />
                </div>
                <div className="space-y-1">
                  <label className="text-sm font-medium leading-none text-gray-700">Teléfono Secundario</label>
                  <Input name="telefono_secundario" value={formData.representantes[0].telefono_secundario} onChange={handleRepChange} required />
                </div>
                <div className="space-y-1">
                  <label className="text-sm font-medium leading-none text-gray-700">Email</label>
                  <Input type="email" name="email" value={formData.representantes[0].email} onChange={handleRepChange} />
                </div>
                <div className="space-y-1">
                  <label className="text-sm font-medium leading-none text-gray-700">Relación</label>
                  <select 
                    name="relacion" 
                    value={formData.representantes[0].relacion} 
                    onChange={handleRepChange}
                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background"
                    required
                  >
                    <option value="Madre/Padre">Madre / Padre</option>
                    <option value="Tio/Tia">Tío / Tía</option>
                    <option value="Abuelo/Abuela">Abuelo / Abuela</option>
                    <option value="Hermano/Hermana">Hermano / Hermana</option>
                    <option value="Otro">Otro</option>
                  </select>
                </div>
              </div>
            </div>
          )}

          <DialogFooter className="pt-4">
            <Button type="button" variant="outline" onClick={onClose} disabled={loading}>
              Cancelar
            </Button>
            <Button type="submit" disabled={loading || calculatedAge === null}>
              {loading ? 'Guardando...' : 'Guardar Estudiante'}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
