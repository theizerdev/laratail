import React from 'react';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import { LuX, LuHistory, LuUser, LuMonitor, LuClock, LuTag } from 'react-icons/lu';

interface AuditModalProps {
  isOpen: boolean;
  onClose: () => void;
  log: any;
}

const AuditModal: React.FC<AuditModalProps> = ({ isOpen, onClose, log }) => {
  if (!isOpen || !log) return null;

  const getEventName = (event: string) => {
    switch (event) {
      case 'created': return 'Creación';
      case 'updated': return 'Actualización';
      case 'deleted': return 'Eliminación';
      case 'restored': return 'Restauración';
      default: return event;
    }
  };

  const getEventColor = (event: string) => {
    switch (event) {
      case 'created': return 'bg-emerald-100 text-emerald-700';
      case 'updated': return 'bg-amber-100 text-amber-700';
      case 'deleted': return 'bg-rose-100 text-rose-700';
      case 'restored': return 'bg-blue-100 text-blue-700';
      default: return 'bg-gray-100 text-gray-700';
    }
  };

  return (
    <div className="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm animate-in fade-in duration-200">
      <div className="bg-white dark:bg-default-50 rounded-xl shadow-xl w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col">
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-default-200 bg-gray-50/50">
          <div className="flex items-center gap-3">
            <div className={`p-2 rounded-lg ${getEventColor(log.event)}`}>
              <LuHistory size={20} />
            </div>
            <div>
              <h3 className="text-lg font-semibold text-gray-900">Detalles de Auditoría</h3>
              <p className="text-xs text-gray-500">ID de Registro: #{log.id}</p>
            </div>
          </div>
          <button 
            onClick={onClose}
            className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
          >
            <LuX size={20} />
          </button>
        </div>

        {/* Body */}
        <div className="p-6 overflow-y-auto flex-1">
          {/* Main Info Grid */}
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div className="bg-gray-50 p-4 rounded-xl border border-gray-100">
              <div className="flex items-center gap-2 text-gray-500 mb-1">
                <LuClock size={16} />
                <span className="text-xs font-semibold uppercase tracking-wider">Fecha y Hora</span>
              </div>
              <div className="text-sm font-medium text-gray-900">
                {format(new Date(log.created_at), "dd MMM yyyy", { locale: es })}
              </div>
              <div className="text-xs text-gray-500">
                {format(new Date(log.created_at), "hh:mm:ss a", { locale: es })}
              </div>
            </div>

            <div className="bg-gray-50 p-4 rounded-xl border border-gray-100">
              <div className="flex items-center gap-2 text-gray-500 mb-1">
                <LuUser size={16} />
                <span className="text-xs font-semibold uppercase tracking-wider">Usuario</span>
              </div>
              <div className="text-sm font-medium text-indigo-600 truncate" title={log.properties?.usuario_nombre || 'Sistema'}>
                {log.properties?.usuario_nombre || 'Sistema'}
              </div>
              <div className="text-xs text-gray-500 truncate" title={log.properties?.usuario_email || ''}>
                {log.properties?.usuario_email || 'Automático'}
              </div>
            </div>

            <div className="bg-gray-50 p-4 rounded-xl border border-gray-100">
              <div className="flex items-center gap-2 text-gray-500 mb-1">
                <LuTag size={16} />
                <span className="text-xs font-semibold uppercase tracking-wider">Acción</span>
              </div>
              <div className="mt-1">
                <span className={`px-2 py-1 text-xs font-semibold rounded-md ${getEventColor(log.event)}`}>
                  {getEventName(log.event)}
                </span>
              </div>
            </div>

            <div className="bg-gray-50 p-4 rounded-xl border border-gray-100">
              <div className="flex items-center gap-2 text-gray-500 mb-1">
                <LuMonitor size={16} />
                <span className="text-xs font-semibold uppercase tracking-wider">IP / Agente</span>
              </div>
              <div className="text-sm font-medium text-gray-900 truncate">
                {log.properties?.ip_address || 'N/A'}
              </div>
              <div className="text-xs text-gray-500 truncate" title={log.properties?.user_agent}>
                {log.properties?.user_agent ? 'Web Browser' : 'Desconocido'}
              </div>
            </div>
          </div>

          {/* Details Section */}
          <div className="mb-6">
            <h4 className="text-sm font-semibold text-gray-900 mb-3 border-b pb-2">Descripción del Evento</h4>
            <div className="bg-blue-50/50 p-4 rounded-lg border border-blue-100 text-sm text-gray-700">
              {log.description}
            </div>
          </div>

          {/* Changes Tracking */}
          {log.event === 'updated' && log.properties?.attributes && log.properties?.old && (
            <div>
              <h4 className="text-sm font-semibold text-gray-900 mb-3 border-b pb-2">Modificaciones Detalladas</h4>
              <div className="border border-gray-200 rounded-lg overflow-hidden">
                <table className="min-w-full divide-y divide-gray-200 text-sm">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campo</th>
                      <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Anterior</th>
                      <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Nuevo</th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {Object.keys(log.properties.attributes).map(key => {
                      if (['created_at', 'updated_at', 'deleted_at'].includes(key)) return null;
                      
                      const oldVal = log.properties.old[key];
                      const newVal = log.properties.attributes[key];
                      
                      // Skip if values are exactly the same
                      if (oldVal === newVal) return null;

                      return (
                        <tr key={key} className="hover:bg-gray-50 transition-colors">
                          <td className="px-4 py-3 whitespace-nowrap font-medium text-gray-900">
                            {key}
                          </td>
                          <td className="px-4 py-3 text-red-600 line-through bg-red-50/30">
                            {oldVal === null ? 'null' : String(oldVal)}
                          </td>
                          <td className="px-4 py-3 text-emerald-600 font-medium bg-emerald-50/30">
                            {newVal === null ? 'null' : String(newVal)}
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {log.event === 'created' && log.properties?.attributes && (
            <div>
              <h4 className="text-sm font-semibold text-gray-900 mb-3 border-b pb-2">Datos Registrados</h4>
              <div className="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <pre className="text-xs text-gray-700 whitespace-pre-wrap font-mono">
                  {JSON.stringify(log.properties.attributes, null, 2)}
                </pre>
              </div>
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="px-6 py-4 border-t border-default-200 bg-gray-50/50 flex justify-end">
          <button
            onClick={onClose}
            className="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm"
          >
            Cerrar Detalles
          </button>
        </div>
      </div>
    </div>
  );
};

export default AuditModal;
