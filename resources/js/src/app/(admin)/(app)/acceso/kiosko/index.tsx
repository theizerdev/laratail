import React, { useState, useEffect, useRef } from 'react';
import axios from '@/lib/axios';
import { ScanLine, CheckCircle2, XCircle, LogIn, LogOut, User, Camera, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { LuArrowLeft } from 'react-icons/lu';
import { Scanner } from '@yudiel/react-qr-scanner';

export default function Kiosko() {
  const [isScanning, setIsScanning] = useState(false);
  const [result, setResult] = useState<any>(null);
  const [error, setError] = useState<string | null>(null);
  const [showCamera, setShowCamera] = useState(false);
  
  const bufferRef = useRef('');
  const timerRef = useRef<any>(null);

  const [time, setTime] = useState(new Date());

  useEffect(() => {
    const interval = setInterval(() => setTime(new Date()), 1000);
    return () => clearInterval(interval);
  }, []);

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        if (showCamera) {
          setShowCamera(false);
        } else {
          window.history.back();
        }
        return;
      }
      if (e.key.length > 1 && e.key !== 'Enter') return;

      if (e.key === 'Enter') {
        if (bufferRef.current.length > 0) {
          procesarEscaneo(bufferRef.current);
          bufferRef.current = '';
        }
      } else {
        bufferRef.current += e.key;
      }

      clearTimeout(timerRef.current);
      timerRef.current = setTimeout(() => {
        bufferRef.current = '';
      }, 100); 
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => {
      window.removeEventListener('keydown', handleKeyDown);
      clearTimeout(timerRef.current);
    };
  }, [showCamera]);

  const procesarEscaneo = async (scannedCode: string) => {
    if (isScanning) return;
    
    setIsScanning(true);
    setResult(null);
    setError(null);
    setShowCamera(false);

    try {
      const response = await axios.post('/api/acceso/escanear', {
        codigo: scannedCode,
        metodo: 'codigo'
      });
      setResult(response.data);
      setTimeout(() => setResult(null), 5000);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al procesar el código.');
      setTimeout(() => setError(null), 4000);
    } finally {
      setIsScanning(false);
    }
  };

  const getInitials = (nombre: string, apellido: string) => {
    return `${nombre?.charAt(0) || ''}${apellido?.charAt(0) || ''}`.toUpperCase();
  };

  return (
    <div 
      style={{ 
        position: 'fixed',
        top: 0, left: 0, right: 0, bottom: 0,
        zIndex: 9999,
        backgroundImage: `url('/images/kiosk_bg.png')`, 
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        backgroundColor: '#0f172a',
        display: 'flex',
        flexDirection: 'column'
      }}
    >
      {/* Overlay Background */}
      <div 
        style={{
          position: 'absolute',
          top: 0, left: 0, right: 0, bottom: 0,
          backgroundColor: 'rgba(30, 27, 75, 0.6)',
          backdropFilter: 'blur(8px)',
          zIndex: 1
        }}
      ></div>

      {/* Main Content Container */}
      <div style={{ position: 'relative', zIndex: 2, display: 'flex', flexDirection: 'column', height: '100%', width: '100%' }}>
        
        {/* Header */}
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '2rem 3rem' }}>
          <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-start' }}>
            <Button 
              variant="outline" 
              onClick={() => window.history.back()} 
              style={{ backgroundColor: 'rgba(255,255,255,0.1)', color: 'white', borderColor: 'rgba(255,255,255,0.2)', marginBottom: '1rem' }}
            >
              <LuArrowLeft className="mr-2" /> Volver al Panel
            </Button>
            <h1 className="text-4xl font-extrabold text-white tracking-tight drop-shadow-md">Laraccess</h1>
            <p className="text-indigo-200 text-lg font-medium mt-1">Control de Acceso Estudiantil</p>
          </div>

          <div className="text-right text-white">
            <div className="text-5xl font-bold tracking-tighter tabular-nums drop-shadow-lg">
              {time.toLocaleTimeString('es-VE', { hour: '2-digit', minute: '2-digit', hour12: true })}
            </div>
            <div className="text-lg font-medium opacity-80 mt-1">
              {time.toLocaleDateString('es-VE', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
            </div>
          </div>
        </div>

        {/* Center Content */}
        <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '2rem' }}>
          <div 
            style={{ 
              width: '100%', 
              maxWidth: '600px', 
              backgroundColor: 'rgba(255, 255, 255, 0.1)', 
              backdropFilter: 'blur(16px)', 
              border: '1px solid rgba(255, 255, 255, 0.2)', 
              boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.5)',
              borderRadius: '2rem',
              padding: '3rem',
              textAlign: 'center',
              overflow: 'hidden',
              position: 'relative'
            }}
          >
            {/* Camera View State */}
            {showCamera && !isScanning && !result && !error && (
              <div className="flex flex-col items-center justify-center animate-in fade-in duration-300">
                <div style={{ width: '300px', height: '300px', borderRadius: '1.5rem', overflow: 'hidden', marginBottom: '1.5rem', position: 'relative', border: '4px solid rgba(255,255,255,0.3)' }}>
                  <Scanner
                    onScan={(detectedCodes) => {
                      if (detectedCodes && detectedCodes.length > 0) {
                        procesarEscaneo(detectedCodes[0].rawValue);
                      }
                    }}
                    formats={['qr_code', 'code_128', 'ean_13']}
                  />
                  <div className="absolute inset-0 border-2 border-indigo-400 rounded-2xl animate-pulse pointer-events-none"></div>
                </div>
                <Button 
                  variant="outline" 
                  onClick={() => setShowCamera(false)}
                  style={{ backgroundColor: 'rgba(239, 68, 68, 0.2)', color: 'white', borderColor: 'rgba(239, 68, 68, 0.4)' }}
                >
                  <X className="mr-2" size={18} /> Cancelar Cámara
                </Button>
              </div>
            )}

            {/* Default Waiting State */}
            {!showCamera && !result && !error && (
              <div className="flex flex-col items-center justify-center animate-in fade-in duration-500">
                <div 
                  onClick={() => setShowCamera(true)}
                  style={{ 
                    backgroundColor: 'rgba(255, 255, 255, 0.2)', 
                    padding: '2.5rem', 
                    borderRadius: '50%', 
                    marginBottom: '2rem', 
                    border: '1px solid rgba(255,255,255,0.4)',
                    cursor: 'pointer',
                    transition: 'all 0.3s ease',
                    position: 'relative'
                  }}
                  className="hover:scale-105 hover:bg-white/30 group"
                  title="Haga clic para encender la cámara"
                >
                  <ScanLine className="text-white group-hover:hidden" size={80} strokeWidth={1.5} />
                  <Camera className="text-white hidden group-hover:block" size={80} strokeWidth={1.5} />
                  
                  {/* Tooltip hint */}
                  <div className="absolute -bottom-10 left-1/2 -translate-x-1/2 w-48 text-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/60 text-white text-xs py-1 px-2 rounded">
                    Haz clic para usar cámara
                  </div>
                </div>
                <h2 className="text-3xl font-bold text-white mb-2 drop-shadow-md">Aproxime su credencial</h2>
                <p className="text-indigo-100 text-lg opacity-90">Escanee con pistola o haga clic para usar la cámara</p>
              </div>
            )}

            {/* Loading State */}
            {isScanning && (
              <div className="flex flex-col items-center justify-center py-8">
                <div className="animate-spin mb-6" style={{ width: '80px', height: '80px', border: '4px solid rgba(255,255,255,0.3)', borderTopColor: 'white', borderRadius: '50%' }}></div>
                <h2 className="text-2xl font-bold text-white tracking-wider animate-pulse">Verificando...</h2>
              </div>
            )}

            {/* Error State */}
            {error && (
              <div className="flex flex-col items-center justify-center text-center animate-in fade-in slide-in-from-bottom-4 duration-300 py-4">
                <div style={{ width: '100px', height: '100px', backgroundColor: 'rgba(239, 68, 68, 0.2)', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: '1.5rem', border: '1px solid rgba(239, 68, 68, 0.4)' }}>
                  <XCircle className="text-red-400" size={60} />
                </div>
                <h2 className="text-3xl font-bold text-white mb-4 drop-shadow-md">Acceso Denegado</h2>
                <div style={{ backgroundColor: 'rgba(239, 68, 68, 0.2)', border: '1px solid rgba(239, 68, 68, 0.3)', borderRadius: '1rem', padding: '1rem 1.5rem' }}>
                  <p className="text-red-200 text-xl font-medium">{error}</p>
                </div>
              </div>
            )}

            {/* Success State */}
            {result && (
              <div className="flex flex-col items-center text-center animate-in fade-in zoom-in duration-300">
                <div 
                  style={{
                    display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
                    padding: '0.5rem 1.5rem', borderRadius: '9999px', marginBottom: '2rem',
                    backgroundColor: result.tipo === 'entrada' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(14, 165, 233, 0.2)',
                    border: `1px solid ${result.tipo === 'entrada' ? 'rgba(16, 185, 129, 0.4)' : 'rgba(14, 165, 233, 0.4)'}`,
                    color: result.tipo === 'entrada' ? '#6ee7b7' : '#7dd3fc'
                  }}
                >
                  {result.tipo === 'entrada' ? <LogIn size={24} className="mr-3" /> : <LogOut size={24} className="mr-3" />}
                  <span className="text-xl font-bold uppercase tracking-wider">
                    {result.tipo === 'entrada' ? 'Entrada Registrada' : 'Salida Registrada'}
                  </span>
                </div>
                
                {/* Profile Picture */}
                <div 
                  style={{
                    position: 'relative', width: '150px', height: '150px', borderRadius: '50%', overflow: 'hidden',
                    border: '4px solid rgba(255,255,255,0.8)', boxShadow: '0 0 30px rgba(255,255,255,0.2)', marginBottom: '1.5rem'
                  }}
                >
                  {result.estudiante.foto_url ? (
                    <img src={result.estudiante.foto_url} alt="Estudiante" style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                  ) : (
                    <div style={{ width: '100%', height: '100%', backgroundColor: 'rgba(255,255,255,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '3rem', fontWeight: 'bold', color: 'white' }}>
                      {getInitials(result.estudiante.nombre, result.estudiante.apellido)}
                    </div>
                  )}
                </div>

                <h3 className="text-4xl font-bold text-white mb-3 drop-shadow-md">
                  {result.estudiante.nombre} {result.estudiante.apellido}
                </h3>
                
                <div style={{ display: 'inline-flex', alignItems: 'center', padding: '0.5rem 1.5rem', backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: '9999px', border: '1px solid rgba(255,255,255,0.2)', color: 'white', fontSize: '1.125rem' }}>
                  <User size={20} className="mr-2 opacity-70" />
                  {result.estudiante.grado} {result.estudiante.seccion && `- ${result.estudiante.seccion}`}
                </div>
              </div>
            )}
          </div>
        </div>

      </div>
    </div>
  );
}
