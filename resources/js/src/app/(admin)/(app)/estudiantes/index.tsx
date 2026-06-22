import { useState, useEffect } from 'react';
import axios from '@/lib/axios';
import PageMeta from '@/components/PageMeta';
import { 
  LuGraduationCap, 
  LuPencil, 
  LuTrash2, 
  LuEye,
  LuCheck, 
  LuClock,  
  LuChevronDown,
  LuArrowDown
} from 'react-icons/lu';

// Reusable UI components
import ModuleHeader from '@/components/ui/ModuleHeader';
import StatsGrid from '@/components/ui/StatsGrid';
import FilterBar from '@/components/ui/FilterBar';
import TableCard from '@/components/ui/TableCard';

// shadcn/ui
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Button } from "@/components/ui/button";

import EstudianteModal from './EstudianteModal';
import EstudianteDetalleModal from './EstudianteDetalleModal';

const MoreVerticalIcon = ({ className }: { className?: string }) => (
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}>
    <circle cx="12" cy="12" r="1" />
    <circle cx="12" cy="5" r="1" />
    <circle cx="12" cy="19" r="1" />
  </svg>
);

const formatDate = (dateString: string) => {
  const d = new Date(dateString);
  if (isNaN(d.getTime())) return '';
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();
  return `${day}/${month}/${year}`;
};

