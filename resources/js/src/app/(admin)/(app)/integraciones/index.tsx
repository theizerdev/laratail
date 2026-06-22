import { Link } from 'react-router-dom';
import { LuMessageSquare, LuMail, LuMessageCircle } from 'react-icons/lu';

const IntegracionesIndex = () => {
  return (
    <div className="p-6">
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Integraciones</h1>
        <p className="text-gray-500 mt-1">Conecta servicios externos para ampliar las capacidades del sistema.</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {/* WhatsApp CRM Card */}
        <Link
          to="/admin/integraciones/whatsapp"
          className="block bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-green-500 hover:shadow-md transition-all p-6 relative group"
        >
          <div className="flex justify-between items-start mb-4">
            <div className="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center">
              <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
            </div>
            <span className="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">Disponible</span>
          </div>
          <h3 className="text-lg font-bold text-gray-900 dark:text-white">WhatsApp CRM</h3>
          <p className="text-gray-500 text-sm mt-2 mb-4">Conecta WhatsApp Business para enviar mensajes masivos, gestionar contactos y automatizar comunicaciones.</p>
          <div className="pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-sm">
            <div className="flex items-center text-gray-600 dark:text-gray-400">
              <span className="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
              Gestionar integración
            </div>
            <svg className="w-5 h-5 text-gray-400 group-hover:text-green-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path></svg>
          </div>
        </Link>

        {/* Email Marketing Card */}
        <div className="bg-gray-50 dark:bg-slate-800/50 rounded-xl border border-dashed border-gray-200 dark:border-gray-700 p-6 relative opacity-75">
          <div className="flex justify-between items-start mb-4">
            <div className="w-10 h-10 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-500 flex items-center justify-center">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <span className="text-gray-400 dark:text-gray-500 text-xs font-semibold">Proximamente</span>
          </div>
          <h3 className="text-lg font-bold text-gray-400 dark:text-gray-500">Email Marketing</h3>
          <p className="text-gray-400 dark:text-gray-500 text-sm mt-2 mb-4">Envio de campanas de email, automatizacion y seguimiento de apertura.</p>
          <div className="pt-4 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-400 dark:text-gray-500">
            Proximamente disponible
          </div>
        </div>

        {/* SMS Masivos Card */}
        <div className="bg-gray-50 dark:bg-slate-800/50 rounded-xl border border-dashed border-gray-200 dark:border-gray-700 p-6 relative opacity-75">
          <div className="flex justify-between items-start mb-4">
            <div className="w-10 h-10 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-500 flex items-center justify-center">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            </div>
            <span className="text-gray-400 dark:text-gray-500 text-xs font-semibold">Proximamente</span>
          </div>
          <h3 className="text-lg font-bold text-gray-400 dark:text-gray-500">SMS Masivos</h3>
          <p className="text-gray-400 dark:text-gray-500 text-sm mt-2 mb-4">Envio de mensajes SMS a contactos y clientes con seguimiento de entrega.</p>
          <div className="pt-4 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-400 dark:text-gray-500">
            Proximamente disponible
          </div>
        </div>
      </div>
    </div>
  );
};

export default IntegracionesIndex;
