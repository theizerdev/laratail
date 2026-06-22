import { useState, useEffect } from 'react';
import axios from '@/lib/axios';
import PageMeta from '@/components/PageMeta';
import { useNavigate } from 'react-router-dom';
import ModuleHeader from '@/components/ui/ModuleHeader';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { 
  LuSettings, 
  LuShield, 
  LuMessagesSquare, 
  LuPhone, 
  LuHeadphones, 
  LuPackage, 
  LuStethoscope, 
  LuActivity, 
  LuBell,
  LuLaptop,
  LuChevronRight
} from 'react-icons/lu';

// Custom inline HomeIcon SVG to prevent package mismatches
const HomeIcon = ({ className }: { className?: string }) => (
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}>
    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
    <polyline points="9 22 9 12 15 12 15 22" />
  </svg>
);

// Helper to map icons by module name
const getModuleIcon = (moduleName: string) => {
  const name = (moduleName || '').toLowerCase();
  if (name.includes('configuraci')) return <LuSettings className="size-4" />;
  if (name.includes('seguridad') || name.includes('sistem')) return <LuShield className="size-4" />;
  if (name.includes('chat')) return <LuMessagesSquare className="size-4" />;
  if (name.includes('comunicaci')) return <LuPhone className="size-4" />;
  if (name.includes('helpdesk')) return <LuHeadphones className="size-4" />;
  if (name.includes('inventari')) return <LuPackage className="size-4" />;
  if (name.includes('médic') || name.includes('medic')) return <LuStethoscope className="size-4" />;
  if (name.includes('monitore')) return <LuActivity className="size-4" />;
  if (name.includes('recepci')) return <LuBell className="size-4" />;
  return <LuLaptop className="size-4" />;
};

// Help to format resource names to Spanish
const formatResourceName = (name: string) => {
  const mapping: Record<string, string> = {
    users: 'Usuarios',
    roles: 'Roles',
    permissions: 'Permisos',
    settings: 'Configuración',
    logs: 'Registros',
    reports: 'Reportes'
  };
  const lower = name.toLowerCase();
  if (mapping[lower]) return mapping[lower];
  return name.charAt(0).toUpperCase() + name.slice(1);
};

// Format permission names to read like "Action resource"
const formatPermissionLabel = (permName: string) => {
  if (permName.includes('.')) {
    const parts = permName.split('.');
    const action = parts[1];
    const resource = parts[0];
    const actionMapping: Record<string, string> = {
      view: 'Ver',
      create: 'Crear',
      edit: 'Editar',
      delete: 'Eliminar',
      access: 'Acceder a',
      activate: 'Activar',
      deactivate: 'Desactivar',
      assign: 'Asignar',
      configure: 'Configurar'
    };
    const actionLabel = actionMapping[action] || action.charAt(0).toUpperCase() + action.slice(1);
    const translatedResource = formatResourceName(resource).toLowerCase();
    return `${actionLabel} ${translatedResource}`;
  }
  return permName;
};

// Dynamic description for the sector
const getModuleDescription = (moduleName: string) => {
  const name = (moduleName || '').toLowerCase();
  if (name.includes('configuraci')) {
    return 'Configuración del sistema, empresas, usuarios y roles';
  }
  if (name.includes('seguridad')) {
    return 'Configuración de seguridad, roles, permisos y accesos de usuario';
  }
  return `Permisos y privilegios asociados al sector ${moduleName}`;
};

