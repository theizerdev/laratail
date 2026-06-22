import { useState, useEffect } from 'react';
import axios from '@/lib/axios';
import PageMeta from '@/components/PageMeta';
import { 
  LuGlobe, 
  LuPencil, 
  LuTrash2, 
  LuCheck, 
  LuChevronDown,
  LuArrowDown,
  LuPlus
} from 'react-icons/lu';
import PaisModal from './components/PaisModal';

// Reusable UI components
import ModuleHeader from '@/components/ui/ModuleHeader';
import StatsGrid from '@/components/ui/StatsGrid';
import FilterBar from '@/components/ui/FilterBar';
import TableCard from '@/components/ui/TableCard';

// shadcn/ui
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Button } from "@/components/ui/button";

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

const GlobeIconMini = ({ className }: { className?: string }) => (
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}>
    <circle cx="12" cy="12" r="10" />
    <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20" />
    <path d="M2 12h20" />
  </svg>
);

export default function PaisesIndex() {
  const [paises, setPaises] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  // Pagination
  const [currentPage, setCurrentPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [totalRecords, setTotalRecords] = useState(0);

  // Filters
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedContinente, setSelectedContinente] = useState('Todos');
  const [selectedEstado, setSelectedEstado] = useState('Todos');

  // Dropdown list control
  const [activeDropdownId, setActiveDropdownId] = useState<number | null>(null);

  // Modal control
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingPais, setEditingPais] = useState<any | null>(null);

  const fetchPaises = async (page = 1, search = '') => {
    try {
      setLoading(true);
      const params: any = { page };
      if (search) params.search = search;
      
      const res = await axios.get('/api/admin/paises', { params });
      const data = res.data;
      
      const fetchedPaises = Array.isArray(data.data) ? data.data : (Array.isArray(data) ? data : []);
      setPaises(fetchedPaises);
      setCurrentPage(data.current_page || 1);
      setLastPage(data.last_page || 1);
      setTotalRecords(data.total || fetchedPaises.length);
    } catch (error) {
      console.error('Error fetching countries:', error);
      setPaises([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPaises(currentPage, searchTerm);
  }, [currentPage]);
  
  useEffect(() => {
    const delayDebounceFn = setTimeout(() => {
      if(currentPage !== 1) {
          setCurrentPage(1);
      } else {
          fetchPaises(1, searchTerm);
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
    if (!confirm('¿Estás seguro de que deseas eliminar este país?')) return;
    try {
      await axios.delete(`/api/admin/paises/${id}`);
      fetchPaises(currentPage, searchTerm);
    } catch (error) {
      alert('Error eliminando el país.');
    }
  };

  const openCreateModal = () => {
    setEditingPais(null);
    setIsModalOpen(true);
  };

  const openEditModal = (pais: any) => {
    setEditingPais(pais);
    setIsModalOpen(true);
  };

  const handleToggleEstado = async (pais: any) => {
    try {
      await axios.put(`/api/admin/paises/${pais.id}`, {
        ...pais,
        activo: !pais.activo
      });
      fetchPaises(currentPage, searchTerm);
    } catch (error) {
      alert('Error actualizando el estado del país.');
    }
  };

  const handleClearFilters = () => {
    setSearchTerm('');
    setSelectedContinente('Todos');
    setSelectedEstado('Todos');
  };

  // Filter Logic (Search is server-side now)
  const filteredPaises = paises.filter(pais => {
    const matchesContinente = selectedContinente === 'Todos' || pais.continente === selectedContinente;
    
    const matchesEstado = 
      selectedEstado === 'Todos' || 
      (selectedEstado === 'Activos' && pais.activo) || 
      (selectedEstado === 'Inactivos' && !pais.activo);

    return matchesContinente && matchesEstado;
  });

  // Export CSV
  const handleExport = () => {
    if (filteredPaises.length === 0) {
      alert('No hay datos para exportar.');
      return;
    }
    const headers = ['#', 'Nombre', 'ISO2', 'ISO3', 'Código Telefónico', 'Moneda', 'Continente', 'Estado'];
    const rows = filteredPaises.map((pais, index) => [
      filteredPaises.length - index,
      pais.nombre,
      pais.codigo_iso2,
      pais.codigo_iso3,
      pais.codigo_telefonico || '',
      pais.moneda_principal || '',
      pais.continente || '',
      pais.activo ? 'Activo' : 'Inactivo'
    ]);

    const csvContent = "data:text/csv;charset=utf-8,\uFEFF" 
      + [headers.join(','), ...rows.map(e => e.map(val => `"${String(val).replace(/"/g, '""')}"`).join(','))].join('\n');
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `listado_paises_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  // Stats Grid calculation
  const totalPaises = paises.length;
  const activeCount = paises.filter(p => p.activo).length;
  const inactiveCount = paises.filter(p => !p.activo).length;
  
  const continentesSet = new Set(paises.map(p => p.continente).filter(Boolean));
  const uniqueContinents = continentesSet.size;

  const statsItems = [
    {
      label: 'Total Países',
      value: totalPaises,
      icon: <LuGlobe className="size-6" />,
      iconBgClass: 'bg-blue-50 text-blue-500'
    },
    {
      label: 'Países Activos',
      value: activeCount,
      icon: <LuCheck className="size-6 font-bold" />,
      iconBgClass: 'bg-emerald-50 text-[#10b981]'
    },
    {
      label: 'Países Inactivos',
      value: inactiveCount,
      icon: <XCircleIcon className="size-6" />,
      iconBgClass: 'bg-rose-50 text-[#ef4444]'
    },
    {
      label: 'Continentes',
      value: uniqueContinents,
      icon: <GlobeIconMini className="size-6" />,
      iconBgClass: 'bg-amber-50 text-[#f59e0b]'
    }
  ];

  return (
    <>
      <PageMeta title="Gestión de Países" />
      <main className="space-y-6">
        
        {/* Header */}
        <ModuleHeader
          title="Países"
          description="Gestión administrativa de países, formatos de fecha, moneda e impuestos locales"
          icon={<LuGlobe className="size-8" />}
          breadcrumbs={[{ label: 'Países', active: true }]}
          actionButton={{
            label: 'Nuevo País',
            onClick: openCreateModal
          }}
        />

        {/* Stats */}
        <StatsGrid stats={statsItems} />

        {/* Filters */}
        <FilterBar
          searchTerm={searchTerm}
          onSearchChange={setSearchTerm}
          searchPlaceholder="Nombre, ISO2, ISO3..."
          onClear={handleClearFilters}
          onExport={handleExport}
        >
          {/* Continente Dropdown */}
          <div>
            <label className="block text-[11px] font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Continente</label>
            <div className="relative">
              <select 
                className="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 bg-white appearance-none cursor-pointer"
                value={selectedContinente}
                onChange={(e) => setSelectedContinente(e.target.value)}
              >
                <option value="Todos">Todos</option>
                <option value="América del Norte">América del Norte</option>
                <option value="América del Sur">América del Sur</option>
                <option value="América Central">América Central</option>
                <option value="Europa">Europa</option>
                <option value="Asia">Asia</option>
                <option value="África">África</option>
                <option value="Oceanía">Oceanía</option>
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

        {/* Data Table */}
        <TableCard
          title="Listado de Países"
          icon={<LuGlobe className="size-4 text-gray-500" />}
        >
          <Table>
            <TableHeader className="bg-[#f8fafc]">
              <TableRow>
                <TableHead className="w-[50px]">#</TableHead>
                <TableHead>Nombre</TableHead>
                <TableHead>ISO2</TableHead>
                <TableHead>ISO3</TableHead>
                <TableHead>TELÉFONO</TableHead>
                <TableHead>MONEDA</TableHead>
                <TableHead>IMPUESTO</TableHead>
                <TableHead>CONTINENTE</TableHead>
                <TableHead>ESTADO</TableHead>
                <TableHead className="text-right">ACCIONES</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody className="bg-white">
              {loading ? (
                <TableRow>
                  <TableCell colSpan={10} className="h-24 text-center text-sm text-gray-400">Cargando países...</TableCell>
                </TableRow>
              ) : filteredPaises.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={10} className="h-24 text-center text-sm text-gray-400">No se encontraron países</TableCell>
                </TableRow>
              ) : (
                filteredPaises.map((pais, index) => {
                  const indexNum = filteredPaises.length - index;
                  
                  return (
                    <TableRow key={pais.id} className="hover:bg-[#f8fafc]/50 transition-colors">
                      <TableCell className="font-medium text-gray-500">
                        {indexNum}
                      </TableCell>
                      <TableCell>
                        <div className="flex items-center gap-3">
                          <div className="size-9 rounded-md bg-[#e0f2fe] text-[#0369a1] flex items-center justify-center font-bold text-sm shadow-sm select-none">
                            {pais.nombre.substring(0, 2).toUpperCase()}
                          </div>
                          <span className="text-sm font-semibold text-gray-700">{pais.nombre}</span>
                        </div>
                      </TableCell>
                      <TableCell className="text-gray-500 font-mono">
                        {pais.codigo_iso2}
                      </TableCell>
                      <TableCell className="text-gray-500 font-mono">
                        {pais.codigo_iso3}
                      </TableCell>
                      <TableCell className="text-gray-500">
                        {pais.codigo_telefonico || '-'}
                      </TableCell>
                      <TableCell className="text-gray-500 font-semibold">
                        {pais.moneda_principal || '-'}
                      </TableCell>
                      <TableCell className="text-gray-500">
                        {pais.impuesto_predeterminado ? `${pais.impuesto_predeterminado}%` : '0%'}
                      </TableCell>
                      <TableCell className="text-gray-500">
                        {pais.continente || '-'}
                      </TableCell>
                      <TableCell>
                        <label className="relative inline-flex items-center cursor-pointer select-none">
                          <input 
                            type="checkbox" 
                            className="sr-only peer" 
                            checked={pais.activo}
                            onChange={() => handleToggleEstado(pais)}
                          />
                          <div className="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#0f3d4c]"></div>
                        </label>
                      </TableCell>
                      <TableCell className="text-right font-medium relative">
                        <Button
                          variant="outline"
                          size="icon"
                          onClick={(e) => {
                            e.stopPropagation();
                            setActiveDropdownId(activeDropdownId === pais.id ? null : pais.id);
                          }}
                          className="h-8 w-8 text-gray-500 hover:text-gray-700 hover:bg-gray-50"
                        >
                          <MoreVerticalIcon className="size-4" />
                        </Button>
                        {activeDropdownId === pais.id && (
                          <div className="absolute right-5 mt-1 w-32 bg-white rounded-lg shadow-lg border border-gray-100 z-50 py-1 text-left">
                            <button
                              onClick={() => openEditModal(pais)}
                              className="w-full px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50 flex items-center gap-2 font-medium"
                            >
                              <LuPencil className="size-3.5 text-indigo-500" />
                              Editar
                            </button>
                            <button
                              onClick={() => handleDelete(pais.id)}
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
        </TableCard>
      </main>

      <PaisModal 
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        pais={editingPais}
        onSuccess={() => fetchPaises(currentPage, searchTerm)}
      />
    </>
  );
}
