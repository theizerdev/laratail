import { useState, useEffect } from 'react';
import axios from '@/lib/axios';
import PageMeta from '@/components/PageMeta';
import { 
  LuUsers, 
  LuPencil, 
  LuTrash2, 
  LuCheck, 
  LuClock, 
  LuChevronDown,
  LuArrowDown
} from 'react-icons/lu';
import UserModal from './components/UserModal';

// Reusable UI components
import ModuleHeader from '@/components/ui/ModuleHeader';
import StatsGrid from '@/components/ui/StatsGrid';
import FilterBar from '@/components/ui/FilterBar';
import TableCard from '@/components/ui/TableCard';

// shadcn/ui
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Button } from "@/components/ui/button";

// Custom inline SVG icons to prevent bundle and version export mismatch errors
const XCircleIcon = ({ className }: { className?: string }) => (
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}>
    <circle cx="12" cy="12" r="10" />
    <path d="m15 9-6 6" />
    <path d="m9 9 6 6" />
  </svg>
);

const MoreVerticalIcon = ({ className }: { className?: string }) => (
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}>
    <circle cx="12" cy="12" r="1" />
    <circle cx="12" cy="5" r="1" />
    <circle cx="12" cy="19" r="1" />
  </svg>
);

// Helper to format date exactly like: 17/06/2026 16:33
const formatDate = (dateString: string) => {
  const d = new Date(dateString);
  if (isNaN(d.getTime())) return '';
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();
  const hours = String(d.getHours()).padStart(2, '0');
  const minutes = String(d.getMinutes()).padStart(2, '0');
  return `${day}/${month}/${year} ${hours}:${minutes}`;
};

