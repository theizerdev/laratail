import { useState } from 'react';
import axios from '@/lib/axios';
import { LuDatabaseBackup, LuDownload, LuUpload, LuCheck, LuRefreshCcw } from 'react-icons/lu';

const ExportImportTab = ({ tablesCount, sizeMb }: { tablesCount: string | number, sizeMb: string | number }) => {
  const [activeTab, setActiveTab] = useState<'export' | 'import'>('export');
  const [step, setStep] = useState(1);
  
  // Export Options
  const [exportOptions, setExportOptions] = useState({
    includeStructure: true,
    includeData: true,
    dropTable: true,
    ifNotExists: true,
    compress: false
  });

  // Import State
  const [importFile, setImportFile] = useState<File | null>(null);

  // Password state
  const [showPasswordModal, setShowPasswordModal] = useState(false);
  const [password, setPassword] = useState('');
  const [pendingAction, setPendingAction] = useState<'export' | 'import' | null>(null);

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);

  const triggerExport = () => {
    setPendingAction('export');
    setPassword('');
    setShowPasswordModal(true);
  };

  const triggerImport = () => {
    setPendingAction('import');
    setPassword('');
    setShowPasswordModal(true);
  };

  const confirmAction = () => {
    if (!password) {
      setError('La contraseña es requerida');
      return;
    }
    setShowPasswordModal(false);
    if (pendingAction === 'export') {
      handleExport(password);
    } else if (pendingAction === 'import') {
      handleImport(password);
    }
  };

  const handleExport = async (pass: string) => {
    try {
      setLoading(true);
      setError(null);
      setSuccess(null);
      
      const response = await axios.post('/api/admin/monitoreo/database/export', {
        password: pass,
        include_structure: exportOptions.includeStructure,
        include_data: exportOptions.includeData,
        drop_table: exportOptions.dropTable,
        if_not_exists: exportOptions.ifNotExists,
        compress: exportOptions.compress
      }, {
        responseType: 'blob'
      });

      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      const extension = exportOptions.compress ? '.sql.gz' : '.sql';
      link.setAttribute('download', `backup_${new Date().getTime()}${extension}`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      
      setSuccess('Base de datos exportada con éxito.');
      setStep(4); // Progreso/Finalizado
    } catch (err: any) {
      if (err.response?.data instanceof Blob) {
        // Parse blob to JSON
        const reader = new FileReader();
        reader.onload = () => {
          try {
            const data = JSON.parse(reader.result as string);
            setError(data.message || 'Error al exportar la base de datos');
          } catch (e) {
            setError('Error al exportar la base de datos');
          }
        };
        reader.readAsText(err.response.data);
      } else {
        setError(err.response?.data?.message || 'Error al exportar la base de datos');
      }
    } finally {
      setLoading(false);
    }
  };

  const handleImport = async (pass: string) => {
    if (!importFile) {
      setError('Por favor selecciona un archivo .sql o .gz');
      return;
    }

    try {
      setLoading(true);
      setError(null);
      setSuccess(null);

      const formData = new FormData();
      formData.append('file', importFile);
      formData.append('password', pass);

      const response = await axios.post('/api/admin/monitoreo/database/import', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });

      if (response.data.success) {
        setSuccess('Base de datos restaurada con éxito.');
        setStep(4);
      }
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al importar la base de datos');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6 mt-6">
      {/* Header Banner */}
      <div className="bg-primary text-white rounded-xl p-6 flex justify-between items-center shadow-md">
        <div>
          <h2 className="text-2xl font-bold flex items-center gap-3">
            <LuDatabaseBackup size={28} />
            Exportar e Importar Base de Datos
          </h2>
          <p className="text-primary-100 mt-1">Gestiona respaldos y restauración del sistema de forma segura</p>
        </div>
        <button 
          onClick={() => { setStep(1); setActiveTab('export'); setSuccess(null); setError(null); }}
          className="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors text-sm font-medium"
        >
          <LuRefreshCcw size={16} /> Reiniciar
        </button>
      </div>

      {/* Info Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="bg-white dark:bg-default-50 border border-default-200 rounded-xl p-4 flex items-center gap-4">
          <div className="bg-primary/10 text-primary p-3 rounded-lg"><LuDatabaseBackup size={24} /></div>
          <div>
            <p className="text-xs text-default-500 font-semibold uppercase">Total Tablas</p>
            <p className="text-xl font-bold">{tablesCount}</p>
          </div>
        </div>
        <div className="bg-white dark:bg-default-50 border border-default-200 rounded-xl p-4 flex items-center gap-4">
          <div className="bg-success/10 text-success p-3 rounded-lg"><LuDatabaseBackup size={24} /></div>
          <div>
            <p className="text-xs text-default-500 font-semibold uppercase">Tamaño Estimado</p>
            <p className="text-xl font-bold">{sizeMb} MB</p>
          </div>
        </div>
        <div className="bg-white dark:bg-default-50 border border-default-200 rounded-xl p-4 flex items-center gap-4 cursor-pointer hover:border-primary transition-colors" onClick={() => { setActiveTab('export'); setStep(1); }}>
          <div className="bg-warning/10 text-warning p-3 rounded-lg"><LuDownload size={24} /></div>
          <div>
            <p className="text-xs text-default-500 font-semibold uppercase">Exportar</p>
            <p className="text-xl font-bold">SQL / GZ</p>
          </div>
        </div>
        <div className="bg-white dark:bg-default-50 border border-default-200 rounded-xl p-4 flex items-center gap-4 cursor-pointer hover:border-primary transition-colors" onClick={() => { setActiveTab('import'); setStep(1); }}>
          <div className="bg-danger/10 text-danger p-3 rounded-lg"><LuUpload size={24} /></div>
          <div>
            <p className="text-xs text-default-500 font-semibold uppercase">Importar</p>
            <p className="text-xl font-bold">.sql / .gz</p>
          </div>
        </div>
      </div>

      {/* Action Tabs */}
      <div className="flex bg-white dark:bg-default-50 border border-default-200 rounded-xl overflow-hidden p-1 gap-2">
        <button 
          className={`flex-1 py-3 text-sm font-semibold rounded-lg transition-colors flex items-center justify-center gap-2 ${activeTab === 'export' ? 'bg-primary text-white' : 'hover:bg-default-100 text-default-600'}`}
          onClick={() => { setActiveTab('export'); setStep(1); }}
        >
          <LuDownload size={18} /> Exportar Base de Datos
        </button>
        <button 
          className={`flex-1 py-3 text-sm font-semibold rounded-lg transition-colors flex items-center justify-center gap-2 ${activeTab === 'import' ? 'bg-primary text-white' : 'hover:bg-default-100 text-default-600'}`}
          onClick={() => { setActiveTab('import'); setStep(1); }}
        >
          <LuUpload size={18} /> Importar Base de Datos
        </button>
      </div>

      {/* Stepper */}
      <div className="flex items-center justify-between px-10 py-6 bg-white dark:bg-default-50 border border-default-200 rounded-xl">
        {[
          { num: 1, label: 'Opciones' },
          { num: 2, label: 'Vista previa' },
          { num: 3, label: 'Confirmación' },
          { num: 4, label: 'Progreso' }
        ].map((s, idx) => (
          <div key={s.num} className="flex flex-col items-center gap-2 relative z-10 w-1/4">
            <div className={`w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-colors ${step >= s.num ? 'bg-primary text-white' : 'bg-default-100 text-default-400'}`}>
              {step > s.num ? <LuCheck size={20} /> : s.num}
            </div>
            <span className={`text-xs font-semibold ${step >= s.num ? 'text-primary' : 'text-default-400'}`}>{s.label}</span>
          </div>
        ))}
      </div>

      {/* Wizard Content */}
      <div className="bg-white dark:bg-default-50 border border-default-200 rounded-xl p-6 min-h-[300px]">
        {error && (
          <div className="bg-danger/10 text-danger border border-danger/20 p-4 rounded-lg mb-6 text-sm">
            {error}
          </div>
        )}
        
        {success && (
          <div className="bg-success/10 text-success border border-success/20 p-4 rounded-lg mb-6 text-sm flex items-center gap-2">
            <LuCheck size={20} /> {success}
          </div>
        )}

        {/* EXPORT WIZARD */}
        {activeTab === 'export' && (
          <>
            {step === 1 && (
              <div className="animate-fade-in">
                <h3 className="text-lg font-bold mb-4 flex items-center gap-2">Configuración de Exportación</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <label className={`border p-4 rounded-xl flex gap-3 cursor-pointer transition-colors ${exportOptions.includeStructure ? 'border-primary bg-primary/5' : 'border-default-200 hover:border-default-300'}`}>
                    <input type="checkbox" className="mt-1" checked={exportOptions.includeStructure} onChange={(e) => setExportOptions({...exportOptions, includeStructure: e.target.checked})} />
                    <div>
                      <h4 className="font-semibold text-sm">Incluir estructura</h4>
                      <p className="text-xs text-default-500">CREATE TABLE statements con definiciones completas</p>
                    </div>
                  </label>
                  <label className={`border p-4 rounded-xl flex gap-3 cursor-pointer transition-colors ${exportOptions.includeData ? 'border-primary bg-primary/5' : 'border-default-200 hover:border-default-300'}`}>
                    <input type="checkbox" className="mt-1" checked={exportOptions.includeData} onChange={(e) => setExportOptions({...exportOptions, includeData: e.target.checked})} />
                    <div>
                      <h4 className="font-semibold text-sm">Incluir datos</h4>
                      <p className="text-xs text-default-500">INSERT statements con todos los registros</p>
                    </div>
                  </label>
                  <label className={`border p-4 rounded-xl flex gap-3 cursor-pointer transition-colors ${exportOptions.dropTable ? 'border-primary bg-primary/5' : 'border-default-200 hover:border-default-300'}`}>
                    <input type="checkbox" className="mt-1" checked={exportOptions.dropTable} onChange={(e) => setExportOptions({...exportOptions, dropTable: e.target.checked})} />
                    <div>
                      <h4 className="font-semibold text-sm">Agregar DROP TABLE</h4>
                      <p className="text-xs text-default-500">Eliminar tablas existentes antes de crearlas</p>
                    </div>
                  </label>
                  <label className={`border p-4 rounded-xl flex gap-3 cursor-pointer transition-colors ${exportOptions.ifNotExists ? 'border-primary bg-primary/5' : 'border-default-200 hover:border-default-300'}`}>
                    <input type="checkbox" className="mt-1" checked={exportOptions.ifNotExists} onChange={(e) => setExportOptions({...exportOptions, ifNotExists: e.target.checked})} />
                    <div>
                      <h4 className="font-semibold text-sm">IF NOT EXISTS</h4>
                      <p className="text-xs text-default-500">Solo crear si la tabla no existe</p>
                    </div>
                  </label>
                  <label className={`border p-4 rounded-xl flex gap-3 cursor-pointer transition-colors ${exportOptions.compress ? 'border-primary bg-primary/5' : 'border-default-200 hover:border-default-300'}`}>
                    <input type="checkbox" className="mt-1" checked={exportOptions.compress} onChange={(e) => setExportOptions({...exportOptions, compress: e.target.checked})} />
                    <div>
                      <h4 className="font-semibold text-sm">Comprimir archivo</h4>
                      <p className="text-xs text-default-500">Generar archivo .sql.gz (gzip) para reducir el peso</p>
                    </div>
                  </label>
                </div>
                <div className="mt-8 flex justify-end">
                  <button onClick={() => setStep(2)} className="bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-600 transition-colors">
                    Siguiente: Vista Previa
                  </button>
                </div>
              </div>
            )}

            {step === 2 && (
              <div className="animate-fade-in">
                <h3 className="text-lg font-bold mb-4">Vista Previa de la Configuración</h3>
                <div className="bg-default-50 p-6 rounded-xl border border-default-200">
                  <ul className="space-y-3 text-sm">
                    <li className="flex justify-between border-b border-default-200 pb-2">
                      <span className="text-default-600">Acción a realizar</span>
                      <span className="font-bold">Exportación de Base de Datos</span>
                    </li>
                    <li className="flex justify-between border-b border-default-200 pb-2">
                      <span className="text-default-600">Estructura incluida</span>
                      <span className="font-semibold text-primary">{exportOptions.includeStructure ? 'Sí' : 'No'}</span>
                    </li>
                    <li className="flex justify-between border-b border-default-200 pb-2">
                      <span className="text-default-600">Datos incluidos</span>
                      <span className="font-semibold text-primary">{exportOptions.includeData ? 'Sí' : 'No'}</span>
                    </li>
                    <li className="flex justify-between border-b border-default-200 pb-2">
                      <span className="text-default-600">Formato del archivo</span>
                      <span className="font-semibold">{exportOptions.compress ? '.sql.gz (Comprimido)' : '.sql (Texto Plano)'}</span>
                    </li>
                  </ul>
                </div>
                <div className="mt-8 flex justify-between">
                  <button onClick={() => setStep(1)} className="border border-default-300 text-default-700 px-6 py-2 rounded-lg font-medium hover:bg-default-100 transition-colors">
                    Atrás
                  </button>
                  <button onClick={() => setStep(3)} className="bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-600 transition-colors">
                    Siguiente: Confirmar
                  </button>
                </div>
              </div>
            )}

            {step === 3 && (
              <div className="animate-fade-in text-center py-10">
                <div className="inline-flex items-center justify-center w-20 h-20 bg-primary/10 text-primary rounded-full mb-6">
                  <LuDownload size={40} />
                </div>
                <h3 className="text-2xl font-bold mb-2">Listo para Exportar</h3>
                <p className="text-default-500 mb-8 max-w-md mx-auto">
                  El sistema generará un archivo con la configuración seleccionada. Dependiendo del tamaño de la base de datos, este proceso puede tardar unos segundos.
                </p>
                <div className="flex justify-center gap-4">
                  <button onClick={() => setStep(2)} className="border border-default-300 text-default-700 px-6 py-2 rounded-lg font-medium hover:bg-default-100 transition-colors" disabled={loading}>
                    Atrás
                  </button>
                  <button onClick={triggerExport} className="bg-primary text-white px-8 py-2 rounded-lg font-medium hover:bg-primary-600 transition-colors flex items-center gap-2" disabled={loading}>
                    {loading ? <span className="animate-spin w-5 h-5 border-2 border-white/30 border-t-white rounded-full"></span> : <LuDownload size={18} />}
                    {loading ? 'Exportando...' : 'Comenzar Exportación'}
                  </button>
                </div>
              </div>
            )}

            {step === 4 && (
              <div className="animate-fade-in text-center py-10">
                <div className="inline-flex items-center justify-center w-20 h-20 bg-success/10 text-success rounded-full mb-6">
                  <LuCheck size={40} />
                </div>
                <h3 className="text-2xl font-bold mb-2">¡Exportación Exitosa!</h3>
                <p className="text-default-500 mb-8 max-w-md mx-auto">
                  El archivo se ha descargado en tu navegador. Recuerda almacenar este respaldo en un lugar seguro.
                </p>
                <button onClick={() => { setStep(1); setSuccess(null); }} className="bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-600 transition-colors">
                  Realizar otra operación
                </button>
              </div>
            )}
          </>
        )}

        {/* IMPORT WIZARD */}
        {activeTab === 'import' && (
          <>
            {step === 1 && (
              <div className="animate-fade-in">
                <h3 className="text-lg font-bold mb-4">Seleccionar archivo de respaldo</h3>
                <div className="border-2 border-dashed border-default-300 rounded-xl p-10 text-center hover:border-primary transition-colors cursor-pointer bg-default-50"
                     onClick={() => document.getElementById('import-file-upload')?.click()}>
                  <LuUpload size={40} className="mx-auto text-default-400 mb-4" />
                  <p className="font-semibold mb-1">Haz clic para subir o arrastra tu archivo</p>
                  <p className="text-xs text-default-500">Soporta archivos .sql o .sql.gz (Máx. 100MB)</p>
                  <input 
                    id="import-file-upload" 
                    type="file" 
                    className="hidden" 
                    accept=".sql,.gz" 
                    onChange={(e) => {
                      if (e.target.files && e.target.files[0]) {
                        setImportFile(e.target.files[0]);
                        setStep(2);
                      }
                    }} 
                  />
                </div>
                {importFile && (
                  <div className="mt-4 p-4 border border-primary/30 bg-primary/5 rounded-lg flex justify-between items-center">
                    <span className="font-medium text-sm text-primary">{importFile.name} ({(importFile.size / 1024 / 1024).toFixed(2)} MB)</span>
                    <button onClick={() => setImportFile(null)} className="text-danger text-sm hover:underline">Quitar</button>
                  </div>
                )}
                {importFile && (
                  <div className="mt-6 flex justify-end">
                    <button onClick={() => setStep(2)} className="bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-600 transition-colors">
                      Siguiente: Vista Previa
                    </button>
                  </div>
                )}
              </div>
            )}

            {step === 2 && (
              <div className="animate-fade-in">
                <h3 className="text-lg font-bold mb-4">Vista Previa de Importación</h3>
                <div className="bg-warning/10 border border-warning/30 p-4 rounded-xl mb-6">
                  <p className="text-warning-800 text-sm font-semibold mb-1 flex items-center gap-2">
                    ⚠️ Advertencia Crítica
                  </p>
                  <p className="text-warning-800 text-xs">
                    Importar una base de datos sobrescribirá las tablas existentes si el archivo contiene sentencias DROP TABLE. Esta acción no se puede deshacer. Se recomienda realizar una exportación de respaldo primero.
                  </p>
                </div>
                
                <div className="bg-default-50 p-6 rounded-xl border border-default-200">
                  <ul className="space-y-3 text-sm">
                    <li className="flex justify-between border-b border-default-200 pb-2">
                      <span className="text-default-600">Archivo a restaurar</span>
                      <span className="font-bold">{importFile?.name}</span>
                    </li>
                    <li className="flex justify-between border-b border-default-200 pb-2">
                      <span className="text-default-600">Tamaño del archivo</span>
                      <span className="font-semibold text-primary">{importFile ? (importFile.size / 1024 / 1024).toFixed(2) : 0} MB</span>
                    </li>
                  </ul>
                </div>
                <div className="mt-8 flex justify-between">
                  <button onClick={() => setStep(1)} className="border border-default-300 text-default-700 px-6 py-2 rounded-lg font-medium hover:bg-default-100 transition-colors">
                    Atrás
                  </button>
                  <button onClick={() => setStep(3)} className="bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-600 transition-colors">
                    Siguiente: Confirmar
                  </button>
                </div>
              </div>
            )}

            {step === 3 && (
              <div className="animate-fade-in text-center py-10">
                <div className="inline-flex items-center justify-center w-20 h-20 bg-danger/10 text-danger rounded-full mb-6">
                  <LuUpload size={40} />
                </div>
                <h3 className="text-2xl font-bold mb-2 text-danger">Confirmar Restauración</h3>
                <p className="text-default-500 mb-8 max-w-md mx-auto">
                  Estás a punto de restaurar la base de datos con el archivo <strong>{importFile?.name}</strong>. Por favor confirma que deseas proceder.
                </p>
                <div className="flex justify-center gap-4">
                  <button onClick={() => setStep(2)} className="border border-default-300 text-default-700 px-6 py-2 rounded-lg font-medium hover:bg-default-100 transition-colors" disabled={loading}>
                    Cancelar
                  </button>
                  <button onClick={triggerImport} className="bg-danger text-white px-8 py-2 rounded-lg font-medium hover:bg-danger-600 transition-colors flex items-center gap-2" disabled={loading}>
                    {loading ? <span className="animate-spin w-5 h-5 border-2 border-white/30 border-t-white rounded-full"></span> : <LuUpload size={18} />}
                    {loading ? 'Restaurando...' : 'Confirmar Restauración'}
                  </button>
                </div>
              </div>
            )}

            {step === 4 && (
              <div className="animate-fade-in text-center py-10">
                <div className="inline-flex items-center justify-center w-20 h-20 bg-success/10 text-success rounded-full mb-6">
                  <LuCheck size={40} />
                </div>
                <h3 className="text-2xl font-bold mb-2">¡Restauración Exitosa!</h3>
                <p className="text-default-500 mb-8 max-w-md mx-auto">
                  La base de datos ha sido restaurada completamente a partir del archivo proporcionado.
                </p>
                <button onClick={() => { setStep(1); setImportFile(null); setSuccess(null); }} className="bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-600 transition-colors">
                  Finalizar
                </button>
              </div>
            )}
          </>
        )}
      </div>

      {/* Password Modal */}
      {showPasswordModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 animate-fade-in">
          <div className="bg-white dark:bg-default-50 rounded-xl shadow-xl w-full max-w-md p-6 animate-scale-up">
            <h3 className="text-xl font-bold mb-2">Verificación de Seguridad</h3>
            <p className="text-default-500 text-sm mb-6">
              Para continuar con la {pendingAction === 'export' ? 'exportación' : 'restauración'} de la base de datos, por favor ingresa tu contraseña.
            </p>
            
            <div className="mb-6">
              <label className="block text-sm font-medium mb-2 text-default-700">Contraseña</label>
              <input 
                type="password" 
                className="w-full px-4 py-2 border border-default-300 rounded-lg focus:ring-primary focus:border-primary dark:bg-default-100"
                placeholder="Ingresa tu contraseña actual"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                onKeyDown={(e) => {
                  if (e.key === 'Enter') confirmAction();
                }}
                autoFocus
              />
            </div>

            <div className="flex justify-end gap-3">
              <button 
                onClick={() => setShowPasswordModal(false)}
                className="px-4 py-2 border border-default-300 rounded-lg font-medium text-default-700 hover:bg-default-100 transition-colors"
              >
                Cancelar
              </button>
              <button 
                onClick={confirmAction}
                className="px-4 py-2 bg-primary text-white rounded-lg font-medium hover:bg-primary-600 transition-colors"
                disabled={!password}
              >
                Verificar y Continuar
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ExportImportTab;
