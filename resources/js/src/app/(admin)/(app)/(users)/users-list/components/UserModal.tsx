import { useState, useEffect } from 'react';
import axios from '@/lib/axios';
import { LuX, LuUser } from 'react-icons/lu';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';

interface UserModalProps {
  isOpen: boolean;
  onClose: () => void;
  user: any | null;
  onSuccess: () => void;
}

export default function UserModal({ isOpen, onClose, user, onSuccess }: UserModalProps) {
  const [empresas, setEmpresas] = useState<any[]>([]);
  const [sucursales, setSucursales] = useState<any[]>([]);
  
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    status: 'activo',
    telefono: '',
    empresa_id: '',
    sucursal_id: '',
  });

  const [errors, setErrors] = useState<any>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    if (isOpen) {
      fetchEmpresas();
      fetchSucursales();
    }
  }, [isOpen]);

  useEffect(() => {
    if (user) {
      setFormData({
        name: user.name || '',
        email: user.email || '',
        password: '',
        status: user.status || 'activo',
        telefono: user.telefono || '',
        empresa_id: user.empresa_id ? String(user.empresa_id) : '',
        sucursal_id: user.sucursal_id ? String(user.sucursal_id) : '',
      });
    } else {
      setFormData({
        name: '',
        email: '',
        password: '',
        status: 'activo',
        telefono: '',
        empresa_id: '',
        sucursal_id: '',
      });
    }
    setErrors({});
  }, [user, isOpen]);

  const fetchEmpresas = async () => {
    try {
      const res = await axios.get('/api/admin/configuracion/empresas');
      setEmpresas(Array.isArray(res.data) ? res.data : []);
    } catch (err) {
      console.error('Error fetching empresas:', err);
    }
  };

  const fetchSucursales = async () => {
    try {
      const res = await axios.get('/api/admin/configuracion/sucursales');
      setSucursales(Array.isArray(res.data) ? res.data : []);
    } catch (err) {
      console.error('Error fetching sucursales:', err);
    }
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
    if (errors[name]) {
      setErrors((prev: any) => ({ ...prev, [name]: undefined }));
    }
  };

  const handleSwitchChange = (checked: boolean) => {
    setFormData(prev => ({ ...prev, status: checked ? 'activo' : 'inactivo' }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setErrors({});

    try {
      const payload: any = { ...formData };
      
      // Remove empty strings for nullable foreign keys
      if (!payload.empresa_id) payload.empresa_id = null;
      if (!payload.sucursal_id) payload.sucursal_id = null;
      if (!payload.telefono) payload.telefono = null;
      
      // In edit mode, password is not required
      if (user && !payload.password) {
        delete payload.password;
      }

      if (user) {
        await axios.put(`/api/admin/usuarios/${user.id}`, payload);
      } else {
        await axios.post('/api/admin/usuarios', payload);
      }
      onSuccess();
      onClose();
    } catch (error: any) {
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors);
      } else {
        alert('Ocurrió un error al guardar el usuario.');
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div className="bg-white dark:bg-default-50 rounded-lg shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
        {/* Header */}
        <div className="flex items-center justify-between p-4 border-b border-default-200">
          <h2 className="text-lg font-semibold flex items-center gap-2">
            <LuUser className="text-primary" />
            {user ? 'Editar Usuario' : 'Nueva Usuario'}
          </h2>
          <button 
            onClick={onClose}
            className="p-1 hover:bg-default-100 rounded-full transition-colors"
          >
            <LuX className="size-5 text-default-500" />
          </button>
        </div>

        {/* Body */}
        <div className="p-6 overflow-y-auto">
          <form id="userForm" onSubmit={handleSubmit} className="space-y-4">
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <label className="text-sm font-medium">Nombre Completo <span className="text-danger">*</span></label>
                <Input 
                  name="name" 
                  value={formData.name} 
                  onChange={handleInputChange} 
                  placeholder="Ej: Juan Perez"
                  required
                />
                {errors.name && <p className="text-xs text-danger mt-1">{errors.name[0]}</p>}
              </div>
              
              <div className="space-y-2">
                <label className="text-sm font-medium">Correo Electrónico <span className="text-danger">*</span></label>
                <Input 
                  type="email"
                  name="email" 
                  value={formData.email} 
                  onChange={handleInputChange} 
                  placeholder="Ej: juan@empresa.com"
                  required
                />
                {errors.email && <p className="text-xs text-danger mt-1">{errors.email[0]}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <label className="text-sm font-medium">
                  Contraseña {user ? '(Dejar en blanco para no cambiar)' : <span className="text-danger">*</span>}
                </label>
                <Input 
                  type="password"
                  name="password" 
                  value={formData.password} 
                  onChange={handleInputChange} 
                  placeholder="*********"
                  required={!user}
                  minLength={8}
                />
                {errors.password && <p className="text-xs text-danger mt-1">{errors.password[0]}</p>}
              </div>
              
              <div className="space-y-2">
                <label className="text-sm font-medium">Teléfono</label>
                <Input 
                  name="telefono" 
                  value={formData.telefono} 
                  onChange={handleInputChange} 
                  placeholder="Ej: +58 412 1234567"
                />
                {errors.telefono && <p className="text-xs text-danger mt-1">{errors.telefono[0]}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <label className="text-sm font-medium">Empresa</label>
                <select 
                  name="empresa_id" 
                  value={formData.empresa_id} 
                  onChange={handleInputChange}
                  className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  <option value="">-- Seleccionar Empresa --</option>
                  {empresas.map(emp => (
                    <option key={emp.id} value={emp.id}>{emp.nombre}</option>
                  ))}
                </select>
                {errors.empresa_id && <p className="text-xs text-danger mt-1">{errors.empresa_id[0]}</p>}
              </div>

              <div className="space-y-2">
                <label className="text-sm font-medium">Sucursal</label>
                <select 
                  name="sucursal_id" 
                  value={formData.sucursal_id} 
                  onChange={handleInputChange}
                  className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  <option value="">-- Seleccionar Sucursal --</option>
                  {sucursales.map(suc => (
                    <option key={suc.id} value={suc.id}>{suc.nombre}</option>
                  ))}
                </select>
                {errors.sucursal_id && <p className="text-xs text-danger mt-1">{errors.sucursal_id[0]}</p>}
              </div>
            </div>

            <div className="flex items-center justify-between p-4 bg-default-50 rounded-lg border border-default-200 mt-4">
              <div>
                <h4 className="text-sm font-medium text-default-800">Estado del Usuario</h4>
                <p className="text-xs text-default-500">Los usuarios inactivos no podrán iniciar sesión.</p>
              </div>
              <Switch 
                checked={formData.status === 'activo'}
                onCheckedChange={handleSwitchChange}
              />
            </div>
            {errors.status && <p className="text-xs text-danger mt-1">{errors.status[0]}</p>}
            
          </form>
        </div>

        {/* Footer */}
        <div className="p-4 border-t border-default-200 flex justify-end gap-2 bg-default-50/50 mt-auto">
          <Button variant="outline" type="button" onClick={onClose} disabled={isSubmitting}>
            Cancelar
          </Button>
          <Button type="submit" form="userForm" disabled={isSubmitting}>
            {isSubmitting ? 'Guardando...' : user ? 'Actualizar Usuario' : 'Crear Usuario'}
          </Button>
        </div>
      </div>
    </div>
  );
}