export default function UsuariosIndex() {
  const [users, setUsers] = useState<any[]>([]);
  const [roles, setRoles] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  
  // Interactive filters
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedEmpresa, setSelectedEmpresa] = useState('Todas');
  const [selectedSucursal, setSelectedSucursal] = useState('Todas');
  const [selectedEstado, setSelectedEstado] = useState('Todos');
  
  // Local state for active/inactive status toggles
  const [userStatuses, setUserStatuses] = useState<Record<number, boolean>>({});
  
  // Dropdown list control
  const [activeDropdownId, setActiveDropdownId] = useState<number | null>(null);

  // Modal control
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingUser, setEditingUser] = useState<any | null>(null);

  const fetchUsers = async () => {
    try {
      setLoading(true);
      const res = await axios.get('/api/admin/usuarios');
      const data = res.data;      const fetchedUsers = Array.isArray(data) ? data : (Array.isArray(data?.data) ? data.data : []);
      setUsers(fetchedUsers);
      
      // Initialize status toggles for users that don't have one yet
      setUserStatuses(prev => {
        const next = { ...prev };
        fetchedUsers.forEach((u: any) => {
          if (next[u.id] === undefined) {
            next[u.id] = true; // default to Active
          }
        });
        return next;
      });
    } catch (error: any) {
      console.error('Error fetching users:', error);
      if (error.response?.status === 403) {
        alert("Acceso denegado: No tienes permisos para ver los usuarios.");
      } else if (error.response?.status === 401) {
        alert("Sesión expirada o no iniciada. Por favor, inicia sesión.");
      }
      setUsers([]);
    } finally {
      setLoading(false);
    }
  };

  const fetchRoles = async () => {
    try {
      const res = await axios.get('/api/admin/roles');
      const data = res.data;
      setRoles(Array.isArray(data) ? data : (Array.isArray(data?.data) ? data.data : []));
    } catch (error) {
      console.error('Error fetching roles:', error);
      setRoles([]);
    }
  };

  useEffect(() => {
    fetchUsers();
    fetchRoles();
  }, []);

  // Close dropdowns when clicking outside
  useEffect(() => {
    const handleGlobalClick = () => setActiveDropdownId(null);
    window.addEventListener('click', handleGlobalClick);
    return () => window.removeEventListener('click', handleGlobalClick);
  }, []);

  const handleDelete = async (id: number) => {
    if (!confirm('¿Estás seguro de que deseas eliminar este usuario?')) return;
    try {
      await axios.delete(`/api/admin/usuarios/${id}`);
      fetchUsers();
    } catch (error) {
      alert('Error eliminando usuario. Es posible que no tengas permisos o sea tu propio usuario.');
    }
  };

  const openCreateModal = () => {
    setEditingUser(null);
    setIsModalOpen(true);
  };

  const openEditModal = (user: any) => {
    setEditingUser(user);
    setIsModalOpen(true);
  };

  const handleToggleEstado = (userId: number) => {
    setUserStatuses(prev => ({
      ...prev,
      [userId]: !prev[userId]
    }));
  };

  const handleClearFilters = () => {
    setSearchTerm('');
    setSelectedEmpresa('Todas');
    setSelectedSucursal('Todas');
    setSelectedEstado('Todos');
  };

  const getUserSucursal = (user: any) => {
    return user.sucursal ? user.sucursal.nombre : '-';
  };

  const getUserEmpresa = (user: any) => {
    return user.empresa ? user.empresa.razon_social : '-';
  };

  // Filter logic
  const filteredUsers = users.filter(user => {
    const matchesSearch = 
      user.name.toLowerCase().includes(searchTerm.toLowerCase()) || 
      user.email.toLowerCase().includes(searchTerm.toLowerCase());

    const matchesEmpresa = selectedEmpresa === 'Todas' || getUserEmpresa(user) === selectedEmpresa;
    const matchesSucursal = selectedSucursal === 'Todas' || getUserSucursal(user) === selectedSucursal;

    const isUserActive = userStatuses[user.id] !== false;
    const matchesEstado = 
      selectedEstado === 'Todos' || 
      (selectedEstado === 'Activos' && isUserActive) || 
      (selectedEstado === 'Inactivos' && !isUserActive);

    return matchesSearch && matchesEmpresa && matchesSucursal && matchesEstado;
  });

  // Export CSV
  const handleExport = () => {
    if (filteredUsers.length === 0) {
      alert('No hay datos para exportar.');
      return;
    }
    const headers = ['#', 'Nombre', 'Email', 'Verificado', 'Empresa', 'Sucursal', 'Estado', 'Fecha Registro'];
    const rows = filteredUsers.map((user, index) => [
      filteredUsers.length - index,
      user.name,
      user.email,
      'Verificado',
      getUserEmpresa(user),
      getUserSucursal(user),
      (userStatuses[user.id] !== false) ? 'Activo' : 'Inactivo',
      formatDate(user.created_at)
    ]);

    const csvContent = "data:text/csv;charset=utf-8,\uFEFF" 
      + [headers.join(','), ...rows.map(e => e.map(val => `"${String(val).replace(/"/g, '""')}"`).join(','))].join('\n');
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `listado_usuarios_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  // Dynamic statistics calculations
  const totalUsers = users.length;
  const activeUsersCount = users.filter(u => userStatuses[u.id] !== false).length;
  const pendingUsersCount = users.filter(u => !u.email_verified_at).length;
  const inactiveUsersCount = users.filter(u => userStatuses[u.id] === false).length;

  // Prepare stats items array for reusable StatsGrid component
  const statsItems = [
    {
      label: 'Total Usuarios',
      value: totalUsers,
      icon: <LuUsers className="size-6" />,
      iconBgClass: 'bg-blue-50 text-blue-500'
    },
    {
      label: 'Usuarios Activos',
      value: activeUsersCount,
      icon: <LuCheck className="size-6 font-bold" />,
      iconBgClass: 'bg-emerald-50 text-[#10b981]'
    },
    {
      label: 'Usuarios Pendientes',
      value: pendingUsersCount,
      icon: <LuClock className="size-6" />,
      iconBgClass: 'bg-amber-50 text-[#f59e0b]'
    },
    {
      label: 'Usuarios Inactivos',
      value: inactiveUsersCount,
      icon: <XCircleIcon className="size-6" />,
      iconBgClass: 'bg-rose-50 text-[#ef4444]'
    }
  ];

  return (
    <>
      <PageMeta title="Gestión de Usuarios" />
      <main className="space-y-6">
        
        {/* Module Header (Breadcrumbs + Colored Banner) */}
        <ModuleHeader
          title="Usuarios"
          description="Gestión de usuarios y acceso al sistema"
          icon={<LuUsers className="size-8" />}
          breadcrumbs={[{ label: 'Usuarios', active: true }]}
          actionButton={{
            label: 'Nuevo Usuario',
            onClick: openCreateModal
          }}
        />

        {/* 4 Statistical Cards Grid */}
        <StatsGrid stats={statsItems} />

        {/* Filter Bar Panel */}
        <FilterBar
          searchTerm={searchTerm}
          onSearchChange={setSearchTerm}
          searchPlaceholder="Nombre, email, username..."
          onClear={handleClearFilters}
          onExport={handleExport}
        >
          {/* Empresa Dropdown */}
          <div>
            <label className="block text-[11px] font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Empresa</label>
            <div className="relative">
              <select 
                className="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 bg-white appearance-none cursor-pointer"
                value={selectedEmpresa}
                onChange={(e) => setSelectedEmpresa(e.target.value)}
              >
                <option value="Todas">Todas</option>
              </select>
              <LuChevronDown className="absolute right-3 top-3 text-gray-400 size-3.5 pointer-events-none" />
            </div>
          </div>

          {/* Sucursal Dropdown */}
          <div>
            <label className="block text-[11px] font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Sucursal</label>
            <div className="relative">
              <select 
                className="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 bg-white appearance-none cursor-pointer"
                value={selectedSucursal}
                onChange={(e) => setSelectedSucursal(e.target.value)}
              >
                <option value="Todas">Todas</option>
                <option value="Sucursal Pupila Inc.">Sucursal Pupila Inc.</option>
                <option value="Sucursal Principal">Sucursal Principal</option>
              </select>
              <LuChevronDown className="absolute right-3 top-3 text-gray-400 size-3.5 pointer-events-none" />
            </div>
          </div>

          {/* Estado Dropdown */}
          <div>
            <label className="block text-[11px] font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Estado</label>
            <div className="relative">
              <select 
                className="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 bg-white appearance-none cursor-pointer"
                value={selectedEstado}
                onChange={(e) => setSelectedEstado(e.target.value)}
              >
                <option value="Todos">Todos</option>
                <option value="Activos">Activos</option>
                <option value="Inactivos">Inactivos</option>
              </select>
              <LuChevronDown className="absolute right-3 top-3 text-gray-400 size-3.5 pointer-events-none" />
            </div>
          </div>
        </FilterBar>

        {/* User Data Table Card */}
        <TableCard
          title="Listado de usuarios"
          icon={<LuUsers className="size-4 text-gray-500" />}
        >
          <Table>
            <TableHeader className="bg-[#f8fafc]">
              <TableRow>
                <TableHead className="w-[50px]">#</TableHead>
                <TableHead>Nombre</TableHead>
                <TableHead>EMAIL</TableHead>
                <TableHead>TELÉFONO</TableHead>
                <TableHead>EMPRESA</TableHead>
                <TableHead>SUCURSAL</TableHead>
                <TableHead>ESTADO</TableHead>
                <TableHead>
                  <span className="flex items-center gap-1 select-none">
                    REGISTRO <LuArrowDown className="size-3.5 text-gray-400" />
                  </span>
                </TableHead>
                <TableHead className="text-right">ACCIONES</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody className="bg-white">
              {loading ? (
                <TableRow>
                  <TableCell colSpan={9} className="h-24 text-center text-sm text-gray-400">Cargando usuarios...</TableCell>
                </TableRow>
              ) : filteredUsers.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={9} className="h-24 text-center text-sm text-gray-400">No se encontraron usuarios</TableCell>
                </TableRow>
              ) : (
                filteredUsers.map((user, index) => {
                  const isUserActive = userStatuses[user.id] !== false;
                  const indexNum = filteredUsers.length - index;
                  
                  return (
                    <TableRow key={user.id} className="hover:bg-[#f8fafc]/50 transition-colors">
                      {/* ID # */}
                      <TableCell className="font-medium text-gray-500">
                        {indexNum}
                      </TableCell>
                      
                      {/* Name with Avatar */}
                      <TableCell>
                        <div className="flex items-center gap-3">
                          <div className="size-9 rounded-md bg-[#dbeafe] text-[#1e40af] flex items-center justify-center font-bold text-sm shadow-sm">
                            {user.name.charAt(0).toUpperCase()}
                          </div>
                          <span className="text-sm font-semibold text-gray-700">{user.name}</span>
                        </div>
                      </TableCell>
                      
                      {/* Email */}
                      <TableCell className="text-gray-500">
                        {user.email}
                      </TableCell>
                      
                      {/* Telefono */}
                      <TableCell className="text-gray-500">
                        {user.telefono || '-'}
                      </TableCell>
                      
                      {/* Empresa */}
                      <TableCell className="text-gray-500">
                        {getUserEmpresa(user)}
                      </TableCell>
                      
                      {/* Sucursal */}
                      <TableCell className="text-gray-500">
                        {getUserSucursal(user)}
                      </TableCell>
                      
                      {/* Estado Switch Toggle */}
                      <TableCell>
                        <label className="relative inline-flex items-center cursor-pointer select-none">
                          <input 
                            type="checkbox" 
                            className="sr-only peer" 
                            checked={isUserActive}
                            onChange={() => handleToggleEstado(user.id)}
                          />
                          <div className="w-9 h-5 bg-gray-200 rounded-full peer peer-focus:ring-0 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#0f3d4c]"></div>
                        </label>
                      </TableCell>
                      
                      {/* Fecha Registro */}
                      <TableCell className="text-gray-500">
                        {formatDate(user.created_at)}
                      </TableCell>
                      
                      {/* Actions Button */}
                      <TableCell className="text-right font-medium relative">
                        <Button
                          variant="outline"
                          size="icon"
                          onClick={(e) => {
                            e.stopPropagation();
                            setActiveDropdownId(activeDropdownId === user.id ? null : user.id);
                          }}
                          className="h-8 w-8 text-gray-500 hover:text-gray-700 hover:bg-gray-50"
                          title="Acciones"
                        >
                          <MoreVerticalIcon className="size-4" />
                        </Button>

                        {/* Dropdown Menu */}
                        {activeDropdownId === user.id && (
                          <div className="absolute right-5 mt-1 w-32 bg-white rounded-lg shadow-lg border border-gray-100 z-50 py-1 text-left">
                            <button
                              onClick={() => openEditModal(user)}
                              className="w-full px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50 flex items-center gap-2 font-medium"
                            >
                              <LuPencil className="size-3.5 text-indigo-500" />
                              Editar
                            </button>
                            <button
                              onClick={() => handleDelete(user.id)}
                              className="w-full px-3 py-1.5 text-xs text-red-600 hover:bg-red-50 flex items-center gap-2 font-medium"
                            >
                              <LuTrash2 className="size-3.5 text-red-500" />
                              Eliminar
                            </button>
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

      {/* Modal User Create / Edit */}
      {isModalOpen && (
        <UserModal 
          isOpen={isModalOpen} 
          onClose={() => setIsModalOpen(false)} 
          user={editingUser} 
          roles={roles}
          onSuccess={fetchUsers}
        />
      )}
    </>
  );
}
