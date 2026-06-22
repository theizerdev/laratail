import { useState, useEffect } from 'react';
import axios from '@/lib/axios';
import PageMeta from '@/components/PageMeta';
import { 
  LuStore, 
  LuPencil, 
  LuTrash2, 
  LuCheck, 
  LuX,
  LuPlus,
  LuChevronDown
} from 'react-icons/lu';
import SucursalModal from './components/SucursalModal';

// Reusable UI components
import ModuleHeader from '@/components/ui/ModuleHeader';
import StatsGrid from '@/components/ui/StatsGrid';
import FilterBar from '@/components/ui/FilterBar';
import TableCard from '@/components/ui/TableCard';

// shadcn/ui
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { Switch } from "@/components/ui/switch";

const MoreVerticalIcon = ({ className }: { className?: string }) => (
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}>
    <circle cx="12" cy="12" r="1" />
    <circle cx="12" cy="5" r="1" />
    <circle cx="12" cy="19" r="1" />
  </svg>
);

export default function SucursalesIndex() {
  const [sucursales, setSucursales] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  // Filters
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedEmpresa, setSelectedEmpresa] = useState('Todas');
  const [selectedEstado, setSelectedEstado] = useState('Todos');

  // Pagination
  const [currentPage, setCurrentPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [totalRecords, setTotalRecords] = useState(0);

  // Dropdown list control
  const [activeDropdownId, setActiveDropdownId] = useState<number | null>(null);

  // Modal control
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingSucursal, setEditingSucursal] = useState<any | null>(null);

  const fetchSucursales = async (page = 1, search = '') => {
    try {
      setLoading(true);
      const params: any = { page };
      if (search) params.search = search;

      const res = await axios.get('/api/admin/configuracion/sucursales', { params });
      const data = res.data;

      const fetchedSucursales = Array.isArray(data.data) ? data.data : (Array.isArray(data) ? data : []);
      setSucursales(fetchedSucursales);
      setCurrentPage(data.current_page || 1);
      setLastPage(data.last_page || 1);
      setTotalRecords(data.total || fetchedSucursales.length);
    } catch (error) {
      console.error('Error fetching sucursales:', error);
      setSucursales([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchSucursales(currentPage, searchTerm);
  }, [currentPage]);
  
  useEffect(() => {
    const delayDebounceFn = setTimeout(() => {
      if(currentPage !== 1) {
          setCurrentPage(1);
      } else {
          fetchSucursales(1, searchTerm);
      }
    }, 500);

    return () => clearTimeout(delayDebounceFn);
  }, [searchTerm]);

  // Close dropdowns on window click
  useEffect(() => {
    const handleGlobalClick = () => setActiveDropdownId(null);
    window.addEventListener('click', handleGlobalClick);
    return () => window.removeEventListener('click', handleGlobalClick);
  }, []);

  const handleDelete = async (id: number) => {
    if (!confirm('¿Estás seguro de que deseas eliminar esta sucursal?')) return;
    try {
      await axios.delete(`/api/admin/configuracion/sucursales/${id}`);
      fetchSucursales(currentPage, searchTerm);
    } catch (error) {
      alert('Error eliminando la sucursal.');
    }
  };

  const openCreateModal = () => {
    setEditingSucursal(null);
    setIsModalOpen(true);
  };

  const openEditModal = (sucursal: any) => {
    setEditingSucursal(sucursal);
    setIsModalOpen(true);
  };

  const handleToggleEstado = async (id: number) => {
    try {
      await axios.put(`/api/admin/configuracion/sucursales/${id}/status`);
      fetchSucursales(currentPage, searchTerm);
    } catch (error) {
      alert('Error actualizando el estado de la sucursal.');
    }
  };

  const handleClearFilters = () => {
    setSearchTerm('');
    setSelectedEmpresa('Todas');
    setSelectedEstado('Todos');
  };

  // Filter Logic (Search is server-side now)
  const filteredSucursales = sucursales.filter(sucursal => {
    const matchesEmpresa = selectedEmpresa === 'Todas' || sucursal.empresa?.razon_social === selectedEmpresa;
    
    const matchesEstado = 
      selectedEstado === 'Todos' || 
      (selectedEstado === 'Activas' && sucursal.status) || 
      (selectedEstado === 'Inactivas' && !sucursal.status);

    return matchesEmpresa && matchesEstado;
  });

  // Unique Empresas for filter
  const empresasSet = new Set(sucursales.map(s => s.empresa?.razon_social).filter(Boolean));
  const uniqueEmpresasArray = Array.from(empresasSet) as string[];

  // Export CSV
  const handleExport = () => {
    if (filteredSucursales.length === 0) {
      alert('No hay datos para exportar.');
      return;
    }
    const headers = ['#', 'Nombre', 'Empresa', 'Teléfono', 'Dirección', 'Estado'];
    const rows = filteredSucursales.map((s, index) => [
      filteredSucursales.length - index,
      s.nombre,
      s.empresa?.razon_social || '',
      s.telefono || '',
      s.direccion || '',
      s.status ? 'Activo' : 'Inactivo'
    ]);

    const csvContent = "data:text/csv;charset=utf-8,\uFEFF" 
      + [headers.join(','), ...rows.map(e => e.map(val => `"${String(val).replace(/"/g, '""')}"`).join(','))].join('\n');
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `listado_sucursales_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  // Stats Grid calculation
  const totalSucursales = sucursales.length;
  const activeCount = sucursales.filter(s => s.status).length;
  const inactiveCount = sucursales.filter(s => !s.status).length;
  
  const statsItems = [
    {
      label: 'Total Sucursales',
      value: totalSucursales,
      icon: <LuStore className="size-6" />,
      iconBgClass: 'bg-blue-50 text-blue-500'
    },
    {
      label: 'Activas',
      value: activeCount,
      icon: <LuCheck className="size-6 font-bold" />,
      iconBgClass: 'bg-emerald-50 text-[#10b981]'
    },
    {
      label: 'Inactivas',
      value: inactiveCount,
      icon: <LuX className="size-6" />,
      iconBgClass: 'bg-rose-50 text-[#ef4444]'
    },
    {
      label: 'Empresas Vinculadas',
      value: empresasSet.size,
      icon: <LuStore className="size-6" />,
      iconBgClass: 'bg-amber-50 text-amber-500'
    }
  ];

  return (
    <>
      <PageMeta title="Sucursales | Configuración" />
      <main className="space-y-6">
        
        {/* Module Header */}
        <ModuleHeader
          title="Sucursales"
          description="Gestiona las sucursales de las empresas registradas en el sistema"
          icon={<LuStore className="size-8" />}
          breadcrumbs={[{ label: 'Sucursales', active: true }]}
          actionButton={{
            label: 'Nueva Sucursal',
            onClick: openCreateModal
          }}
        />

        {/* Stats Grid */}
        <StatsGrid stats={statsItems} />

        {/* Filter Bar */}
        <FilterBar
          searchTerm={searchTerm}
          onSearchChange={setSearchTerm}
          searchPlaceholder="Buscar por nombre o empresa..."
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
                {uniqueEmpresasArray.map((emp) => (
                  <option key={emp} value={emp}>{emp}</option>
                ))}
              </select>
              <LuChevronDown className="absolute right-3 top-3 text-gray-400 size-4 pointer-events-none" />
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
                <option value="Activas">Activas</option>
                <option value="Inactivas">Inactivas</option>
              </select>
              <LuChevronDown className="absolute right-3 top-3 text-gray-400 size-4 pointer-events-none" />
            </div>
          </div>
        </FilterBar>

        {/* Data Table */}
        <TableCard>
          {loading ? (
            <div className="p-8 text-center text-gray-500">Cargando sucursales...</div>
          ) : (
            <div className="relative overflow-x-auto">
              <Table>
                <TableHeader className="bg-gray-50/50">
                  <TableRow>
                    <TableHead className="w-16 text-center font-semibold text-gray-600">ID</TableHead>
                    <TableHead className="font-semibold text-gray-600">NOMBRE</TableHead>
                    <TableHead className="font-semibold text-gray-600">EMPRESA</TableHead>
                    <TableHead className="font-semibold text-gray-600">TELÉFONO</TableHead>
                    <TableHead className="font-semibold text-gray-600 text-center">ESTADO</TableHead>
                    <TableHead className="text-right font-semibold text-gray-600 pr-6">ACCIONES</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredSucursales.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={6} className="text-center py-8 text-gray-500">
                        No se encontraron sucursales
                      </TableCell>
                    </TableRow>
                  ) : (
                    filteredSucursales.map((sucursal) => (
                      <TableRow key={sucursal.id} className="hover:bg-gray-50/50 transition-colors">
                        <TableCell className="text-center font-medium text-gray-900">
                          {sucursal.id}
                        </TableCell>
                        <TableCell>
                          <div className="font-medium text-[#0f3d4c]">{sucursal.nombre}</div>
                          {sucursal.direccion && <div className="text-xs text-gray-500 truncate max-w-[200px]">{sucursal.direccion}</div>}
                        </TableCell>
                        <TableCell>
                          <div className="flex items-center gap-2">
                            <div className="h-6 w-6 rounded bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600">
                              {sucursal.empresa?.razon_social ? sucursal.empresa.razon_social.charAt(0).toUpperCase() : '?'}
                            </div>
                            <span className="text-sm text-gray-700">{sucursal.empresa?.razon_social || 'N/A'}</span>
                          </div>
                        </TableCell>
                        <TableCell>
                          <span className="text-sm text-gray-600">{sucursal.telefono || '—'}</span>
                        </TableCell>
                        <TableCell className="text-center">
                          <Switch 
                            checked={sucursal.status} 
                            onCheckedChange={() => handleToggleEstado(sucursal.id)}
                          />
                        </TableCell>
                        <TableCell className="text-right pr-6">
                          <div className="flex justify-end items-center gap-2 relative">
                            {/* Actions Dropdown */}
                            <button 
                              className="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-md transition-colors"
                              onClick={(e) => {
                                e.stopPropagation();
                                setActiveDropdownId(activeDropdownId === sucursal.id ? null : sucursal.id);
                              }}
                            >
                              <MoreVerticalIcon className="size-4" />
                            </button>

                            {/* Dropdown Menu */}
                            {activeDropdownId === sucursal.id && (
                              <div className="absolute right-0 top-full mt-1 w-40 bg-white border border-gray-100 rounded-xl shadow-lg shadow-gray-200/50 py-1.5 z-50 animate-in fade-in slide-in-from-top-2">
                                <button 
                                  onClick={(e) => {
                                    e.stopPropagation();
                                    setActiveDropdownId(null);
                                    openEditModal(sucursal);
                                  }}
                                  className="w-full text-left px-3 py-1.5 text-sm text-gray-600 hover:text-[#0f3d4c] hover:bg-gray-50 flex items-center gap-2"
                                >
                                  <LuPencil className="size-4" />
                                  <span>Editar</span>
                                </button>
                                <button 
                                  onClick={(e) => {
                                    e.stopPropagation();
                                    setActiveDropdownId(null);
                                    handleDelete(sucursal.id);
                                  }}
                                  className="w-full text-left px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2"
                                >
                                  <LuTrash2 className="size-4" />
                                  <span>Eliminar</span>
                                </button>
                              </div>
                            )}
                          </div>
                        </TableCell>
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>
              
              {/* Pagination Controls */}
              {lastPage > 1 && (
                <div className="flex items-center justify-between px-6 py-3 border-t border-gray-200">
                  <div className="text-sm text-gray-500">
                    Mostrando página {currentPage} de {lastPage} ({totalRecords} registros)
                  </div>
                  <div className="flex gap-2">
                    <Button 
                      variant="outline" 
                      size="sm" 
                      onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                      disabled={currentPage === 1 || loading}
                    >
                      Anterior
                    </Button>
                    <Button 
                      variant="outline" 
                      size="sm" 
                      onClick={() => setCurrentPage(prev => Math.min(prev + 1, lastPage))}
                      disabled={currentPage === lastPage || loading}
                    >
                      Siguiente
                    </Button>
                  </div>
                </div>
              )}
            </div>
          )}
        </TableCard>

      </main>

      {/* Modal for Create/Edit */}
      <SucursalModal 
        isOpen={isModalOpen} 
        onClose={() => setIsModalOpen(false)} 
        sucursal={editingSucursal} 
        onSuccess={() => fetchSucursales(currentPage, searchTerm)} 
      />

    </>
  );
}
