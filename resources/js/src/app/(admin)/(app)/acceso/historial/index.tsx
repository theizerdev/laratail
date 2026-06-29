import React, { useState, useEffect } from 'react';
import axios from '@/lib/axios';
import PageMeta from '@/components/PageMeta';
import { 
  LuArrowRightLeft, 
  LuCalendar,
  LuClock,
  LuCheck
} from 'react-icons/lu';

// Reusable UI components
import ModuleHeader from '@/components/ui/ModuleHeader';
import StatsGrid from '@/components/ui/StatsGrid';
import FilterBar from '@/components/ui/FilterBar';
import TableCard from '@/components/ui/TableCard';

import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';

export default function HistorialAccesos() {
  const [accesos, setAccesos] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  
  const [fechaFiltro, setFechaFiltro] = useState(() => {
    const today = new Date();
    return today.toISOString().split('T')[0];
  });
  
  // Pagination
  const [currentPage, setCurrentPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [totalRecords, setTotalRecords] = useState(0);

  // Filters
  const [searchTerm, setSearchTerm] = useState('');

  const fetchHistorial = async (page = 1, fecha = fechaFiltro, search = searchTerm) => {
    setLoading(true);
    try {
      const res = await axios.get('/api/acceso/historial', {
        params: {
          page,
          fecha,
          search
        }
      });
      setAccesos(res.data.data || []);
      setCurrentPage(res.data.current_page || 1);
      setLastPage(res.data.last_page || 1);
      setTotalRecords(res.data.total || 0);
    } catch (error) {
      console.error('Error fetching accesos', error);
      setAccesos([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchHistorial(currentPage, fechaFiltro, searchTerm);
  }, [currentPage, fechaFiltro]);

  useEffect(() => {
    const delayDebounceFn = setTimeout(() => {
      if(currentPage !== 1) {
          setCurrentPage(1);
      } else {
          fetchHistorial(1, fechaFiltro, searchTerm);
      }
    }, 500);

    return () => clearTimeout(delayDebounceFn);
  }, [searchTerm]);

  const handleClearFilters = () => {
    setSearchTerm('');
    const today = new Date().toISOString().split('T')[0];
    setFechaFiltro(today);
  };

  const handleExport = () => {
    alert("Función de exportar en desarrollo.");
  };

  const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleTimeString('es-VE', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: true
    });
  };

  const formatEstadia = (minutos: number | null | undefined) => {
    if (minutos === null || minutos === undefined) return '-';
    if (minutos < 60) return `${minutos} m`;
    const horas = Math.floor(minutos / 60);
    const min = minutos % 60;
    return `${horas}h ${min}m`;
  };

  const entradasCount = accesos.filter(a => a.tipo === 'entrada').length;
  const salidasCount = accesos.filter(a => a.tipo === 'salida').length;

  const statsItems = [
    {
      label: 'Accesos del Día',
      value: totalRecords,
      icon: <LuArrowRightLeft className="size-6" />,
      iconBgClass: 'bg-indigo-50 text-indigo-500'
    },
    {
      label: 'Entradas',
      value: entradasCount,
      icon: <LuCheck className="size-6" />,
      iconBgClass: 'bg-emerald-50 text-emerald-500'
    },
    {
      label: 'Salidas',
      value: salidasCount,
      icon: <LuClock className="size-6" />,
      iconBgClass: 'bg-sky-50 text-sky-500'
    }
  ];

  return (
    <>
      <PageMeta title="Historial de Accesos" />
      <main className="space-y-6">
        
        <ModuleHeader
          title="Historial de Accesos"
          description="Registro completo de entradas y salidas de la institución."
          icon={<LuArrowRightLeft className="size-8" />}
          breadcrumbs={[{ label: 'Control de Acceso', active: false }, { label: 'Historial', active: true }]}
        />

        <StatsGrid stats={statsItems} />

        {/* Custom Filter Bar with Date Picker */}
        <div className="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-4 items-center justify-between">
          <div className="flex flex-1 items-center gap-4 w-full">
            <FilterBar
              searchTerm={searchTerm}
              onSearchChange={setSearchTerm}
              searchPlaceholder="Buscar estudiante..."
              onClear={handleClearFilters}
              onExport={handleExport}
            />
            
            <div className="relative w-full md:w-64 shrink-0">
              <LuCalendar className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
              <input
                type="date"
                value={fechaFiltro}
                onChange={(e) => setFechaFiltro(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
              />
            </div>
          </div>
        </div>

        <TableCard
          title="Registros de Acceso"
          icon={<LuArrowRightLeft className="size-4 text-gray-500" />}
        >
          <Table>
            <TableHeader className="bg-[#f8fafc]">
              <TableRow>
                <TableHead className="w-[80px]">Foto</TableHead>
                <TableHead>ESTUDIANTE</TableHead>
                <TableHead>DOCUMENTO</TableHead>
                <TableHead>GRADO</TableHead>
                <TableHead>HORA</TableHead>
                <TableHead>TIPO</TableHead>
                <TableHead>MÉTODO</TableHead>
                <TableHead>ESTADÍA</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody className="bg-white">
              {loading ? (
                <TableRow>
                  <TableCell colSpan={7} className="h-24 text-center text-sm text-gray-400">Cargando historial...</TableCell>
                </TableRow>
              ) : accesos.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={7} className="h-24 text-center text-sm text-gray-400">No hay registros de acceso para esta fecha</TableCell>
                </TableRow>
              ) : (
                accesos.map((acceso) => (
                  <TableRow key={acceso.id} className="hover:bg-[#f8fafc]/50 transition-colors">
                    <TableCell>
                      <div className="size-10 rounded-full overflow-hidden border border-gray-100 bg-gray-50 flex items-center justify-center">
                        {acceso.estudiante?.foto_url ? (
                          <img src={acceso.estudiante.foto_url} alt="Foto" className="w-full h-full object-cover" />
                        ) : (
                          <span className="text-xs font-bold text-gray-400">
                            {acceso.estudiante?.nombre?.charAt(0)}{acceso.estudiante?.apellido?.charAt(0)}
                          </span>
                        )}
                      </div>
                    </TableCell>
                    <TableCell className="font-medium text-gray-700">
                      {acceso.estudiante?.nombre} {acceso.estudiante?.apellido}
                    </TableCell>
                    <TableCell className="text-gray-500">
                      {acceso.estudiante?.dni || '-'}
                    </TableCell>
                    <TableCell className="text-gray-500">
                      {acceso.estudiante?.grado} {acceso.estudiante?.seccion && `- ${acceso.estudiante.seccion}`}
                    </TableCell>
                    <TableCell className="font-medium text-gray-700">
                      {formatTime(acceso.fecha_hora)}
                    </TableCell>
                    <TableCell>
                      <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                        acceso.tipo === 'entrada' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700'
                      }`}>
                        {acceso.tipo === 'entrada' ? 'Entrada' : 'Salida'}
                      </span>
                    </TableCell>
                    <TableCell className="text-gray-500 capitalize">
                      {acceso.metodo}
                    </TableCell>
                    <TableCell className="font-medium text-gray-700">
                      {acceso.tipo === 'salida' ? (
                        <span className="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-amber-50 text-amber-700 text-xs font-semibold border border-amber-100">
                          <LuClock className="size-3.5" />
                          {formatEstadia(acceso.estadia_minutos)}
                        </span>
                      ) : (
                        '-'
                      )}
                    </TableCell>
                  </TableRow>
                ))
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
    </>
  );
}
