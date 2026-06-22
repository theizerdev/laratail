import React, { useRef } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { QRCodeSVG } from 'qrcode.react';
import { Download, User, Phone, Mail, GraduationCap, Calendar, CreditCard } from 'lucide-react';

interface EstudianteDetalleModalProps {
  isOpen: boolean;
  onClose: () => void;
  estudiante: any | null;
}

export default function EstudianteDetalleModal({ isOpen, onClose, estudiante }: EstudianteDetalleModalProps) {
  const qrRef = useRef<SVGSVGElement>(null);

  if (!estudiante) return null;

  const downloadQR = () => {
    if (!qrRef.current) return;
    const svg = qrRef.current;
    const svgData = new XMLSerializer().serializeToString(svg);
    const canvas = document.createElement("canvas");
    const ctx = canvas.getContext("2d");
    const img = new Image();
    
    img.onload = () => {
      // Add padding and white background
      canvas.width = img.width + 40;
      canvas.height = img.height + 40;
      if (ctx) {
        ctx.fillStyle = "white";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 20, 20);
      }
      const pngFile = canvas.toDataURL("image/png");
      const downloadLink = document.createElement("a");
      downloadLink.download = `QR_${estudiante.nombre}_${estudiante.apellido}.png`;
      downloadLink.href = `${pngFile}`;
      downloadLink.click();
    };
    
    img.src = "data:image/svg+xml;base64," + btoa(unescape(encodeURIComponent(svgData)));
  };

  const getInitials = (nombre: string, apellido: string) => {
    return `${nombre?.charAt(0) || ''}${apellido?.charAt(0) || ''}`.toUpperCase();
  };

  const codigoAcceso = estudiante.codigo_acceso || estudiante.dni || `STU-${estudiante.id}`;

  return (
    <Dialog open={isOpen} onOpenChange={(open) => !open && onClose()}>
      <DialogContent className="sm:max-w-4xl max-h-[90vh] overflow-y-auto p-0 border-0 rounded-2xl shadow-2xl">
        <div className="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 text-white rounded-t-2xl flex items-center justify-between">
          <div>
            <h2 className="text-2xl font-bold">Ficha del Estudiante</h2>
            <p className="text-blue-100 opacity-90 mt-1">Detalles y credencial de acceso</p>
          </div>
        </div>

        <div className="p-8">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {/* Left Column: Student Profile */}
            <div className="md:col-span-1 flex flex-col items-center">
              <div className="relative w-40 h-40 rounded-full overflow-hidden border-4 border-white shadow-xl bg-white mb-6">
                {estudiante.foto_url ? (
                  <img src={estudiante.foto_url} alt="Foto Estudiante" className="w-full h-full object-cover" />
                ) : (
                  <div className="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-400 text-5xl font-bold">
                    {getInitials(estudiante.nombre, estudiante.apellido)}
                  </div>
                )}
              </div>
              
              <h3 className="text-2xl font-bold text-gray-900 text-center">{estudiante.nombre} {estudiante.apellido}</h3>
              <span className="inline-flex items-center px-3 py-1 mt-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                Estudiante
              </span>

              <div className="w-full mt-8 space-y-4">
                <div className="flex items-center text-sm text-gray-600">
                  <CreditCard className="size-4 mr-3 text-gray-400" />
                  <span className="font-medium text-gray-900 w-24">Documento:</span>
                  <span className="truncate">{estudiante.dni || 'No registrado'}</span>
                </div>
                <div className="flex items-center text-sm text-gray-600">
                  <GraduationCap className="size-4 mr-3 text-gray-400" />
                  <span className="font-medium text-gray-900 w-24">Grado/Sec:</span>
                  <span className="truncate">{estudiante.grado} {estudiante.seccion && `- ${estudiante.seccion}`}</span>
                </div>
                <div className="flex items-center text-sm text-gray-600">
                  <Calendar className="size-4 mr-3 text-gray-400" />
                  <span className="font-medium text-gray-900 w-24">Edad:</span>
                  <span>{estudiante.edad ? `${estudiante.edad} años` : 'No registrada'}</span>
                </div>
                {estudiante.email && (
                  <div className="flex items-center text-sm text-gray-600">
                    <Mail className="size-4 mr-3 text-gray-400" />
                    <span className="font-medium text-gray-900 w-24">Email:</span>
                    <span className="truncate">{estudiante.email}</span>
                  </div>
                )}
              </div>
            </div>

            {/* Right Column: QR and Representatives */}
            <div className="md:col-span-2 flex flex-col">
              
              {/* Credential Card */}
              <div className="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border p-6 flex flex-col md:flex-row items-center justify-between shadow-sm mb-8">
                <div className="text-center md:text-left mb-6 md:mb-0">
                  <h4 className="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Código de Acceso</h4>
                  <p className="text-3xl font-mono font-bold text-indigo-700 mb-2">{codigoAcceso}</p>
                  <p className="text-sm text-gray-500">Utilice este QR para registrar entradas y salidas.</p>
                  
                  <Button onClick={downloadQR} className="mt-6 shadow-md" variant="default">
                    <Download className="mr-2 size-4" />
                    Descargar Código QR
                  </Button>
                </div>
                <div className="bg-white p-4 rounded-xl shadow-sm border">
                  <QRCodeSVG
                    id="student-qr-code"
                    value={codigoAcceso}
                    size={150}
                    level="H"
                    includeMargin={false}
                    ref={qrRef}
                  />
                </div>
              </div>

              {/* Representative Information */}
              {estudiante.representantes && estudiante.representantes.length > 0 && (
                <div>
                  <h4 className="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">Información del Representante</h4>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {estudiante.representantes.map((rep: any, index: number) => (
                      <div key={index} className="bg-white border rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
                        <div className="flex items-start justify-between mb-3">
                          <h5 className="font-bold text-gray-900 truncate pr-2">{rep.nombre}</h5>
                          <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700">
                            {rep.pivot?.relacion || 'Representante'}
                          </span>
                        </div>
                        <div className="space-y-2 mt-4">
                          <div className="flex items-center text-sm text-gray-600">
                            <CreditCard className="size-4 mr-2 text-gray-400" />
                            <span>{rep.dni || 'Sin documento'}</span>
                          </div>
                          <div className="flex items-center text-sm text-gray-600">
                            <Phone className="size-4 mr-2 text-gray-400" />
                            <span>{rep.telefono || 'Sin teléfono'}</span>
                          </div>
                          {rep.email && (
                            <div className="flex items-center text-sm text-gray-600">
                              <Mail className="size-4 mr-2 text-gray-400" />
                              <span className="truncate">{rep.email}</span>
                            </div>
                          )}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {(!estudiante.representantes || estudiante.representantes.length === 0) && estudiante.edad !== null && estudiante.edad >= 18 && (
                 <div className="bg-blue-50 text-blue-800 rounded-xl p-5 border border-blue-100">
                   <p className="text-sm font-medium">Estudiante mayor de edad. No requiere representante obligatorio.</p>
                 </div>
              )}
            </div>
          </div>
        </div>

        <div className="bg-gray-50 px-8 py-4 border-t flex justify-end rounded-b-2xl">
          <Button onClick={onClose} variant="outline" className="min-w-[120px]">
            Cerrar
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  );
}
