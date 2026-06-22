import { useState, useEffect } from 'react';
import PageMetaData from '@/components/PageMeta';
import axios from '@/lib/axios';
import ExportImportTab from './ExportImportTab';
import ApexChartClient from '@/components/client-wrapper/ApexChartClient';
import ModuleHeader from '@/components/ui/ModuleHeader';
import { LuDatabase, LuHardDrive, LuActivity, LuClock, LuTable } from 'react-icons/lu';

const MonitoreoDatabase = () => {
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [activeMainTab, setActiveMainTab] = useState<'resumen' | 'export'>('resumen');

  const fetchMetrics = async () => {
    try {
      setLoading(true);
      const response = await axios.get('/api/admin/monitoreo/database');
      setData(response.data.data);
    } catch (error) {
      console.error('Error fetching db metrics', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchMetrics();
    const interval = setInterval(fetchMetrics, 30000); // 30s
    return () => clearInterval(interval);
  }, []);

  return (
    <>
      <PageMetaData title="Monitoreo de Base de Datos" />
      <main className="space-y-6">
        <ModuleHeader
          title="Monitoreo de Base de Datos"
          description="Gestión, métricas, y operaciones de exportación/importación de la base de datos."
          icon={<LuDatabase className="size-8" />}
          breadcrumbs={[
            { label: 'Monitoreo', active: false },
            { label: 'Base de Datos', active: true }
          ]}
        />

        {loading && !data ? (
          <div className="flex justify-center p-10">
            <div className="animate-spin size-8 border-[3px] border-primary border-t-transparent rounded-full"></div>
          </div>
        ) : (
          <>
            {/* Tabs Selector */}
            <div className="flex bg-white dark:bg-default-50 border border-default-200 rounded-xl overflow-hidden p-1 gap-2 mb-6">
              <button 
                className={`flex-1 py-3 text-sm font-semibold rounded-lg transition-colors flex items-center justify-center gap-2 ${activeMainTab === 'resumen' ? 'bg-primary text-white' : 'hover:bg-default-100 text-default-600'}`}
                onClick={() => setActiveMainTab('resumen')}
              >
                Resumen de Métricas
              </button>
              <button 
                className={`flex-1 py-3 text-sm font-semibold rounded-lg transition-colors flex items-center justify-center gap-2 ${activeMainTab === 'export' ? 'bg-primary text-white' : 'hover:bg-default-100 text-default-600'}`}
                onClick={() => setActiveMainTab('export')}
              >
                Exportar e Importar BD
              </button>
            </div>

            {activeMainTab === 'resumen' ? (
              <div className="space-y-6 animate-fade-in mt-6">
                {/* Top Metrics */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                  <div className="bg-white dark:bg-default-50 border border-default-200 rounded-xl p-6 shadow-sm flex items-center gap-4">
                    <div className="bg-primary/10 text-primary p-4 rounded-lg"><LuHardDrive size={24} /></div>
                    <div>
                      <h3 className="text-default-500 text-sm font-medium mb-1">Tamaño Total</h3>
                      <div className="text-2xl font-bold text-primary">{data?.size_mb} MB</div>
                    </div>
                  </div>
                  
                  <div className="bg-white dark:bg-default-50 border border-default-200 rounded-xl p-6 shadow-sm flex items-center gap-4">
                    <div className="bg-warning/10 text-warning p-4 rounded-lg"><LuActivity size={24} /></div>
                    <div>
                      <h3 className="text-default-500 text-sm font-medium mb-1">Conexiones Activas</h3>
                      <div className="text-2xl font-bold text-warning">{data?.active_connections}</div>
                      <p className="text-xs text-default-400 mt-1">Máx: {data?.max_connections}</p>
                    </div>
                  </div>

                  <div className="bg-white dark:bg-default-50 border border-default-200 rounded-xl p-6 shadow-sm flex items-center gap-4">
                    <div className="bg-success/10 text-success p-4 rounded-lg"><LuDatabase size={24} /></div>
                    <div>
                      <h3 className="text-default-500 text-sm font-medium mb-1">Tablas en BD</h3>
                      <div className="text-2xl font-bold text-success">{data?.tables_count}</div>
                    </div>
                  </div>

                  <div className="bg-white dark:bg-default-50 border border-default-200 rounded-xl p-6 shadow-sm flex items-center gap-4">
                    <div className="bg-info/10 text-info p-4 rounded-lg"><LuClock size={24} /></div>
                    <div>
                      <h3 className="text-default-500 text-sm font-medium mb-1">Tiempo Activo</h3>
                      <div className="text-2xl font-bold text-info">
                        {Math.floor((data?.uptime_seconds || 0) / 3600)} hrs
                      </div>
                    </div>
                  </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                  {/* Chart: Top Tables by Size */}
                  <div className="lg:col-span-1 bg-white dark:bg-default-50 border border-default-200 rounded-xl p-6 shadow-sm">
                    <h3 className="text-lg font-semibold mb-4 text-default-800">Top 5 Tablas por Tamaño</h3>
                    {data?.top_tables && data.top_tables.length > 0 ? (
                      <ApexChartClient 
                        type="donut" 
                        height={300} 
                        series={data.top_tables.map((t: any) => t.size_mb)}
                        getOptions={() => ({
                          chart: { type: 'donut' },
                          labels: data.top_tables.map((t: any) => t.name),
                          dataLabels: { enabled: false },
                          legend: { position: 'bottom' },
                          tooltip: {
                            y: { formatter: (val) => `${val} MB` }
                          }
                        })}
                      />
                    ) : (
                      <p className="text-default-400 text-sm">No hay datos suficientes</p>
                    )}
                  </div>

                  {/* List: Recent Tables */}
                  <div className="lg:col-span-2 bg-white dark:bg-default-50 border border-default-200 rounded-xl p-6 shadow-sm">
                    <h3 className="text-lg font-semibold mb-4 text-default-800 flex items-center gap-2">
                      <LuTable /> Tablas Modificadas Recientemente
                    </h3>
                    <div className="overflow-x-auto">
                      <table className="min-w-full divide-y divide-default-200">
                        <thead>
                          <tr className="text-left text-xs font-semibold text-default-500 uppercase tracking-wider">
                            <th className="px-4 py-3">Tabla</th>
                            <th className="px-4 py-3">Motor</th>
                            <th className="px-4 py-3">Filas</th>
                            <th className="px-4 py-3">Tamaño</th>
                            <th className="px-4 py-3">Actualización</th>
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-default-200">
                          {data?.recent_tables?.map((t: any, idx: number) => (
                            <tr key={idx} className="hover:bg-default-50 transition-colors">
                              <td className="px-4 py-3 text-sm font-medium text-default-700">{t.name}</td>
                              <td className="px-4 py-3 text-sm text-default-500">
                                <span className="bg-default-100 text-default-700 px-2 py-1 rounded text-xs">{t.engine}</span>
                              </td>
                              <td className="px-4 py-3 text-sm text-default-500">{t.rows}</td>
                              <td className="px-4 py-3 text-sm text-default-500">{t.size_mb} MB</td>
                              <td className="px-4 py-3 text-sm text-default-500">{t.updated_at || t.created_at || '-'}</td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

              </div>
            ) : (
              <ExportImportTab tablesCount={data?.tables_count || 0} sizeMb={data?.size_mb || 0} />
            )}
          </>
        )}
      </main>
    </>
  );
};

export default MonitoreoDatabase;
