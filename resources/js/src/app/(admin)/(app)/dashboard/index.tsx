import { useEffect, useState } from 'react';
import axios from '@/lib/axios';
import PageMeta from '@/components/PageMeta';
import { LuUsers, LuArrowRightLeft, LuLogIn, LuLogOut, LuClock, LuLayoutDashboard } from 'react-icons/lu';
import ReactApexChart from 'react-apexcharts';
import type { ApexOptions } from 'apexcharts';

import ModuleHeader from '@/components/ui/ModuleHeader';
import StatsGrid from '@/components/ui/StatsGrid';

const Dashboard = () => {
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
    const fetchDashboard = async () => {
      try {
        const response = await axios.get('/api/dashboard');
        setData(response.data);
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchDashboard();
  }, []);

  const chartOptions: any = {
    chart: {
      type: 'area',
      height: 350,
      toolbar: { show: false },
      zoom: { enabled: false },
      fontFamily: 'Inter, sans-serif',
    },
    colors: ['#10b981', '#0ea5e9'], // Emerald (Entradas), Sky (Salidas)
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: {
      categories: data?.chart_data?.labels || [],
      axisBorder: { show: false },
      axisTicks: { show: false },
      labels: { style: { colors: '#64748b' } },
    },
    yaxis: {
      labels: { style: { colors: '#64748b' } },
    },
    legend: { position: 'top', horizontalAlign: 'right' },
    grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
    fill: {
      type: 'gradient',
      gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] }
    },
  };

  const chartSeries = [
    { name: 'Entradas', data: data?.chart_data?.entradas || [] },
    { name: 'Salidas', data: data?.chart_data?.salidas || [] },
  ];

  const statsItems = [
    {
      label: 'Total Estudiantes',
      value: data?.metrics?.total_estudiantes || 0,
      icon: <LuUsers className="size-6" />,
      iconBgClass: 'bg-indigo-50 text-indigo-500'
    },
    {
      label: 'Accesos de Hoy',
      value: data?.metrics?.accesos_hoy || 0,
      icon: <LuArrowRightLeft className="size-6" />,
      iconBgClass: 'bg-purple-50 text-purple-500'
    },
    {
      label: 'Entradas (Hoy)',
      value: data?.metrics?.entradas_hoy || 0,
      icon: <LuLogIn className="size-6" />,
      iconBgClass: 'bg-emerald-50 text-emerald-500'
    },
    {
      label: 'Salidas (Hoy)',
      value: data?.metrics?.salidas_hoy || 0,
      icon: <LuLogOut className="size-6" />,
      iconBgClass: 'bg-sky-50 text-sky-500'
    }
  ];

  return (
    <>
      <PageMeta title="Dashboard" description="Panel de control principal" />
      
      <main className="space-y-6">
        <ModuleHeader
          title="Panel Operativo"
          description="Monitoreo de Estudiantes y Control de Accesos"
          icon={<LuLayoutDashboard className="size-8" />}
          breadcrumbs={[{ label: 'Dashboard', active: true }]}
        />

        {loading ? (
          <div className="flex items-center justify-center h-64">
            <div className="animate-spin inline-block w-8 h-8 border-[3px] border-current border-t-transparent text-indigo-600 rounded-full" role="status">
              <span className="sr-only">Cargando...</span>
            </div>
          </div>
        ) : (
          <>
            <StatsGrid stats={statsItems} />

            <div className="grid lg:grid-cols-4 grid-cols-1 gap-6">
              {/* Chart Section */}
              <div className="lg:col-span-3 col-span-1">
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 h-full">
                  <div className="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                    <h4 className="text-lg font-bold text-gray-900">Actividad Semanal</h4>
                    <span className="text-sm text-gray-500">Últimos 7 días</span>
                  </div>
                  <div className="p-4">
                    {mounted && (
                      <ReactApexChart options={chartOptions} series={chartSeries} type="area" height={350} />
                    )}
                  </div>
                </div>
              </div>

              {/* Live Feed Section */}
              <div className="lg:col-span-1 col-span-1">
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 h-full flex flex-col">
                  <div className="px-6 py-5 border-b border-gray-100">
                    <h4 className="text-lg font-bold text-gray-900 flex items-center gap-2">
                      <LuClock className="text-indigo-500" /> Feed en Vivo
                    </h4>
                  </div>
                  <div className="p-4 flex-1 overflow-y-auto max-h-[400px]">
                    <div className="space-y-4">
                      {data?.recent_activity?.length > 0 ? (
                        data.recent_activity.map((activity: any) => (
                          <div key={activity.id} className="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
                            {/* Avatar */}
                            <div className="size-10 rounded-full overflow-hidden bg-gray-100 shrink-0 flex items-center justify-center border border-gray-200">
                              {activity.foto_url ? (
                                <img src={activity.foto_url} alt="avatar" className="w-full h-full object-cover" />
                              ) : (
                                <span className="text-xs font-bold text-gray-400">
                                  {activity.estudiante_nombre?.charAt(0)}
                                </span>
                              )}
                            </div>
                            
                            {/* Info */}
                            <div className="flex-1 min-w-0">
                              <p className="text-sm font-bold text-gray-900 truncate">{activity.estudiante_nombre}</p>
                              <div className="flex items-center gap-2 mt-0.5">
                                <span className={`inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${
                                  activity.tipo === 'entrada' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700'
                                }`}>
                                  {activity.tipo}
                                </span>
                                <span className="text-xs text-gray-500 truncate">{activity.grado}</span>
                              </div>
                            </div>

                            {/* Time */}
                            <div className="text-right shrink-0">
                              <p className="text-xs font-medium text-gray-900">{activity.hora}</p>
                              <p className="text-[10px] text-gray-500 mt-0.5">{activity.fecha}</p>
                            </div>
                          </div>
                        ))
                      ) : (
                        <div className="text-center py-12 text-gray-500">
                          <LuClock className="size-8 mx-auto mb-3 text-gray-300" />
                          <p>No hay accesos recientes</p>
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </>
        )}
      </main>
    </>
  );
};

export default Dashboard;
