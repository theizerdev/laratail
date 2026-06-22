import { useState, useEffect } from 'react';
import PageMetaData from '@/components/PageMeta';
import axios from '@/lib/axios';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';

// Reusable UI components
import ModuleHeader from '@/components/ui/ModuleHeader';
import FilterBar from '@/components/ui/FilterBar';
import TableCard from '@/components/ui/TableCard';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { LuHistory, LuEye, LuChevronDown, LuArrowDown } from 'react-icons/lu';

// Modal
import AuditModal from './components/AuditModal';

const MonitoreoAuditoria = () => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedAction, setSelectedAction] = useState('Todas');
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  
  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedLog, setSelectedLog] = useState<any | null>(null);

  const fetchAuditoria = async (currentPage = 1, search = '') => {
    try {
      setLoading(true);
      const response = await axios.get(`/api/admin/monitoreo/auditoria?page=${currentPage}&search=${search}`);
      setData(response.data.data.data);
      setTotalPages(response.data.data.last_page);
      setPage(currentPage);
    } catch (error) {
      console.error('Error fetching auditoria', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const delayDebounceFn = setTimeout(() => {
      fetchAuditoria(1, searchTerm);
    }, 500);
    return () => clearTimeout(delayDebounceFn);
  }, [searchTerm]);

  const getEventColor = (event: string) => {
    switch (event) {
      case 'created': return 'bg-success/10 text-success border-success/20';
      case 'updated': return 'bg-warning/10 text-warning border-warning/20';
      case 'deleted': return 'bg-danger/10 text-danger border-danger/20';
      case 'restored': return 'bg-info/10 text-info border-info/20';
      default: return 'bg-default-100 text-default-600 border-default-200';
    }
  };

  const getEventName = (event: string) => {
    switch (event) {
      case 'created': return 'Creación';
      case 'updated': return 'Actualización';
      case 'deleted': return 'Eliminación';
      case 'restored': return 'Restauración';
      default: return event;
    }
  };

  const openModal = (log: any) => {
    setSelectedLog(log);
    setIsModalOpen(true);
  };

  const handleClearFilters = () => {
    setSearchTerm('');
    setSelectedAction('Todas');
  };

  // Filter localmente si la API no filtra por accion
  const filteredData = data.filter(log => {
    if (selectedAction === 'Todas') return true;
    if (selectedAction === 'Creación' && log.event === 'created') return true;
    if (selectedAction === 'Actualización' && log.event === 'updated') return true;
    if (selectedAction === 'Eliminación' && log.event === 'deleted') return true;
    return false;
  });

  return (
    <>
      <PageMetaData title="Auditoría del Sistema" />
      <main className="space-y-6">
        
        {/* Module Header */}
        <ModuleHeader
          title="Auditoría"
          description="Registro detallado de todas las acciones de los usuarios en la plataforma."
          icon={<LuHistory className="size-8" />}
          breadcrumbs={[
            { label: 'Monitoreo', active: false },
            { label: 'Auditoría', active: true }
          ]}
        />

        {/* Filter Bar */}
        <FilterBar
          searchTerm={searchTerm}
          onSearchChange={setSearchTerm}
          searchPlaceholder="Buscar por usuario o ID..."
          onClear={handleClearFilters}
        >
          {/* Action Dropdown */}
          <div>
            <label className="block text-[11px] font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Acción</label>
            <div className="relative">
              <select 
                className="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 bg-white appearance-none cursor-pointer"
                value={selectedAction}
                onChange={(e) => setSelectedAction(e.target.value)}
              >
                <option value="Todas">Todas</option>
                <option value="Creación">Creaciones</option>
                <option value="Actualización">Actualizaciones</option>
                <option value="Eliminación">Eliminaciones</option>
              </select>
              <LuChevronDown className="absolute right-3 top-3 text-gray-400 size-3.5 pointer-events-none" />
            </div>
          </div>
        </FilterBar>

        {/* Table Card */}
        <TableCard
          title="Historial de Acciones"
          icon={<LuHistory className="size-4 text-gray-500" />}
        >
          <Table>
            <TableHeader className="bg-[#f8fafc]">
              <TableRow>
                <TableHead>FECHA</TableHead>
                <TableHead>USUARIO / IP</TableHead>
                <TableHead>ACCIÓN / MÓDULO</TableHead>
                <TableHead className="w-[40%]">DESCRIPCIÓN</TableHead>
                <TableHead className="text-right">DETALLES</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody className="bg-white">
              {loading && data.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={5} className="h-24 text-center">
                    <div className="flex justify-center">
                      <div className="animate-spin size-6 border-[3px] border-primary border-t-transparent rounded-full"></div>
                    </div>
                  </TableCell>
                </TableRow>
              ) : filteredData.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={5} className="h-24 text-center text-default-500">
                    No se encontraron registros de auditoría.
                  </TableCell>
                </TableRow>
              ) : (
                filteredData.map((log) => (
                  <TableRow key={log.id} className="hover:bg-default-50 transition-colors cursor-pointer" onClick={() => openModal(log)}>
                    <TableCell className="whitespace-nowrap">
                      <div className="text-sm font-medium text-gray-900">
                        {format(new Date(log.created_at), "dd MMM yyyy", { locale: es })}
                      </div>
                      <div className="text-xs text-gray-500">
                        {format(new Date(log.created_at), "hh:mm a", { locale: es })}
                      </div>
                    </TableCell>
                    
                    <TableCell className="whitespace-nowrap">
                      <div className="text-sm font-semibold text-primary truncate max-w-[150px]" title={log.properties?.usuario_nombre || 'Sistema'}>
                        {log.properties?.usuario_nombre || 'Sistema'}
                      </div>
                      <div className="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                        <span className="inline-block size-1.5 rounded-full bg-gray-400"></span>
                        {log.properties?.ip_address || 'N/A'}
                      </div>
                    </TableCell>
                    
                    <TableCell className="whitespace-nowrap">
                      <span className={`px-2.5 py-1 inline-flex text-[11px] font-bold uppercase tracking-wider rounded border ${getEventColor(log.event)}`}>
                        {getEventName(log.event)}
                      </span>
                      <div className="text-xs text-gray-500 mt-1.5 font-medium">
                        {log.log_name}
                      </div>
                    </TableCell>
                    
                    <TableCell>
                      <div className="text-sm text-gray-700 truncate max-w-[300px] xl:max-w-[400px]" title={log.description}>
                        {log.description}
                      </div>
                    </TableCell>

                    <TableCell className="text-right">
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={(e) => {
                          e.stopPropagation();
                          openModal(log);
                        }}
                        className="text-primary hover:bg-primary/10"
                      >
                        <LuEye className="size-4 mr-1.5" />
                        Ver
                      </Button>
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>

          {/* Pagination Footer */}
          {totalPages > 1 && (
            <div className="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-white rounded-b-xl">
              <span className="text-sm text-gray-500 font-medium">Página {page} de {totalPages}</span>
              <div className="flex gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  disabled={page === 1}
                  onClick={() => fetchAuditoria(page - 1, searchTerm)}
                >
                  Anterior
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  disabled={page === totalPages}
                  onClick={() => fetchAuditoria(page + 1, searchTerm)}
                >
                  Siguiente
                </Button>
              </div>
            </div>
          )}
        </TableCard>

      </main>

      {/* Details Modal */}
      <AuditModal 
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        log={selectedLog}
      />
    </>
  );
};

export default MonitoreoAuditoria;
