import { useEffect, useState } from 'react';
import axios from 'axios';
import PageBreadcrumb from '@/components/PageBreadcrumb';
import PageMeta from '@/components/PageMeta';
import { LuUsers, LuShield, LuLogIn, LuTriangleAlert } from 'react-icons/lu';

const Dashboard = () => {
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
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

  return (
    <>
      <PageMeta title="Dashboard" description="Panel de control principal" />
      <PageBreadcrumb title="Dashboard" subName="Inicio" />

      {loading ? (
        <div className="flex items-center justify-center h-64">
          <div className="animate-spin inline-block w-8 h-8 border-[3px] border-current border-t-transparent text-primary rounded-full" role="status" aria-label="loading">
            <span className="sr-only">Cargando...</span>
          </div>
        </div>
      ) : (
        <div className="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-6">
          <div className="card">
            <div className="card-body">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-gray-500 dark:text-gray-400 mb-2">Total Usuarios</p>
                  <h4 className="text-2xl font-semibold">{data?.metrics?.users || 0}</h4>
                </div>
                <div className="h-12 w-12 bg-primary/10 text-primary flex items-center justify-center rounded-lg">
                  <LuUsers className="text-2xl" />
                </div>
              </div>
            </div>
          </div>

          <div className="card">
            <div className="card-body">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-gray-500 dark:text-gray-400 mb-2">Total Roles</p>
                  <h4 className="text-2xl font-semibold">{data?.metrics?.roles || 0}</h4>
                </div>
                <div className="h-12 w-12 bg-teal-500/10 text-teal-500 flex items-center justify-center rounded-lg">
                  <LuShield className="text-2xl" />
                </div>
              </div>
            </div>
          </div>

          <div className="card">
            <div className="card-body">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-gray-500 dark:text-gray-400 mb-2">Accesos Hoy</p>
                  <h4 className="text-2xl font-semibold">{data?.metrics?.logins_today || 0}</h4>
                </div>
                <div className="h-12 w-12 bg-indigo-500/10 text-indigo-500 flex items-center justify-center rounded-lg">
                  <LuLogIn className="text-2xl" />
                </div>
              </div>
            </div>
          </div>

          <div className="card">
            <div className="card-body">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-gray-500 dark:text-gray-400 mb-2">Intentos Fallidos Hoy</p>
                  <h4 className="text-2xl font-semibold">{data?.metrics?.failed_logins_today || 0}</h4>
                </div>
                <div className="h-12 w-12 bg-red-500/10 text-red-500 flex items-center justify-center rounded-lg">
                  <LuTriangleAlert className="text-2xl" />
                </div>
              </div>
            </div>
          </div>

          <div className="lg:col-span-4 col-span-1 mt-6">
            <div className="card">
              <div className="card-header border-b border-gray-200 dark:border-gray-700">
                <h4 className="card-title">Accesos Recientes al Sistema</h4>
              </div>
              <div className="card-body p-0">
                <div className="overflow-x-auto">
                  <table className="w-full text-sm text-left">
                    <thead className="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                      <tr>
                        <th className="px-6 py-3 font-semibold">Usuario / Intento</th>
                        <th className="px-6 py-3 font-semibold">Descripción</th>
                        <th className="px-6 py-3 font-semibold">IP</th>
                        <th className="px-6 py-3 font-semibold">Estado</th>
                        <th className="px-6 py-3 font-semibold">Fecha</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                      {data?.recent_activity?.length > 0 ? (
                        data.recent_activity.map((activity: any) => (
                          <tr key={activity.id} className="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td className="px-6 py-4">{activity.causer_name}</td>
                            <td className="px-6 py-4">{activity.description}</td>
                            <td className="px-6 py-4">{activity.ip_address}</td>
                            <td className="px-6 py-4">
                              <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                activity.status === 'success' ? 'bg-teal-500/10 text-teal-500' : 
                                activity.status === 'failed' ? 'bg-red-500/10 text-red-500' : 'bg-indigo-500/10 text-indigo-500'
                              }`}>
                                {activity.status === 'success' ? 'Éxito' : activity.status === 'failed' ? 'Fallido' : 'Logout'}
                              </span>
                            </td>
                            <td className="px-6 py-4">{activity.created_at}</td>
                          </tr>
                        ))
                      ) : (
                        <tr>
                          <td colSpan={5} className="px-6 py-4 text-center text-gray-500">No hay actividad reciente</td>
                        </tr>
                      )}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
};

export default Dashboard;