export default function RoleCreate() {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    name: '',
    display_name: '',
    permissions: [] as string[]
  });
  const [permissionsGrouped, setPermissionsGrouped] = useState<Record<string, any[]>>({});
  const [errors, setErrors] = useState<any>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [activeModule, setActiveModule] = useState<string>('');

  useEffect(() => {
    const fetchPermissions = async () => {
      try {
        const res = await axios.get('/api/admin/permisos');
        setPermissionsGrouped(res.data);
        const keys = Object.keys(res.data);
        if (keys.length > 0) {
          setActiveModule(keys[0]);
        }
      } catch (error) {
        console.error('Error fetching permissions', error);
      }
    };
    fetchPermissions();
  }, []);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handlePermissionToggle = (permName: string) => {
    setFormData(prev => {
      if (prev.permissions.includes(permName)) {
        return { ...prev, permissions: prev.permissions.filter(p => p !== permName) };
      } else {
        return { ...prev, permissions: [...prev.permissions, permName] };
      }
    });
  };

  // Select/Deselect all permissions in the system
  const handleSelectAllGlobal = (checked: boolean) => {
    if (checked) {
      const allPerms: string[] = [];
      Object.keys(permissionsGrouped).forEach(mod => {
        permissionsGrouped[mod].forEach(p => {
          allPerms.push(p.name);
        });
      });
      setFormData(prev => ({ ...prev, permissions: allPerms }));
    } else {
      setFormData(prev => ({ ...prev, permissions: [] }));
    }
  };

  // Select/Deselect all permissions in active sector/module
  const handleSelectAllModule = (moduleName: string, checked: boolean) => {
    const modulePerms = (permissionsGrouped[moduleName] || []).map(p => p.name);
    setFormData(prev => {
      let newPerms = [...prev.permissions];
      if (checked) {
        modulePerms.forEach(p => {
          if (!newPerms.includes(p)) newPerms.push(p);
        });
      } else {
        newPerms = newPerms.filter(p => !modulePerms.includes(p));
      }
      return { ...prev, permissions: newPerms };
    });
  };

  // Select/Deselect all permissions inside a sub-sector/resource
  const handleSelectAllResource = (resourcePerms: any[], checked: boolean) => {
    const permNames = resourcePerms.map(p => p.name);
    setFormData(prev => {
      let newPerms = [...prev.permissions];
      if (checked) {
        permNames.forEach(p => {
          if (!newPerms.includes(p)) newPerms.push(p);
        });
      } else {
        newPerms = newPerms.filter(p => !permNames.includes(p));
      }
      return { ...prev, permissions: newPerms };
    });
  };

  // Group permissions of active module by resource
  const getActiveModuleGrouped = () => {
    const perms = permissionsGrouped[activeModule] || [];
    const grouped: Record<string, any[]> = {};
    perms.forEach(perm => {
      let resource = 'Otros';
      if (perm.name.includes('.')) {
        resource = perm.name.split('.')[0];
      } else if (perm.name.includes('_')) {
        const parts = perm.name.split('_');
        resource = parts[parts.length - 1];
      }
      if (!grouped[resource]) {
        grouped[resource] = [];
      }
      grouped[resource].push(perm);
    });
    return grouped;
  };

  const getModuleSelectedCount = (moduleName: string) => {
    const perms = permissionsGrouped[moduleName] || [];
    return perms.filter(p => formData.permissions.includes(p.name)).length;
  };

  const getModuleTotalCount = (moduleName: string) => {
    return (permissionsGrouped[moduleName] || []).length;
  };

  const isAllGlobalSelected = () => {
    let total = 0;
    Object.keys(permissionsGrouped).forEach(mod => {
      total += permissionsGrouped[mod].length;
    });
    return total > 0 && formData.permissions.length === total;
  };

  const isAllModuleSelected = (moduleName: string) => {
    const perms = permissionsGrouped[moduleName] || [];
    return perms.length > 0 && perms.every(p => formData.permissions.includes(p.name));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setErrors({});

    try {
      await axios.post('/api/admin/roles', formData);
      navigate('/admin/seguridad/roles');
    } catch (error: any) {
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors);
      } else {
        alert('Error guardando el rol.');
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  const activeGrouped = getActiveModuleGrouped();

  return (
    <>
      <PageMeta title="Crear Rol" />
      <main className="p-6 bg-[#f8fafc] pb-24">
        
        {/* Module Header */}
        <ModuleHeader
          title="Crear Nuevo Rol"
          description="Define un nuevo rol y asigna los permisos por sector"
          icon={<LuShield className="size-8" />}
          breadcrumbs={[
            { label: 'Roles', href: '/admin/seguridad/roles' },
            { label: 'Crear', active: true }
          ]}
        />

        {/* Card Form */}
        <Card className="p-6 md:p-8 w-full border-gray-100 shadow-sm">

          <form onSubmit={handleSubmit} className="space-y-6">
            
            {/* Input Nombre del Rol */}
            <div className="max-w-md">
              <label className="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Nombre del Rol</label>
              <Input 
                type="text" 
                name="display_name" 
                value={formData.display_name} 
                onChange={handleChange} 
                placeholder="Ej. editor, supervisor, etc."
                className={errors.display_name ? 'border-red-500 focus-visible:ring-red-500' : ''}
                required 
              />
              <p className="text-[10px] text-gray-400 mt-1 select-none">El nombre debe ser único y descriptivo</p>
              {errors.display_name && <p className="text-xs text-red-500 mt-1">{errors.display_name[0]}</p>}
            </div>

            {/* Hidden slug generation */}
            <input 
              type="hidden" 
              name="name" 
              value={formData.display_name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '')} 
            />

            {/* Permissions Matrix */}
            <div>
              <div className="flex justify-between items-center mb-4 border-b border-gray-100 pb-2">
                <div>
                  <h4 className="text-xs font-bold text-gray-700 uppercase tracking-wider">Permisos del Rol</h4>
                  <p className="text-[10px] text-gray-400 font-light mt-0.5">Selecciona los permisos organizados por sector</p>
                </div>
                
                {/* Global Toggle */}
                <div className="flex items-center space-x-2">
                  <Switch 
                    id="global-toggle"
                    checked={isAllGlobalSelected()}
                    onCheckedChange={(checked) => handleSelectAllGlobal(checked)}
                  />
                  <label htmlFor="global-toggle" className="text-xs font-semibold text-gray-500 cursor-pointer select-none">
                    Seleccionar todos
                  </label>
                </div>
              </div>

              {/* Tabs Row */}
              <div className="flex gap-2 overflow-x-auto pb-3 mb-6 select-none no-scrollbar">
                {Object.keys(permissionsGrouped).map((moduleName) => {
                  const isActive = activeModule === moduleName;
                  const selectedCount = getModuleSelectedCount(moduleName);
                  const totalCount = getModuleTotalCount(moduleName);

                  // Colors for badge
                  let badgeClass = 'bg-gray-100 text-gray-500';
                  if (selectedCount > 0) {
                    badgeClass = selectedCount === totalCount 
                      ? 'bg-emerald-500 text-white' 
                      : 'bg-amber-500 text-white';
                  }

                  return (
                    <button
                      key={moduleName}
                      type="button"
                      onClick={() => setActiveModule(moduleName)}
                      className={`flex items-center gap-2 px-3 py-1.5 rounded-lg border text-xs font-medium whitespace-nowrap transition-all focus:outline-none ${
                        isActive
                          ? 'bg-[#0f3d4c] border-[#0f3d4c] text-white shadow-sm'
                          : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'
                      }`}
                    >
                      {getModuleIcon(moduleName)}
                      <span>{moduleName}</span>
                      <span className={`text-[9px] px-1.5 py-0.5 rounded font-bold ${badgeClass}`}>
                        {selectedCount}/{totalCount}
                      </span>
                    </button>
                  );
                })}
              </div>

              {/* Selected Module Details Card */}
              {activeModule && (
                <div className="border border-sky-100 rounded-xl overflow-hidden shadow-sm bg-white">
                  
                  {/* Active Sector Banner Header */}
                  <div className="bg-[#e0f2fe] px-5 py-4 border-b border-sky-200 flex items-center justify-between">
                    <div className="flex items-center gap-2.5 text-[#0284c7]">
                      {getModuleIcon(activeModule)}
                      <div>
                        <h4 className="text-sm font-bold capitalize">{activeModule}</h4>
                        <p className="text-[10px] text-sky-600/90 font-light mt-0.5">{getModuleDescription(activeModule)}</p>
                      </div>
                    </div>
                    
                    {/* Sector Toggle */}
                    <div className="flex items-center space-x-2 bg-white/50 px-2.5 py-1.5 rounded-lg border border-sky-200/50">
                      <Switch 
                        id="sector-toggle"
                        checked={isAllModuleSelected(activeModule)}
                        onCheckedChange={(checked) => handleSelectAllModule(activeModule, checked)}
                      />
                      <label htmlFor="sector-toggle" className="text-[10px] font-bold text-sky-800 cursor-pointer select-none">
                        Todo el sector
                      </label>
                    </div>
                  </div>

                  {/* Sub-sectors grid list */}
                  <div className="p-5 bg-sky-50/20">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                      {Object.keys(activeGrouped).map((resName) => {
                        const resPerms = activeGrouped[resName];
                        const selectedInRes = resPerms.filter(p => formData.permissions.includes(p.name)).length;
                        const totalInRes = resPerms.length;
                        const isAllResSelected = selectedInRes === totalInRes;

                        return (
                          <div key={resName} className="bg-white border border-gray-100 rounded-xl p-4 shadow-sm flex flex-col justify-between">
                            <div>
                              {/* Subsector Header */}
                              <div className="flex justify-between items-center mb-3 pb-2 border-b border-gray-50">
                                <h5 className="text-xs font-bold text-gray-700">{formatResourceName(resName)}</h5>
                                <span className={`text-[9px] font-bold px-1.5 py-0.5 rounded ${selectedInRes > 0 ? 'bg-sky-100 text-sky-800' : 'bg-gray-100 text-gray-400'}`}>
                                  {selectedInRes}/{totalInRes}
                                </span>
                              </div>

                              {/* Permission Checklist */}
                              <div className="space-y-2 max-h-48 overflow-y-auto no-scrollbar">
                                {/* Toggle all inside resource */}
                                <label className="flex items-center gap-2 cursor-pointer select-none hover:bg-gray-50/50 p-1.5 rounded transition-colors border border-dashed border-gray-100 mb-1">
                                  <Checkbox 
                                    checked={isAllResSelected}
                                    onCheckedChange={(checked) => handleSelectAllResource(resPerms, checked as boolean)}
                                  />
                                  <span className="text-xs font-bold text-gray-700">Seleccionar todos</span>
                                </label>

                                {resPerms.map(perm => (
                                  <label key={perm.id} className="flex items-center gap-2 cursor-pointer select-none hover:bg-gray-50/50 p-1 rounded transition-colors">
                                    <Checkbox 
                                      checked={formData.permissions.includes(perm.name)}
                                      onCheckedChange={() => handlePermissionToggle(perm.name)}
                                    />
                                    <span className="text-xs text-gray-600 font-light">{formatPermissionLabel(perm.name)}</span>
                                  </label>
                                ))}
                              </div>
                            </div>
                          </div>
                        );
                      })}
                    </div>
                  </div>

                </div>
              )}
              {errors.permissions && <p className="text-xs text-red-500 mt-2">{errors.permissions[0]}</p>}
            </div>

            {/* Bottom Actions */}
            <div className="flex justify-end gap-3 pt-6 border-t border-gray-100">
              <Button 
                type="button" 
                variant="outline"
                onClick={() => navigate('/admin/seguridad/roles')} 
              >
                Cancelar
              </Button>
              <Button 
                type="submit" 
                disabled={isSubmitting} 
                className="bg-[#6366f1] hover:bg-indigo-700 text-white"
              >
                {isSubmitting ? 'Guardando...' : 'Guardar Rol'}
              </Button>
            </div>

          </form>
        </Card>
      </main>
    </>
  );
}
