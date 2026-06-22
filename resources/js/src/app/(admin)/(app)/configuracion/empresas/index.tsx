import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import axios from '@/lib/axios';
import PageMeta from '@/components/PageMeta';
import {
  LuBuilding2,
  LuPencil,
  LuTrash2,
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

export default function EmpresasIndex() {
  const navigate = useNavigate();
  const [empresas, setEmpresas] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  // Interactive filters
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedEstado, setSelectedEstado] = useState('Todos');

  // Local state for active/inactive status toggles
  const [empresaStatuses, setEmpresaStatuses] = useState<Record<number, boolean>>({});

  // Dropdown list control
  const [activeDropdownId, setActiveDropdownId] = useState<number | null>(null);

  const fetchEmpresas = async () => {
    try {
      setLoading(true);
      const res = await axios.get('/api/admin/configuracion/empresas');
      const data = res.data;
      const fetched = Array.isArray(data) ? data : (Array.isArray(data?.data) ? data.data : []);
      setEmpresas(fetched);

      setEmpresaStatuses(prev => {
        const next = { ...prev };
        fetched.forEach((u: any) => {
          if (next[u.id] === undefined) {
            next[u.id] = u.status;
          }
        });
        return next;
      });
    } catch (error: any) {
      console.error('Error fetching empresas:', error);
      setEmpresas([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchEmpresas();
  }, []);

  useEffect(() => {
    const handleGlobalClick = () => setActiveDropdownId(null);
    window.addEventListener('click', handleGlobalClick);
    return () => window.removeEventListener('click', handleGlobalClick);
  }, []);

  const handleDelete = async (id: number) => {
    if (!confirm('¿Estás seguro de que deseas eliminar esta empresa?')) return;
    try {
      await axios.delete(`/api/admin/configuracion/empresas/${id}`);
      fetchEmpresas();
    } catch (error) {
      alert('Error eliminando empresa.');
    }
  };

  const handleToggleEstado = async (id: number) => {
    try {
      const res = await axios.put(`/api/admin/configuracion/empresas/${id}/status`);
      setEmpresaStatuses(prev => ({
        ...prev,
        [id]: res.data.status
      }));
    } catch (error) {
      console.error('Error updating status', error);
      alert('Error al actualizar estado');
    }
  };

  const handleClearFilters = () => {
    setSearchTerm('');
    setSelectedEstado('Todos');
  };

  const filteredEmpresas = empresas.filter(emp => {
    const matchesSearch =
      emp.razon_social.toLowerCase().includes(searchTerm.toLowerCase()) ||
      emp.documento.toLowerCase().includes(searchTerm.toLowerCase());

    const isEmpresaActive = empresaStatuses[emp.id] !== false;
    const matchesEstado =
      selectedEstado === 'Todos' ||
      (selectedEstado === 'Activas' && isEmpresaActive) ||
      (selectedEstado === 'Inactivas' && !isEmpresaActive);

    return matchesSearch && matchesEstado;
  });

  const handleExport = () => {
    if (filteredEmpresas.length === 0) {
      alert('No hay datos para exportar.');
      return;
    }
    const headers = ['#', 'Razón Social', 'Documento', 'Representante', 'Teléfono', 'Email', 'Estado', 'Fecha Registro'];
    const rows = filteredEmpresas.map((emp, index) => [
      filteredEmpresas.length - index,
      emp.razon_social,
      emp.documento,
      emp.representante_legal,
      emp.telefono,
      emp.email,
      (empresaStatuses[emp.id] !== false) ? 'Activo' : 'Inactivo',
      formatDate(emp.created_at)
    ]);

    const csvContent = "data:text/csv;charset=utf-8,\uFEFF"
      + [headers.join(','), ...rows.map(e => e.map(val => `"${String(val || '').replace(/"/g, '""')}"`).join(','))].join('\n');

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `listado_empresas_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  const totalEmpresas = empresas.length;
  const activeEmpresasCount = empresas.filter(u => empresaStatuses[u.id] !== false).length;
  const inactiveEmpresasCount = empresas.filter(u => empresaStatuses[u.id] === false).length;

  const statsItems = [
    {
      label: 'Total Empresas',
      value: totalEmpresas,
      icon: <LuBuilding2 className="size-6" />,
      iconBgClass: 'bg-blue-50 text-blue-500'
    },
    {
      label: 'Empresas Activas',
      value: activeEmpresasCount,
      icon: <LuCheck className="size-6 font-bold" />,
      iconBgClass: 'bg-emerald-50 text-[#10b981]'
    },
    {
      label: 'Empresas Inactivas',
      value: inactiveEmpresasCount,
      icon: <XCircleIcon className="size-6" />,
      iconBgClass: 'bg-rose-50 text-[#ef4444]'
    }
  ];

  return (
    <>
      <PageMeta title="Gestión de Empresas" />
      <main className="space-y-6">

        <ModuleHeader
          title="Empresas"
          description="Gestión de empresas e instituciones"
          icon={<LuBuilding2 className="size-8" />}
          breadcrumbs={[{ label: 'Configuración', active: false }, { label: 'Empresas', active: true }]}
          actionButton={{
            label: 'Nueva Empresa',
            onClick: () => navigate('/admin/configuracion/empresas/create')
          }}
        />

        <StatsGrid stats={statsItems} />

        <FilterBar
          searchTerm={searchTerm}
          onSearchChange={setSearchTerm}
          searchPlaceholder="Razón Social, RIF/NIT..."
          onClear={handleClearFilters}
          onExport={handleExport}
        >
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
              <LuChevronDown className="absolute right-3 top-3 text-gray-400 size-3.5 pointer-events-none" />
            </div>
          </div>
        </FilterBar>

        <TableCard
          title="Listado de empresas"
          icon={<LuBuilding2 className="size-4 text-gray-500" />}
        >
          <Table>
            <TableHeader className="bg-[#f8fafc]">
              <TableRow>
                <TableHead className="w-[50px]">#</TableHead>
                <TableHead>RAZÓN SOCIAL</TableHead>
                <TableHead>DOCUMENTO</TableHead>
                <TableHead>TELÉFONO</TableHead>
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
                  <TableCell colSpan={7} className="h-24 text-center text-sm text-gray-400">Cargando empresas...</TableCell>
                </TableRow>
              ) : filteredEmpresas.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={7} className="h-24 text-center text-sm text-gray-400">No se encontraron empresas</TableCell>
                </TableRow>
              ) : (
                filteredEmpresas.map((emp, index) => {
                  const isEmpresaActive = empresaStatuses[emp.id] !== false;
                  const indexNum = filteredEmpresas.length - index;

                  return (
                    <TableRow key={emp.id} className="hover:bg-[#f8fafc]/50 transition-colors">
                      <TableCell className="font-medium text-gray-500">
                        {indexNum}
                      </TableCell>

                      <TableCell>
                        <div className="flex items-center gap-3">
                          <div className="size-9 rounded-md bg-[#dbeafe] text-[#1e40af] flex items-center justify-center font-bold text-sm shadow-sm">
                            {emp.razon_social.charAt(0).toUpperCase()}
                          </div>
                          <span className="text-sm font-semibold text-gray-700">{emp.razon_social}</span>
                        </div>
                      </TableCell>

                      <TableCell className="text-gray-500">
                        {emp.documento}
                      </TableCell>

                      <TableCell className="text-gray-500">
                        {emp.telefono || '-'}
                      </TableCell>

                      <TableCell>
                        <label className="relative inline-flex items-center cursor-pointer select-none">
                          <input
                            type="checkbox"
                            className="sr-only peer"
                            checked={isEmpresaActive}
                            onChange={() => handleToggleEstado(emp.id)}
                          />
                          <div className="w-9 h-5 bg-gray-200 rounded-full peer peer-focus:ring-0 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#0f3d4c]"></div>
                        </label>
                      </TableCell>

                      <TableCell className="text-gray-500">
                        {formatDate(emp.created_at)}
                      </TableCell>

                      <TableCell className="text-right font-medium relative">
                        <Button
                          variant="outline"
                          size="icon"
                          onClick={(e) => {
                            e.stopPropagation();
                            setActiveDropdownId(activeDropdownId === emp.id ? null : emp.id);
                          }}
                          className="h-8 w-8 text-gray-500 hover:text-gray-700 hover:bg-gray-50"
                          title="Acciones"
                        >
                          <MoreVerticalIcon className="size-4" />
                        </Button>

                        {activeDropdownId === emp.id && (
                          <div className="absolute right-5 mt-1 w-32 bg-white rounded-lg shadow-lg border border-gray-100 z-50 py-1 text-left">
                            <Link
                              to={`/admin/configuracion/empresas/${emp.id}/edit`}
                              className="w-full px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50 flex items-center gap-2 font-medium"
                            >
                              <LuPencil className="size-3.5 text-indigo-500" />
                              Editar
                            </Link>
                            <button
                              onClick={() => handleDelete(emp.id)}
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
    </>
  );
}
