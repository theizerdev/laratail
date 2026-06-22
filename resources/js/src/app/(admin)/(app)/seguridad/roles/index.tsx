import { useState, useEffect } from 'react';
import axios from '@/lib/axios';
import PageMeta from '@/components/PageMeta';
import { 
  LuShield, 
  LuPencil, 
  LuTrash2, 
  LuCheck, 
  LuKey,
  LuArrowDown
} from 'react-icons/lu';
import { useNavigate } from 'react-router-dom';

// Reusable UI components
import ModuleHeader from '@/components/ui/ModuleHeader';
import StatsGrid from '@/components/ui/StatsGrid';
import FilterBar from '@/components/ui/FilterBar';
import TableCard from '@/components/ui/TableCard';

// shadcn/ui
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Button } from "@/components/ui/button";

const MoreVerticalIcon = ({ className }: { className?: string }) => (
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}>
    <circle cx="12" cy="12" r="1" />
    <circle cx="12" cy="5" r="1" />
    <circle cx="12" cy="19" r="1" />
  </svg>
);

export default function RolesIndex() {
  const [roles, setRoles] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [activeDropdownId, setActiveDropdownId] = useState<number | null>(null);
  const navigate = useNavigate();

  const fetchRoles = async () => {
    try {
      setLoading(true);
      const res = await axios.get('/api/admin/roles');
      const data = res.data;
      setRoles(Array.isArray(data) ? data : (Array.isArray(data?.data) ? data.data : []));
    } catch (error: any) {
      console.error('Error fetching roles:', error);
      if (error.response?.status === 403) {
        alert("Acceso denegado: No tienes permisos para ver los roles.");
      } else if (error.response?.status === 401) {
        alert("Sesión expirada o no iniciada. Por favor, inicia sesión.");
      }
      setRoles([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchRoles();
  }, []);

  // Close dropdowns when clicking outside
  useEffect(() => {
    const handleGlobalClick = () => setActiveDropdownId(null);
    window.addEventListener('click', handleGlobalClick);
    return () => window.removeEventListener('click', handleGlobalClick);
  }, []);

  const handleDelete = async (id: number) => {
    if (!confirm('¿Estás seguro de que deseas eliminar este rol?')) return;
    try {
      await axios.delete(`/api/admin/roles/${id}`);
      fetchRoles();
    } catch (error: any) {
      alert(error.response?.data?.message || 'Error eliminando rol.');
    }
  };

  const handleClearFilters = () => {
    setSearchTerm('');
  };

  // Filter logic
  const filteredRoles = roles.filter(role => 
    (role.display_name || '').toLowerCase().includes(searchTerm.toLowerCase()) || 
    role.name.toLowerCase().includes(searchTerm.toLowerCase())
  );

  // Export CSV
  const handleExport = () => {
    if (filteredRoles.length === 0) {
      alert('No hay datos para exportar.');
      return;
    }
    const headers = ['#', 'Nombre del Rol', 'Slug', 'Permisos Asignados'];
    const rows = filteredRoles.map((role, index) => [
      filteredRoles.length - index,
      role.display_name || role.name,
      role.name,
      role.permissions ? role.permissions.length : 0
    ]);

    const csvContent = "data:text/csv;charset=utf-8,\uFEFF" 
      + [headers.join(','), ...rows.map(e => e.map(val => `"${String(val).replace(/"/g, '""')}"`).join(','))].join('\n');
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `listado_roles_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  // Calculations for stats
  const totalRoles = roles.length;
  const totalAssignedPermissions = roles.reduce((acc, r) => acc + (r.permissions?.length || 0), 0);

  const statsItems = [
    {
      label: 'Total Roles',
      value: totalRoles,
      icon: <LuShield className="size-6" />,
      iconBgClass: 'bg-blue-50 text-blue-500'
    },
    {
      label: 'Permisos Asignados',
      value: totalAssignedPermissions,
      icon: <LuKey className="size-6" />,
      iconBgClass: 'bg-emerald-50 text-emerald-600'
    },
    {
      label: 'Rol Super Admin',
      value: 'Activo',
      icon: <LuCheck className="size-6 font-bold" />,
      iconBgClass: 'bg-green-50 text-green-500'
    },
    {
      label: 'Guard Defecto',
      value: 'web',
      icon: <LuShield className="size-6" />,
      iconBgClass: 'bg-amber-50 text-amber-500'
    }
  ];

  return (
    <>
      <PageMeta title="Gestión de Roles" />
      <main className="space-y-6">
        
        {/* Module Header */}
        <ModuleHeader
          title="Roles"
          description="Gestión de roles y permisos de acceso al sistema"
          icon={<LuShield className="size-8" />}
          breadcrumbs={[{ label: 'Roles', active: true }]}
          actionButton={{
            label: 'Nuevo Rol',
            onClick: () => navigate('/admin/seguridad/roles/create')
          }}
        />

        {/* 4 Stats Grid */}
        <StatsGrid stats={statsItems} />

        {/* Filter Bar */}
        <FilterBar
          searchTerm={searchTerm}
          onSearchChange={setSearchTerm}
          searchPlaceholder="Buscar por nombre..."
          onClear={handleClearFilters}
          onExport={handleExport}
        />

        {/* Roles Table Card */}
        <TableCard
          title="Listado de roles"
          icon={<LuShield className="size-4 text-gray-500" />}
        >
          <Table>
            <TableHeader className="bg-[#f8fafc]">
              <TableRow>
                <TableHead className="w-[50px]">#</TableHead>
                <TableHead>Nombre del Rol</TableHead>
                <TableHead>Identificador (Slug)</TableHead>
                <TableHead>
                  <span className="flex items-center gap-1 select-none">
                    Permisos Asignados <LuArrowDown className="size-3.5 text-gray-400" />
                  </span>
                </TableHead>
                <TableHead className="text-right">ACCIONES</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody className="bg-white">
              {loading ? (
                <TableRow>
                  <TableCell colSpan={5} className="h-24 text-center text-sm text-gray-400">Cargando roles...</TableCell>
                </TableRow>
              ) : filteredRoles.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={5} className="h-24 text-center text-sm text-gray-400">No se encontraron roles</TableCell>
                </TableRow>
              ) : (
                filteredRoles.map((role, index) => {
                  const indexNum = filteredRoles.length - index;
                  
                  return (
                    <TableRow key={role.id} className="hover:bg-[#f8fafc]/50 transition-colors">
                      {/* ID # */}
                      <TableCell className="font-medium text-gray-500">
                        {indexNum}
                      </TableCell>

                      {/* Display Name */}
                      <TableCell>
                        <span className="text-sm font-semibold text-gray-700">
                          {role.display_name || role.name}
                        </span>
                      </TableCell>

                      {/* Name slug */}
                      <TableCell className="text-gray-500">
                        {role.name}
                      </TableCell>

                      {/* Permissions Count */}
                      <TableCell>
                        <span className="inline-flex items-center bg-indigo-50 text-indigo-700 text-xs font-semibold px-2.5 py-0.5 rounded">
                          {role.permissions ? role.permissions.length : 0} Permisos
                        </span>
                      </TableCell>

                      {/* Actions */}
                      <TableCell className="text-right font-medium relative">
                        <Button
                          variant="outline"
                          size="icon"
                          onClick={(e) => {
                            e.stopPropagation();
                            setActiveDropdownId(activeDropdownId === role.id ? null : role.id);
                          }}
                          className="h-8 w-8 text-gray-500 hover:text-gray-700 hover:bg-gray-50"
                          title="Acciones"
                        >
                          <MoreVerticalIcon className="size-4" />
                        </Button>

                        {/* Dropdown Menu */}
                        {activeDropdownId === role.id && (
                          <div className="absolute right-5 mt-1 w-32 bg-white rounded-lg shadow-lg border border-gray-100 z-50 py-1 text-left">
                            <button
                              onClick={() => {
                                navigate(`/admin/seguridad/roles/${role.id}/edit`);
                                setActiveDropdownId(null);
                              }}
                              className="w-full px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50 flex items-center gap-2 font-medium"
                            >
                              <LuPencil className="size-3.5 text-indigo-500" />
                              Editar
                            </button>
                            {role.name !== 'super-admin' && (
                              <button
                                onClick={() => handleDelete(role.id)}
                                className="w-full px-3 py-1.5 text-xs text-red-600 hover:bg-red-50 flex items-center gap-2 font-medium"
                              >
                                <LuTrash2 className="size-3.5 text-red-500" />
                                Eliminar
                              </button>
                            )}
                          </div>
                        )}
                      </TableCell>
                    </TableRow>
                  );
                })
              )}
            </TableBody>
          </Table>
        </TableCard>

      </main>
    </>
  );
}
