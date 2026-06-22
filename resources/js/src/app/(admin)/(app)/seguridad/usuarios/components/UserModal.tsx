import { useState, useEffect } from 'react';
import axios from '@/lib/axios';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

interface UserModalProps {
  isOpen: boolean;
  onClose: () => void;
  user: any | null;
  roles: any[];
  onSuccess: () => void;
}

export default function UserModal({ isOpen, onClose, user, roles, onSuccess }: UserModalProps) {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    status: 'activo',
    telefono: '',
    empresa_id: '',
    sucursal_id: '',
    roles: [] as string[]
  });

  const [empresas, setEmpresas] = useState<any[]>([]);
  const [sucursales, setSucursales] = useState<any[]>([]);
  const [errors, setErrors] = useState<any>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    if (isOpen) {
      axios.get('/api/admin/configuracion/empresas')
        .then(res => {
          const data = res.data;
          setEmpresas(Array.isArray(data) ? data : (Array.isArray(data?.data) ? data.data : []));
        })
        .catch(console.error);

      axios.get('/api/admin/configuracion/sucursales')
        .then(res => {
          const data = res.data;
          setSucursales(Array.isArray(data) ? data : (Array.isArray(data?.data) ? data.data : []));
        })
        .catch(console.error);
    }

    if (user) {
      setFormData({
        name: user.name,
        email: user.email,
        password: '',
        status: user.status || 'activo',
        telefono: user.telefono || '',
        empresa_id: user.empresa_id ? String(user.empresa_id) : '',
        sucursal_id: user.sucursal_id ? String(user.sucursal_id) : '',
        roles: user.roles ? user.roles.map((r: any) => r.name) : []
      });
    } else {
      setFormData({ name: '', email: '', password: '', status: 'activo', telefono: '', empresa_id: '', sucursal_id: '', roles: [] });
    }
    setErrors({});
  }, [user, isOpen]);

  if (!isOpen) return null;

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    if (name === 'empresa_id') {
      setFormData({ ...formData, empresa_id: value, sucursal_id: '' });
    } else {
      setFormData({ ...formData, [name]: value });
    }
  };

  const handleRoleToggle = (roleName: string) => {
    setFormData(prev => {
      if (prev.roles.includes(roleName)) {
        return { ...prev, roles: prev.roles.filter(r => r !== roleName) };
      } else {
        return { ...prev, roles: [...prev.roles, roleName] };
      }
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setErrors({});

    try {
      const payload: any = { ...formData };
      if (!payload.empresa_id) payload.empresa_id = null;
      if (!payload.sucursal_id) payload.sucursal_id = null;
      if (!payload.telefono) payload.telefono = null;
      if (user && !payload.password) delete payload.password;

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
        alert('Ocurrió un error inesperado al guardar el usuario.');
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/40 backdrop-blur-sm animate-fade-in">
      <div className="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg mx-4 flex flex-col max-h-[90vh] animate-slide-up overflow-hidden">

        <div className="flex justify-between items-center p-5 border-b dark:border-gray-700">
          <h3 className="text-xl font-semibold text-gray-800 dark:text-white">
            {user ? 'Editar Usuario' : 'Crear Usuario'}
          </h3>
          <button onClick={onClose} className="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg className="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>

        <div className="p-5 overflow-y-auto flex-1">
          <form id="user-form" onSubmit={handleSubmit} className="space-y-4">

            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre Completo</label>
              <Input
                type="text"
                name="name"
                value={formData.name}
                onChange={handleChange}
                className={errors.name ? 'border-red-500 focus-visible:ring-red-500' : ''}
                required
              />
              {errors.name && <p className="text-xs text-danger mt-1">{errors.name[0]}</p>}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Correo Electrónico</label>
              <Input
                type="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                className={errors.email ? 'border-red-500 focus-visible:ring-red-500' : ''}
                required
              />
              {errors.email && <p className="text-xs text-danger mt-1">{errors.email[0]}</p>}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Contraseña {user && <span className="text-gray-400 font-normal">(Dejar en blanco para no cambiar)</span>}
              </label>
              <Input
                type="password"
                name="password"
                value={formData.password}
                onChange={handleChange}
                className={errors.password ? 'border-red-500 focus-visible:ring-red-500' : ''}
                required={!user}
              />
              {errors.password && <p className="text-xs text-danger mt-1">{errors.password[0]}</p>}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono</label>
              <Input
                type="text"
                name="telefono"
                value={formData.telefono}
                onChange={handleChange}
                className={errors.telefono ? 'border-red-500 focus-visible:ring-red-500' : ''}
              />
              {errors.telefono && <p className="text-xs text-danger mt-1">{errors.telefono[0]}</p>}
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Empresa</label>
                <select
                  name="empresa_id"
                  value={formData.empresa_id}
                  onChange={handleChange}
                  className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  <option value="">-- Seleccionar Empresa --</option>
                  {empresas.map(emp => (
                    <option key={emp.id} value={emp.id}>{emp.razon_social}</option>
                  ))}
                </select>
                {errors.empresa_id && <p className="text-xs text-danger mt-1">{errors.empresa_id[0]}</p>}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sucursal</label>
                <select
                  name="sucursal_id"
                  value={formData.sucursal_id}
                  onChange={handleChange}
                  className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  disabled={!formData.empresa_id}
                >
                  <option value="">-- Seleccionar Sucursal --</option>
                  {sucursales
                    .filter(suc => String(suc.empresa_id) === String(formData.empresa_id))
                    .map(suc => (
                      <option key={suc.id} value={suc.id}>{suc.nombre}</option>
                    ))}
                </select>
                {errors.sucursal_id && <p className="text-xs text-danger mt-1">{errors.sucursal_id[0]}</p>}
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
              <select
                name="status"
                value={formData.status}
                onChange={handleChange}
                className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
              >
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
              </select>
              {errors.status && <p className="text-xs text-danger mt-1">{errors.status[0]}</p>}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Asignar Roles</label>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1">
                {roles.map(role => {
                  const isSelected = formData.roles.includes(role.name);
                  return (
                    <label
                      key={role.id}
                      className={`flex items-center gap-3 cursor-pointer p-3 rounded-xl border transition-all duration-200 ${isSelected
                          ? 'border-primary bg-primary/5 dark:bg-primary/10 ring-1 ring-primary'
                          : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'
                        }`}
                    >
                      <input
                        type="checkbox"
                        className="form-checkbox text-primary rounded border-gray-300 focus:ring-primary h-4 w-4"
                        checked={isSelected}
                        onChange={() => handleRoleToggle(role.name)}
                      />
                      <span className={`text-sm font-medium ${isSelected ? 'text-primary dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'}`}>
                        {role.display_name || role.name}
                      </span>
                    </label>
                  );
                })}
              </div>
              {errors.roles && <p className="text-xs text-danger mt-1">{errors.roles[0]}</p>}
            </div>

          </form>
        </div>

        <div className="p-5 border-t dark:border-gray-700 flex justify-end gap-3 bg-gray-50/50 dark:bg-gray-800/80">
          <Button type="button" variant="outline" onClick={onClose}>
            Cancelar
          </Button>
          <Button type="submit" form="user-form" disabled={isSubmitting} className="bg-[#0f3d4c] hover:bg-[#0c313d]">
            {isSubmitting ? 'Guardando...' : (user ? 'Actualizar Usuario' : 'Crear Usuario')}
          </Button>
        </div>

      </div>
    </div>
  );
}
