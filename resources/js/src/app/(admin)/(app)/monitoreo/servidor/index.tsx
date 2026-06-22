import { useState, useEffect } from 'react';
import PageMetaData from '@/components/PageMeta';
import axios from '@/lib/axios';
import ApexChartClient from '@/components/client-wrapper/ApexChartClient';
import ModuleHeader from '@/components/ui/ModuleHeader';
import { LuServer, LuCpu, LuMemoryStick, LuHardDrive, LuSettings, LuActivity } from 'react-icons/lu';

const MonitoreoServidor = () => {
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  const fetchMetrics = async () => {
    try {
      setLoading(true);
      const response = await axios.get('/api/admin/monitoreo/server');
      setData(response.data.data);
    } catch (error) {
      console.error('Error fetching server metrics', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchMetrics();
    const interval = setInterval(fetchMetrics, 30000); // 30s
    return () => clearInterval(interval);
  }, []);

  const formatBytes = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  return (
    <>
      <PageMetaData title="Monitoreo de Servidor" />
      <main className="space-y-6">
        <ModuleHeader
          title="Monitoreo de Servidor"
          description="Visualiza en tiempo real el estado de CPU, Memoria RAM, Disco Duro y Entorno."
          icon={<LuServer className="size-8" />}
          breadcrumbs={[
            { label: 'Monitoreo', active: false },
            { label: 'Servidor', active: true }
          ]}
        />

        {loading && !data ? (
          <div className="flex justify-center p-10">
            <div className="animate-spin size-8 border-[3px] border-primary border-t-transparent rounded-full"></div>
          </div>
        ) : (
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade-in">
            
            {/* CPU */}
            <div className="bg-white dark:bg-default-50 border border-default-200 rounded-xl p-6 shadow-sm flex flex-col items-center">
              <div className="w-full flex justify-between items-center mb-2">
                <h3 className="text-default-700 font-semibold flex items-center gap-2"><LuCpu className="text-primary" size={20} /> CPU</h3>
              </div>
              <div className="-my-4">
                <ApexChartClient 
                  type="radialBar" 
                  height={250} 
                  series={[data?.cpu_load_percent || 0]}
                  getOptions={() => ({
                    chart: { type: 'radialBar' },
                    plotOptions: {
                      radialBar: {
                        hollow: { size: '60%' },
                        dataLabels: {
                          name: { show: false },
                          value: {
                            fontSize: '24px',
                            fontWeight: 'bold',
                            formatter: (val) => `${val}%`
                          }
                        }
                      }
                    },
                    colors: [data?.cpu_load_percent > 80 ? '#ef4444' : '#3b82f6'],
                    labels: ['CPU'],
                  })}
                />
              </div>
              <p className="text-sm text-default-500 mt-2 text-center bg-default-100 px-4 py-1.5 rounded-full w-full truncate">
                {data?.os}
              </p>
            </div>

            {/* RAM */}
            <div className="bg-white dark:bg-default-50 border border-default-200 rounded-xl p-6 shadow-sm flex flex-col items-center">
              <div className="w-full flex justify-between items-center mb-2">
                <h3 className="text-default-700 font-semibold flex items-center gap-2"><LuMemoryStick className="text-warning" size={20} /> Memoria RAM</h3>
              </div>
              <div className="-my-4">
                <ApexChartClient 
                  type="radialBar" 
                  height={250} 
                  series={[data?.ram_usage_percent || 0]}
                  getOptions={() => ({
                    chart: { type: 'radialBar' },
                    plotOptions: {
                      radialBar: {
                        hollow: { size: '60%' },
                        dataLabels: {
                          name: { show: false },
                          value: {
                            fontSize: '24px',
                            fontWeight: 'bold',
                            formatter: (val) => `${val}%`
                          }
                        }
                      }
                    },
                    colors: [data?.ram_usage_percent > 85 ? '#ef4444' : '#f59e0b'],
                    labels: ['RAM'],
                  })}
                />
              </div>
              <p className="text-sm text-default-500 mt-2 text-center bg-default-100 px-4 py-1.5 rounded-full w-full">
                {formatBytes(data?.ram_total_bytes - data?.ram_free_bytes)} / {formatBytes(data?.ram_total_bytes)}
              </p>
            </div>

            {/* Disk */}
            <div className="bg-white dark:bg-default-50 border border-default-200 rounded-xl p-6 shadow-sm flex flex-col items-center">
              <div className="w-full flex justify-between items-center mb-2">
                <h3 className="text-default-700 font-semibold flex items-center gap-2"><LuHardDrive className="text-success" size={20} /> Disco Duro</h3>
              </div>
              <div className="-my-4">
                <ApexChartClient 
                  type="radialBar" 
                  height={250} 
                  series={[data?.disk_usage_percent || 0]}
                  getOptions={() => ({
                    chart: { type: 'radialBar' },
                    plotOptions: {
                      radialBar: {
                        hollow: { size: '60%' },
                        dataLabels: {
                          name: { show: false },
                          value: {
                            fontSize: '24px',
                            fontWeight: 'bold',
                            formatter: (val) => `${val}%`
                          }
                        }
                      }
                    },
                    colors: [data?.disk_usage_percent > 90 ? '#ef4444' : '#10b981'],
                    labels: ['Disco'],
                  })}
                />
              </div>
              <p className="text-sm text-default-500 mt-2 text-center bg-default-100 px-4 py-1.5 rounded-full w-full">
                Libre: {formatBytes(data?.disk_free_bytes)} / Total: {formatBytes(data?.disk_total_bytes)}
              </p>
            </div>

            {/* Detailed Server & PHP Info */}
            <div className="bg-white dark:bg-default-50 border border-default-200 rounded-xl p-6 shadow-sm lg:col-span-3">
              <div className="flex items-center gap-2 mb-6 pb-4 border-b border-default-200">
                <LuSettings size={22} className="text-primary" />
                <h3 className="text-lg font-semibold text-default-800">Información del Entorno (PHP & Servidor)</h3>
              </div>

              <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
                <div>
                  <p className="text-sm font-medium text-default-500 mb-1">Versión PHP</p>
                  <p className="text-base font-semibold text-default-800">{data?.php_version}</p>
                </div>
                <div>
                  <p className="text-sm font-medium text-default-500 mb-1">Versión Laravel</p>
                  <p className="text-base font-semibold text-default-800">{data?.laravel_version}</p>
                </div>
                <div>
                  <p className="text-sm font-medium text-default-500 mb-1">Servidor Web</p>
                  <p className="text-base font-semibold text-default-800 truncate" title={data?.server_software}>{data?.server_software}</p>
                </div>
                <div>
                  <p className="text-sm font-medium text-default-500 mb-1">Memoria Usada PHP</p>
                  <p className="text-base font-semibold text-default-800">{formatBytes(data?.php_memory_usage_bytes || 0)}</p>
                </div>
                <div>
                  <p className="text-sm font-medium text-default-500 mb-1">Límite Memoria (limit)</p>
                  <p className="text-base font-semibold text-default-800">{data?.php_memory_limit}</p>
                </div>
                <div>
                  <p className="text-sm font-medium text-default-500 mb-1">Tiempo Max (exec)</p>
                  <p className="text-base font-semibold text-default-800">{data?.php_max_execution_time} s</p>
                </div>
                <div>
                  <p className="text-sm font-medium text-default-500 mb-1">Upload Max</p>
                  <p className="text-base font-semibold text-default-800">{data?.php_upload_max_filesize}</p>
                </div>
                <div>
                  <p className="text-sm font-medium text-default-500 mb-1">Post Max</p>
                  <p className="text-base font-semibold text-default-800">{data?.php_post_max_size}</p>
                </div>
              </div>
            </div>

          </div>
        )}
      </main>
    </>
  );
};

export default MonitoreoServidor;