export default function EstudiantesIndex() {
  const [estudiantes, setEstudiantes] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  
  // Pagination & Filters
  const [currentPage, setCurrentPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [totalRecords, setTotalRecords] = useState(0);

  // Interactive filters
  const [searchTerm, setSearchTerm] = useState('');
  
  // Dropdown list control
  const [activeDropdownId, setActiveDropdownId] = useState<number | null>(null);

  // Modal control
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isDetailModalOpen, setIsDetailModalOpen] = useState(false);
  const [selectedEstudiante, setSelectedEstudiante] = useState<any>(null);

  const fetchEstudiantes = async (page = 1, search = '') => {
    try {
      setLoading(true);
      const params: any = { page };
      if (search) params.search = search;
      
      const res = await axios.get('/api/estudiantes', { params });
      const data = res.data;
      
      const fetched = Array.isArray(data.data) ? data.data : (Array.isArray(data) ? data : []);
      setEstudiantes(fetched);
      setCurrentPage(data.current_page || 1);
      setLastPage(data.last_page || 1);
      setTotalRecords(data.total || fetched.length);
    } catch (error: any) {
      console.error('Error fetching estudiantes:', error);
      setEstudiantes([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchEstudiantes(currentPage, searchTerm);
  }, [currentPage]);

  useEffect(() => {
    const delayDebounceFn = setTimeout(() => {
      if(currentPage !== 1) {
          setCurrentPage(1);
      } else {
          fetchEstudiantes(1, searchTerm);
      }
    }, 500);

    return () => clearTimeout(delayDebounceFn);
  }, [searchTerm]);

  useEffect(() => {
    const handleGlobalClick = () => setActiveDropdownId(null);
    window.addEventListener('click', handleGlobalClick);
    return () => window.removeEventListener('click', handleGlobalClick);
  }, []);

  const handleDelete = async (id: number) => {
    if (!confirm('¿Estás seguro de que deseas eliminar este estudiante?')) return;
    try {
      await axios.delete(`/api/estudiantes/${id}`);
      fetchEstudiantes(currentPage, searchTerm);
    } catch (error: any) {
      alert('Error eliminando estudiante.');
    }
  };

  const openCreateModal = () => {
    setSelectedEstudiante(null);
    setIsModalOpen(true);
  };

  const openEditModal = (estudiante: any) => {
    setSelectedEstudiante(estudiante);
    setIsModalOpen(true);
  };

  const openDetailModal = (estudiante: any) => {
    setSelectedEstudiante(estudiante);
    setIsDetailModalOpen(true);
  };

  const handleModalSuccess = () => {
    setIsModalOpen(false);
    fetchEstudiantes(currentPage, searchTerm);
  };

  const handleClearFilters = () => {
    setSearchTerm('');
  };

  const handleExport = () => {
    alert("Función de exportar en desarrollo.");
  };

  const statsItems = [
    {
      label: 'Total Estudiantes',
      value: totalRecords,
      icon: <LuGraduationCap className="size-6" />,
      iconBgClass: 'bg-blue-50 text-blue-500'
    }
  ];

  return (
    <>
      <PageMeta title="Gestión de Estudiantes" />
      <main className="space-y-6">
        
        <ModuleHeader
          title="Estudiantes"
          description="Directorio de alumnos y control de accesos"
          icon={<LuGraduationCap className="size-8" />}
          breadcrumbs={[{ label: 'Estudiantes', active: true }]}
          actionButton={{
            label: 'Nuevo Estudiante',
            onClick: openCreateModal
          }}
        />

        <StatsGrid stats={statsItems} />

        <FilterBar
          searchTerm={searchTerm}
          onSearchChange={setSearchTerm}
          searchPlaceholder="Buscar por nombre, documento o código..."
          onClear={handleClearFilters}
          onExport={handleExport}
        >
        </FilterBar>

        <TableCard
          title="Listado de estudiantes"
          icon={<LuGraduationCap className="size-4 text-gray-500" />}
        >
          <Table>
            <TableHeader className="bg-[#f8fafc]">
              <TableRow>
                <TableHead className="w-[50px]">#</TableHead>
                <TableHead>NOMBRE</TableHead>
                <TableHead>DOCUMENTO</TableHead>
                <TableHead>GRADO Y SECCIÓN</TableHead>
                <TableHead>CÓDIGO ACCESO</TableHead>
                <TableHead className="text-right">ACCIONES</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody className="bg-white">
              {loading ? (
                <TableRow>
                  <TableCell colSpan={6} className="h-24 text-center text-sm text-gray-400">Cargando estudiantes...</TableCell>
                </TableRow>
              ) : estudiantes.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={6} className="h-24 text-center text-sm text-gray-400">No se encontraron estudiantes</TableCell>
                </TableRow>
              ) : (
                estudiantes.map((estudiante, index) => {
                  return (
                    <TableRow key={estudiante.id} className="hover:bg-[#f8fafc]/50 transition-colors">
                      <TableCell className="font-medium text-gray-500">
                        {estudiante.id}
                      </TableCell>
                      
                      <TableCell>
                        <div className="flex items-center gap-3">
                          <div className="size-9 rounded-md bg-[#dbeafe] text-[#1e40af] flex items-center justify-center font-bold text-sm shadow-sm">
                            {estudiante.nombre.charAt(0).toUpperCase()}
                          </div>
                          <span className="text-sm font-semibold text-gray-700">{estudiante.nombre} {estudiante.apellido}</span>
                        </div>
                      </TableCell>
                      
                      <TableCell className="text-gray-500">
                        {estudiante.dni || '-'}
                      </TableCell>
                      
                      <TableCell className="text-gray-500">
                        {estudiante.grado || '-'} {estudiante.seccion ? `(${estudiante.seccion})` : ''}
                      </TableCell>

                      <TableCell className="text-gray-500">
                        {estudiante.codigo_acceso || 'Sin asignar'}
                      </TableCell>
                      
                      <TableCell className="text-right">
                        <div className="flex justify-end gap-2">
                          <Button 
                            variant="outline" 
                            size="sm" 
                            className="h-8 w-8 p-0"
                            onClick={() => openDetailModal(estudiante)}
                            title="Ver ficha del estudiante"
                          >
                            <LuEye className="size-4" />
                          </Button>
                          <Button
                            variant="outline"
                            size="sm"
                            className="h-8 w-8 p-0 text-gray-500"
                            onClick={(e) => {
                              e.stopPropagation();
                              setActiveDropdownId(activeDropdownId === estudiante.id ? null : estudiante.id);
                            }}
                            title="Acciones"
                          >
                            <MoreVerticalIcon className="size-4" />
                          </Button>
                        </div>

                        {activeDropdownId === estudiante.id && (
                          <div className="absolute right-5 mt-1 w-32 bg-white rounded-lg shadow-lg border border-gray-100 z-50 py-1 text-left">
                            <button
                              onClick={() => openEditModal(estudiante)}
                              className="w-full px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50 flex items-center gap-2 font-medium"
                            >
                              <LuPencil className="size-3.5 text-indigo-500" />
                              Editar
                            </button>
                            <button
                              onClick={() => handleDelete(estudiante.id)}
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

      <EstudianteModal 
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        onSuccess={handleModalSuccess}
        estudiante={selectedEstudiante}
      />

      <EstudianteDetalleModal
        isOpen={isDetailModalOpen}
        onClose={() => setIsDetailModalOpen(false)}
        estudiante={selectedEstudiante}
      />
    </>
  );
}
