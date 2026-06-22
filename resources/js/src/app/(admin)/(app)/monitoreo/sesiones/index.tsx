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
import { LuLogOut, LuLogIn } from 'react-icons/lu';

const MonitoreoSesiones = () => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  const fetchSessions = async (currentPage = 1, search = '') => {
    try {
      setLoading(true);
      const response = await axios.get(`/api/admin/monitoreo/sesiones?page=${currentPage}&search=${search}`);
      setData(response.data.data.data);
      setTotalPages(response.data.data.last_page);
      setPage(currentPage);
    } catch (error) {
      console.error('Error fetching sessions', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const delayDebounceFn = setTimeout(() => {
      fetchSessions(1, searchTerm);
    }, 500);
    return () => clearTimeout(delayDebounceFn);
  }, [searchTerm]);

  const formatDate = (dateString: string | null) => {
    if (!dateString) return <span className="text-default-400">Activa</span>;
    return format(new Date(dateString), "dd MMM yyyy, hh:mm a", { locale: es });
  };

  return (
    <>
      <PageMetaData title="Monitoreo de Sesiones" />
      <main className="space-y-6">
        
        {/* Module Header */}
        <ModuleHeader
          title="Registro de Sesiones"
          description="Control y registro de inicio y cierre de sesión de todos los usuarios."
          icon={<LuLogIn className="size-8" />}
          breadcrumbs={[
            { label: 'Monitoreo', active: false },
            { label: 'Sesiones', active: true }
          ]}
        />

        {/* Filter Bar */}
        <FilterBar
          searchTerm={searchTerm}
          onSearchChange={setSearchTerm}
          searchPlaceholder="Buscar por usuario, email o IP..."
          onClear={() => setSearchTerm('')}
        />

        {/* Table Card */}
        <TableCard
          title="Historial de Accesos"
          icon={<LuLogIn className="size-4 text-gray-500" />}
        >
          <Table>
            <TableHeader className="bg-[#f8fafc]">
              <TableRow>
                <TableHead>USUARIO</TableHead>
                <TableHead>EMPRESA / SUCURSAL</TableHead>
                <TableHead>IP / NAVEGADOR</TableHead>
                <TableHead>ENTRADA (LOGIN)</TableHead>
                <TableHead>SALIDA (LOGOUT)</TableHead>
                <TableHead className="text-center">ESTADO</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody className="bg-white">
              {loading && data.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={6} className="h-24 text-center">
                    <div className="flex justify-center">
                      <div className="animate-spin size-6 border-[3px] border-primary border-t-transparent rounded-full"></div>
                    </div>
                  </TableCell>
                </TableRow>
              ) : data.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={6} className="h-24 text-center text-gray-500">
                    No se encontraron registros de sesiones.
                  </TableCell>
                </TableRow>
              ) : (
                data.map((session) => (
                  <TableRow key={session.id} className="hover:bg-[#f8fafc]/50 transition-colors">
                    <TableCell className="whitespace-nowrap">
                      <div className="flex items-center">
                        <div className="shrink-0 size-8 rounded-full bg-[#dbeafe] text-[#1e40af] flex items-center justify-center font-bold text-sm shadow-sm">
                          {session.user?.name?.charAt(0).toUpperCase()}
                        </div>
                        <div className="ml-3">
                          <div className="text-sm font-semibold text-gray-900">{session.user?.name}</div>
                          <div className="text-xs text-gray-500">{session.user?.email}</div>
                        </div>
                      </div>
                    </TableCell>
                    
                    <TableCell className="whitespace-nowrap">
                      <div className="text-sm font-medium text-gray-700">{session.empresa?.razon_social || '-'}</div>
                      <div className="text-xs text-gray-500 mt-0.5">{session.sucursal?.nombre || '-'}</div>
                    </TableCell>
                    
                    <TableCell>
                      <div className="text-sm font-medium text-gray-900">{session.ip_address}</div>
                      <div className="text-[11px] text-gray-500 truncate max-w-[200px]" title={session.user_agent}>{session.user_agent}</div>
                    </TableCell>
                    
                    <TableCell className="whitespace-nowrap">
                      <div className="flex items-center gap-2">
                        <LuLogIn className="text-emerald-500 size-4" />
                        <span className="text-sm font-medium text-gray-700">{formatDate(session.login_at)}</span>
                      </div>
                    </TableCell>
                    
                    <TableCell className="whitespace-nowrap">
                      <div className="flex items-center gap-2">
                        <LuLogOut className="text-rose-500 size-4" />
                        <span className="text-sm font-medium text-gray-700">{formatDate(session.logout_at)}</span>
                      </div>
                    </TableCell>
                    
                    <TableCell className="text-center">
                      {!session.logout_at ? (
                        <span className="px-2.5 py-1 inline-flex text-xs font-bold rounded-md bg-emerald-50 text-emerald-600 border border-emerald-100">
                          EN LÍNEA
                        </span>
                      ) : (
                        <span className="px-2.5 py-1 inline-flex text-xs font-bold rounded-md bg-gray-50 text-gray-500 border border-gray-200">
                          DESCONECTADO
                        </span>
                      )}
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>

          {/* Pagination */}
          {totalPages > 1 && (
            <div className="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-white rounded-b-xl">
              <span className="text-sm text-gray-500 font-medium">Página {page} de {totalPages}</span>
              <div className="flex gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  disabled={page === 1}
                  onClick={() => fetchSessions(page - 1, searchTerm)}
                >
                  Anterior
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  disabled={page === totalPages}
                  onClick={() => fetchSessions(page + 1, searchTerm)}
                >
                  Siguiente
                </Button>
              </div>
            </div>
          )}
        </TableCard>
      </main>
    </>
  );
};

export default MonitoreoSesiones;
