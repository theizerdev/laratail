import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';

interface DashboardStats {
  sent: number;
  delivered: number;
  failed: number;
  pending: number;
  total: number;
  today: number;
}

const WhatsAppCrm = () => {
  const [loading, setLoading] = useState(true);
  const [configLoading, setConfigLoading] = useState(false);
  const [actionLoading, setActionLoading] = useState(false);
  
  const [config, setConfig] = useState({
    empresa_id: '',
    empresa_name: '',
    api_key: '',
    api_url: 'http://82.165.213.124:8092',
  });

  const [empresas, setEmpresas] = useState<any[]>([]);

  const [connectionStatus, setConnectionStatus] = useState('disconnected');
  const [statusMessage, setStatusMessage] = useState<string | null>(null);
  const [phone, setPhone] = useState<string | null>(null);
  const [qrCodeData, setQrCodeData] = useState<string | null>(null);
  const [messages, setMessages] = useState<any[]>([]);
  
  const [stats, setStats] = useState<DashboardStats>({
    sent: 0, delivered: 0, failed: 0, pending: 0, total: 0, today: 0
  });

  const fetchConfig = async (empresaId?: string) => {
    try {
      const url = empresaId ? `/api/admin/integraciones/whatsapp/config?empresa_id=${empresaId}` : '/api/admin/integraciones/whatsapp/config';
      const response = await axios.get(url);
      setEmpresas(response.data.empresas || []);
      setConfig({
        empresa_id: response.data.empresa_id,
        empresa_name: response.data.empresa_name,
        api_key: response.data.api_key,
        api_url: response.data.api_url || 'http://82.165.213.124:8092',
      });
      // automatically fetch status for newly selected company
      if (empresaId) fetchStatus(response.data.empresa_id);
    } catch (error) {
      console.error('Error fetching config', error);
    }
  };

  const fetchStatus = async (empresaId?: string) => {
    try {
      const currentId = empresaId || config.empresa_id;
      const url = currentId ? `/api/admin/integraciones/whatsapp/status?empresa_id=${currentId}` : '/api/admin/integraciones/whatsapp/status';
      const response = await axios.get(url);
      const data = response.data;
      
      setConnectionStatus(data.status);
      setStatusMessage(data.message);
      setPhone(data.phone);
      setQrCodeData(data.qr);
      
      if (data.messages && Array.isArray(data.messages)) {
        setMessages(data.messages);
      } else {
        setMessages([]);
      }
      
      if (data.dashboard) {
        setStats({
          sent: data.dashboard.sent || 0,
          delivered: data.dashboard.delivered || 0,
          failed: data.dashboard.failed || 0,
          pending: data.dashboard.pending || 0,
          total: data.dashboard.total || 0,
          today: data.dashboard.today || 0
        });
      }
    } catch (error) {
      console.error('Error fetching status', error);
      setConnectionStatus('disconnected');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const init = async () => {
      await fetchConfig();
      await fetchStatus();
    };
    init();
  }, []);

  // Polling logic when connecting or connected
  useEffect(() => {
    let interval: NodeJS.Timeout;
    if (connectionStatus === 'connecting' || connectionStatus === 'qr_ready') {
      interval = setInterval(() => {
        if (config.empresa_id) fetchStatus(config.empresa_id);
      }, 3000);
    } else if (connectionStatus === 'connected') {
      interval = setInterval(() => {
        if (config.empresa_id) fetchStatus(config.empresa_id);
      }, 5000);
    }
    return () => {
      if (interval) clearInterval(interval);
    };
  }, [connectionStatus, config.empresa_id]);

  const saveConfig = async () => {
    setConfigLoading(true);
    try {
      await axios.post('/api/admin/integraciones/whatsapp/config', {
        empresa_id: config.empresa_id,
        api_key: config.api_key,
        api_url: config.api_url,
      });
      alert('Configuración guardada correctamente.');
      fetchStatus(config.empresa_id);
    } catch (error) {
      console.error('Error saving config', error);
      alert('Error al guardar configuración.');
    } finally {
      setConfigLoading(false);
    }
  };

  const handleAction = async (action: 'connect' | 'disconnect' | 'reconnect' | 'remove-session') => {
    setActionLoading(true);
    try {
      const response = await axios.post(`/api/admin/integraciones/whatsapp/${action}`, {
        empresa_id: config.empresa_id
      });
      if (response.data.success) {
        if (action === 'connect' || action === 'reconnect') {
          setConnectionStatus('connecting');
        } else {
          setConnectionStatus('disconnected');
          setPhone(null);
          setQrCodeData(null);
        }
      } else {
        alert(response.data.message || 'Error al ejecutar la acción.');
      }
    } catch (error) {
      console.error(`Error on ${action}`, error);
      alert(`Error al ejecutar ${action}.`);
    } finally {
      setActionLoading(false);
    }
  };

  const generateApiKey = () => {
    const array = new Uint8Array(16);
    window.crypto.getRandomValues(array);
    const key = Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
    setConfig({ ...config, api_key: key });
  };

  const renderStatusBadge = () => {
    switch (connectionStatus) {
      case 'connected': return <span className="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">● Conectado</span>;
      case 'connecting': return <span className="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">● Conectando...</span>;
      case 'qr_ready': return <span className="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">● QR Listo para escanear</span>;
      case 'service_unavailable': return <span className="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">● Servicio no disponible</span>;
      default: return <span className="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-medium">● Desconectado</span>;
    }
  };

  return (
    <div className="p-6 w-full flex flex-col pb-24">
      <div className="flex justify-between items-center mb-6">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <svg className="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
            WhatsApp CRM
          </h1>
          <p className="text-sm text-gray-500">Gestiona tu conexión, monitorea mensajes y revisa estadísticas en tiempo real.</p>
        </div>
        <Link to="/admin/integraciones" className="text-gray-500 hover:text-gray-700 flex items-center gap-1">
          &larr; Volver
        </Link>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        
        {/* LEFT COLUMN: Config & Connection State */}
        <div className="space-y-6 lg:col-span-1 xl:col-span-2">
          
          {/* Config Card */}
          <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 className="font-semibold text-gray-800 mb-4 flex items-center gap-2">
              <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
              Configuración
            </h3>
            
            <div className="space-y-4">
              <div>
                <label className="block text-sm text-gray-600 mb-1">Empresa</label>
                <select 
                  value={config.empresa_id}
                  onChange={(e) => fetchConfig(e.target.value)}
                  className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500"
                >
                  <option value="" disabled>Seleccione una empresa</option>
                  {empresas.map((emp) => (
                    <option key={emp.id} value={emp.id}>{emp.razon_social}</option>
                  ))}
                </select>
              </div>
              
              <div>
                <label className="block text-sm text-gray-600 mb-1">API Key</label>
                <div className="flex gap-2">
                  <input 
                    type="text" 
                    value={config.api_key}
                    onChange={(e) => setConfig({ ...config, api_key: e.target.value })}
                    className="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500"
                    placeholder="Ingrese API Key"
                  />
                  <button onClick={generateApiKey} className="bg-green-50 text-green-600 px-3 py-2 rounded-lg text-sm font-medium border border-green-100 hover:bg-green-100 transition">
                    Generar
                  </button>
                </div>
              </div>

              <div>
                <label className="block text-sm text-gray-600 mb-1">URL del Servidor</label>
                <input 
                  type="text" 
                  value={config.api_url}
                  onChange={(e) => setConfig({ ...config, api_url: e.target.value })}
                  className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500"
                />
              </div>

              <button 
                onClick={saveConfig} 
                disabled={configLoading}
                className="w-full bg-green-600 text-white rounded-lg py-2 text-sm font-semibold hover:bg-green-700 transition disabled:opacity-50"
              >
                {configLoading ? 'Guardando...' : 'Guardar Configuración'}
              </button>
            </div>
          </div>

          {/* Connection State Card */}
          <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div className="flex justify-between items-center mb-4">
              <h3 className="font-semibold text-gray-800 flex items-center gap-2">
                <svg className="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Estado de Conexión
              </h3>
              <button onClick={() => fetchStatus(config.empresa_id)} className="text-green-600 text-xs flex items-center gap-1 hover:underline">
                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Actualizar
              </button>
            </div>
            
            <div className="bg-gray-50 rounded-lg p-4 flex flex-col items-center justify-center min-h-[120px] mb-4 text-center">
              {loading ? (
                <div className="text-gray-400 text-sm">Cargando estado...</div>
              ) : (
                <>
                  <div className="mb-2 w-full flex justify-start">{renderStatusBadge()}</div>
                  {phone && (
                    <div className="flex items-center gap-2 text-gray-700 font-medium text-lg mt-2 w-full justify-start">
                      <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                      {phone ? `+${phone.split('@')[0].split(':')[0]}` : ''}
                    </div>
                  )}
                  {statusMessage && <p className="text-sm text-gray-500 mt-2 w-full text-left">{statusMessage}</p>}
                </>
              )}
            </div>

            {qrCodeData && (
              <div className="flex justify-center mb-4 p-2 bg-white border rounded-lg">
                <img src={qrCodeData} alt="WhatsApp QR Code" className="w-48 h-48" />
              </div>
            )}

            <div className="grid grid-cols-2 gap-2 mb-2">
              <button 
                onClick={() => handleAction('connect')} 
                disabled={actionLoading || connectionStatus === 'connected' || connectionStatus === 'connecting'}
                className="bg-green-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-green-700 disabled:opacity-50"
              >
                Conectar
              </button>
              <button 
                onClick={() => handleAction('disconnect')} 
                disabled={actionLoading || connectionStatus === 'disconnected'}
                className="bg-gray-200 text-gray-700 py-2 rounded-lg text-sm font-medium hover:bg-gray-300 disabled:opacity-50"
              >
                Desconectar
              </button>
            </div>
            <div className="grid grid-cols-2 gap-2 mb-2">
              <button 
                onClick={() => handleAction('reconnect')} 
                disabled={actionLoading || connectionStatus === 'disconnected'}
                className="bg-yellow-500 text-white py-2 rounded-lg text-sm font-medium hover:bg-yellow-600 disabled:opacity-50"
              >
                Reconectar
              </button>
              <button 
                onClick={() => handleAction('remove-session')} 
                disabled={actionLoading}
                className="bg-red-50 text-red-600 py-2 rounded-lg text-sm font-medium hover:bg-red-100 disabled:opacity-50"
              >
                Eliminar Sesion
              </button>
            </div>
          </div>
        </div>

        {/* RIGHT COLUMN: Stats & Recent Messages */}
        <div className="lg:col-span-3 xl:col-span-3 space-y-6 flex flex-col">
          
          <div className="flex justify-between items-center mb-2">
            <h3 className="font-semibold text-gray-800 flex items-center gap-2">
              <svg className="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
              Estadísticas en Tiempo Real
            </h3>
            <button onClick={() => fetchStatus(config.empresa_id)} className="text-green-600 text-xs flex items-center gap-1 hover:underline">
              <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
              Actualizar
            </button>
          </div>

          <div className="grid grid-cols-2 md:grid-cols-6 gap-3">
            <div className="bg-white border border-gray-100 rounded-xl p-4 text-center shadow-sm">
              <div className="text-2xl font-bold text-gray-800">{stats.total}</div>
              <div className="text-xs text-gray-500 mt-1 flex items-center justify-center gap-1">Total</div>
            </div>
            <div className="bg-white border border-gray-100 rounded-xl p-4 text-center shadow-sm">
              <div className="text-2xl font-bold text-green-600">{stats.sent}</div>
              <div className="text-xs text-green-600 mt-1 flex items-center justify-center gap-1">Enviados</div>
            </div>
            <div className="bg-white border border-gray-100 rounded-xl p-4 text-center shadow-sm">
              <div className="text-2xl font-bold text-blue-600">{stats.delivered}</div>
              <div className="text-xs text-blue-600 mt-1 flex items-center justify-center gap-1">Entregados</div>
            </div>
            <div className="bg-white border border-gray-100 rounded-xl p-4 text-center shadow-sm">
              <div className="text-2xl font-bold text-red-500">{stats.failed}</div>
              <div className="text-xs text-red-500 mt-1 flex items-center justify-center gap-1">Fallidos</div>
            </div>
            <div className="bg-white border border-gray-100 rounded-xl p-4 text-center shadow-sm">
              <div className="text-2xl font-bold text-orange-500">{stats.pending}</div>
              <div className="text-xs text-orange-500 mt-1 flex items-center justify-center gap-1">Pendientes</div>
            </div>
            <div className="bg-green-50 border border-green-100 rounded-xl p-4 text-center shadow-sm">
              <div className="text-2xl font-bold text-green-700">{stats.today}</div>
              <div className="text-xs text-green-700 mt-1 flex items-center justify-center gap-1">Hoy</div>
            </div>
          </div>

          {/* Recent Messages Card */}
          <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-5 min-h-[400px] flex flex-col">
            <div className="flex justify-between items-center mb-4">
              <h3 className="font-semibold text-gray-800 flex items-center gap-2">
                <svg className="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Mensajes Recientes
              </h3>
              <span className="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full flex items-center gap-1 font-medium">
                <span className="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                En vivo
              </span>
            </div>

            <div className="flex-1 flex flex-col p-2 overflow-y-auto max-h-[500px]">
              {connectionStatus === 'connected' ? (
                messages.length > 0 ? (
                  <div className="space-y-3 flex flex-col">
                    {messages.map((msg: any, i: number) => {
                      const isIncoming = msg.status === 'received';
                      const contactId = isIncoming ? (msg.from || msg.remoteJid) : (msg.to || msg.remoteJid);
                      
                      const formatPhone = (phoneStr: string) => {
                        if (!phoneStr) return 'Desconocido';
                        let clean = phoneStr.split('@')[0].split(':')[0];
                        return '+' + clean;
                      };

                      const parseMessageBody = (rawMsg: any) => {
                        if (!rawMsg) return 'Contenido multimedia';
                        let obj = rawMsg;
                        if (typeof rawMsg === 'string') {
                          try { obj = JSON.parse(rawMsg); } 
                          catch (e) { return rawMsg; }
                        }
                        
                        if (obj.conversation) return obj.conversation;
                        if (obj.text) return obj.text;
                        if (obj.extendedTextMessage?.text) return obj.extendedTextMessage.text;
                        
                        if (obj.imageMessage) return `📷 ${obj.imageMessage.caption || 'Imagen'}`;
                        if (obj.videoMessage) return `🎥 ${obj.videoMessage.caption || 'Video'}`;
                        if (obj.documentMessage || obj.document) {
                          const doc = obj.documentMessage || obj.document;
                          const name = doc.fileName || doc.title || 'Documento';
                          return doc.caption ? `📄 ${name}\n\n${doc.caption}` : `📄 ${name}`;
                        }
                        if (obj.audioMessage) return '🎵 Audio';
                        if (obj.stickerMessage) return '🧩 Sticker';
                        if (obj.contactsArrayMessage || obj.contactMessage) return '👤 Contacto';
                        if (obj.locationMessage) return '📍 Ubicación';
                        
                        return 'Contenido multimedia';
                      };

                      return (
                        <div key={i} className="flex flex-col bg-gray-50 rounded-lg p-3 border border-gray-100">
                          <div className="flex justify-between items-center mb-1">
                            <span className="text-xs font-semibold text-gray-700 font-mono">
                              {formatPhone(contactId)} {isIncoming ? '(Entrante)' : ''}
                            </span>
                            <span className="text-[10px] text-gray-400">
                              {new Date(msg.createdAt || Date.now()).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                            </span>
                          </div>
                          <p className="text-sm text-gray-600 break-words line-clamp-2">
                            {parseMessageBody(msg.message || msg.body)}
                          </p>
                          <div className="mt-2 flex justify-end">
                            <span className={`text-[10px] px-2 py-0.5 rounded-full font-medium capitalize ${
                              msg.status === 'delivered' ? 'bg-blue-100 text-blue-700' :
                              msg.status === 'sent' ? 'bg-green-100 text-green-700' :
                              msg.status === 'failed' ? 'bg-red-100 text-red-700' :
                              msg.status === 'received' ? 'bg-purple-100 text-purple-700' :
                              'bg-gray-100 text-gray-700'
                            }`}>
                              {msg.status === 'received' ? 'recibido' : (msg.status || 'enviado')}
                            </span>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                ) : (
                  <div className="flex-1 flex flex-col items-center justify-center text-gray-400 text-sm h-full py-10">
                    <svg className="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    Aún no hay mensajes recientes.
                  </div>
                )
              ) : (
                <div className="flex-1 flex flex-col items-center justify-center text-gray-400 text-sm h-full py-10">
                  <svg className="w-12 h-12 text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                  Conecta WhatsApp para ver los mensajes en tiempo real.
                </div>
              )}
            </div>
          </div>

        </div>
      </div>
    </div>
  );
};

export default WhatsAppCrm;
