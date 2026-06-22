import { useState, useEffect } from 'react';
import axios from '@/lib/axios';
import { LuX } from 'react-icons/lu';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

interface PaisModalProps {
  isOpen: boolean;
  onClose: () => void;
  pais: any | null;
  onSuccess: () => void;
}

export default function PaisModal({ isOpen, onClose, pais, onSuccess }: PaisModalProps) {
  const [formData, setFormData] = useState({
    nombre: '',
    codigo_iso2: '',
    codigo_iso3: '',
    codigo_telefonico: '',
    moneda_principal: '',
    idioma_principal: '',
    continente: '',
    zona_horaria: '',
    formato_fecha: 'dd/mm/yyyy',
    formato_moneda: '1.234,56',
    impuesto_predeterminado: 0.00,
    separador_miles: '.',
    separador_decimales: ',',
    decimales_moneda: 2,
    latitud: '',
    longitud: '',
    activo: true,
  });

  const [errors, setErrors] = useState<any>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    if (pais) {
      setFormData({
        nombre: pais.nombre || '',
        codigo_iso2: pais.codigo_iso2 || '',
        codigo_iso3: pais.codigo_iso3 || '',
        codigo_telefonico: pais.codigo_telefonico || '',
        moneda_principal: pais.moneda_principal || '',
        idioma_principal: pais.idioma_principal || '',
        continente: pais.continente || '',
        zona_horaria: pais.zona_horaria || '',
        formato_fecha: pais.formato_fecha || 'dd/mm/yyyy',
        formato_moneda: pais.formato_moneda || '1.234,56',
        impuesto_predeterminado: Number(pais.impuesto_predeterminado) || 0.00,
        separador_miles: pais.separador_miles || '.',
        separador_decimales: pais.separador_decimales || ',',
        decimales_moneda: Number(pais.decimales_moneda) || 2,
        latitud: pais.latitud !== null ? String(pais.latitud) : '',
        longitud: pais.longitud !== null ? String(pais.longitud) : '',
        activo: pais.activo !== false,
      });
    } else {
      setFormData({
        nombre: '',
        codigo_iso2: '',
        codigo_iso3: '',
        codigo_telefonico: '',
        moneda_principal: '',
        idioma_principal: '',
        continente: '',
        zona_horaria: '',
        formato_fecha: 'dd/mm/yyyy',
        formato_moneda: '1.234,56',
        impuesto_predeterminado: 0.00,
        separador_miles: '.',
        separador_decimales: ',',
        decimales_moneda: 2,
        latitud: '',
        longitud: '',
        activo: true,
      });
    }
    setErrors({});
  }, [pais, isOpen]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value, type } = e.target;
    const checked = (e.target as HTMLInputElement).checked;

    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setErrors({});

    const dataToSend = {
      ...formData,
      latitud: formData.latitud === '' ? null : Number(formData.latitud),
      longitud: formData.longitud === '' ? null : Number(formData.longitud),
      impuesto_predeterminado: Number(formData.impuesto_predeterminado),
      decimales_moneda: Number(formData.decimales_moneda),
    };

    try {
      if (pais) {
        await axios.put(`/api/admin/paises/${pais.id}`, dataToSend);
      } else {
        await axios.post('/api/admin/paises', dataToSend);
      }
      onSuccess();
      onClose();
    } catch (error: any) {
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors);
      } else {
        alert('Ocurrió un error guardando el país.');
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-xl shadow-xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]">
        {/* Modal Header */}
        <div className="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-50">
          <h3 className="text-sm font-bold text-gray-700 uppercase tracking-wider">
            {pais ? 'Editar País' : 'Nuevo País'}
          </h3>
          <button onClick={onClose} className="p-1 hover:bg-gray-100 rounded-lg transition-colors text-gray-400 hover:text-gray-600 focus:outline-none">
            <LuX className="size-5" />
          </button>
        </div>

        {/* Modal Body / Form */}
        <form onSubmit={handleSubmit} className="flex-1 overflow-y-auto p-6 space-y-6 no-scrollbar">
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {/* Nombre */}
            <div className="md:col-span-2">
              <label className="block text-xs font-semibold text-gray-600 mb-1 uppercase">Nombre del País</label>
              <Input 
                type="text" 
                name="nombre" 
                value={formData.nombre} 
                onChange={handleChange} 
                className={errors.nombre ? 'border-red-500 focus-visible:ring-red-500' : ''}
                required 
              />
              {errors.nombre && <p className="text-xs text-red-500 mt-1">{errors.nombre[0]}</p>}
            </div>

            {/* Código ISO 2 */}
            <div>
              <label className="block text-xs font-semibold text-gray-600 mb-1 uppercase">Código ISO 2</label>
              <Input 
                type="text" 
                name="codigo_iso2" 
                value={formData.codigo_iso2} 
                onChange={handleChange} 
                placeholder="Ej. VE"
                maxLength={2}
                className={errors.codigo_iso2 ? 'border-red-500 focus-visible:ring-red-500' : ''}
                required 
              />
              {errors.codigo_iso2 && <p className="text-xs text-red-500 mt-1">{errors.codigo_iso2[0]}</p>}
            </div>

            {/* Código ISO 3 */}
            <div>
              <label className="block text-xs font-semibold text-gray-600 mb-1 uppercase">Código ISO 3</label>
              <Input 
                type="text" 
                name="codigo_iso3" 
                value={formData.codigo_iso3} 
                onChange={handleChange} 
                placeholder="Ej. VEN"
                maxLength={3}
                className={errors.codigo_iso3 ? 'border-red-500 focus-visible:ring-red-500' : ''}
                required 
              />
              {errors.codigo_iso3 && <p className="text-xs text-red-500 mt-1">{errors.codigo_iso3[0]}</p>}
            </div>

            {/* Código Telefónico */}
            <div>
              <label className="block text-xs font-semibold text-gray-600 mb-1 uppercase">Código Telefónico</label>
              <Input 
                type="text" 
                name="codigo_telefonico" 
                value={formData.codigo_telefonico} 
                onChange={handleChange} 
                placeholder="Ej. +58"
              />
            </div>

            {/* Moneda Principal */}
            <div>
              <label className="block text-xs font-semibold text-gray-600 mb-1 uppercase">Moneda Principal</label>
              <Input 
                type="text" 
                name="moneda_principal" 
                value={formData.moneda_principal} 
                onChange={handleChange} 
                placeholder="Ej. USD o VES"
              />
            </div>

            {/* Idioma Principal */}
            <div>
              <label className="block text-xs font-semibold text-gray-600 mb-1 uppercase">Idioma Principal</label>
              <Input 
                type="text" 
                name="idioma_principal" 
                value={formData.idioma_principal} 
                onChange={handleChange} 
                placeholder="Ej. es o en"
              />
            </div>

            {/* Continente */}
            <div>
              <label className="block text-xs font-semibold text-gray-600 mb-1 uppercase">Continente</label>
              <select 
                name="continente" 
                value={formData.continente} 
                onChange={handleChange} 
                className="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 bg-white"
              >
                <option value="">Seleccionar continente</option>
                <option value="América del Norte">América del Norte</option>
                <option value="América del Sur">América del Sur</option>
                <option value="América Central">América Central</option>
                <option value="Europa">Europa</option>
                <option value="Asia">Asia</option>
                <option value="África">África</option>
                <option value="Oceanía">Oceanía</option>
              </select>
            </div>

            {/* Zona Horaria */}
            <div>
              <label className="block text-xs font-semibold text-gray-600 mb-1 uppercase">Zona Horaria</label>
              <Input 
                type="text" 
                name="zona_horaria" 
                value={formData.zona_horaria} 
                onChange={handleChange} 
                placeholder="Ej. America/Caracas"
              />
            </div>

            {/* Formato Fecha */}
            <div>
              <label className="block text-xs font-semibold text-gray-600 mb-1 uppercase">Formato Fecha</label>
              <select 
                name="formato_fecha" 
                value={formData.formato_fecha} 
                onChange={handleChange} 
                className="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 bg-white"
              >
                <option value="dd/mm/yyyy">dd/mm/yyyy</option>
                <option value="yyyy-mm-dd">yyyy-mm-dd</option>
                <option value="mm/dd/yyyy">mm/dd/yyyy</option>
              </select>
            </div>

            {/* Latitud */}
            <div>
              <label className="block text-xs font-semibold text-gray-600 mb-1 uppercase">Latitud</label>
              <Input 
                type="number" 
                step="any"
                name="latitud" 
                value={formData.latitud} 
                onChange={handleChange} 
                placeholder="Ej. 10.4806"
              />
            </div>

            {/* Longitud */}
            <div>
              <label className="block text-xs font-semibold text-gray-600 mb-1 uppercase">Longitud</label>
              <Input 
                type="number" 
                step="any"
                name="longitud" 
                value={formData.longitud} 
                onChange={handleChange} 
                placeholder="Ej. -66.9036"
              />
            </div>

            {/* Impuesto Predeterminado */}
            <div>
              <label className="block text-xs font-semibold text-gray-600 mb-1 uppercase">Impuesto Predeterminado (%)</label>
              <Input 
                type="number" 
                step="0.01"
                name="impuesto_predeterminado" 
                value={formData.impuesto_predeterminado} 
                onChange={handleChange} 
              />
            </div>

            {/* Separador de Miles */}
            <div>
              <label className="block text-xs font-semibold text-gray-600 mb-1 uppercase">Separador Miles</label>
              <select 
                name="separador_miles" 
                value={formData.separador_miles} 
                onChange={handleChange} 
                className="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 bg-white"
              >
                <option value=".">Punto (.)</option>
                <option value=",">Coma (,)</option>
                <option value="">Ninguno</option>
              </select>
            </div>

            {/* Separador de Decimales */}
            <div>
              <label className="block text-xs font-semibold text-gray-600 mb-1 uppercase">Separador Decimales</label>
              <select 
                name="separador_decimales" 
                value={formData.separador_decimales} 
                onChange={handleChange} 
                className="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 bg-white"
              >
                <option value=",">Coma (,)</option>
                <option value=".">Punto (.)</option>
              </select>
            </div>

            {/* Decimales de Moneda */}
            <div>
              <label className="block text-xs font-semibold text-gray-600 mb-1 uppercase">Decimales de Moneda</label>
              <Input 
                type="number" 
                name="decimales_moneda" 
                value={formData.decimales_moneda} 
                onChange={handleChange} 
                min={0}
                max={5}
              />
            </div>

            {/* Activo Switch */}
            <div className="flex items-center gap-3 md:col-span-2 pt-2 border-t border-gray-50">
              <label className="relative inline-flex items-center cursor-pointer select-none">
                <input 
                  type="checkbox" 
                  name="activo"
                  className="sr-only peer" 
                  checked={formData.activo}
                  onChange={handleChange}
                />
                <div className="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#0f3d4c]"></div>
              </label>
              <span className="text-xs font-semibold text-gray-600 uppercase tracking-wide">País Activo</span>
            </div>

          </div>

          <div className="flex justify-end gap-3 pt-6 border-t border-gray-100">
            <Button 
              type="button" 
              variant="outline"
              onClick={onClose} 
            >
              Cancelar
            </Button>
            <Button 
              type="submit" 
              disabled={isSubmitting} 
              className="bg-[#6366f1] hover:bg-indigo-700 text-white"
            >
              {isSubmitting ? 'Guardando...' : 'Guardar'}
            </Button>
          </div>

        </form>
      </div>
    </div>
  );
}
