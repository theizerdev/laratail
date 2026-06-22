import { ReactNode } from 'react';
import { LuSearch, LuRefreshCw, LuFileSpreadsheet } from 'react-icons/lu';

interface FilterBarProps {
  searchTerm?: string;
  onSearchChange?: (val: string) => void;
  searchPlaceholder?: string;
  onClear?: () => void;
  onExport?: () => void;
  children?: ReactNode;
}

export default function FilterBar({
  searchTerm,
  onSearchChange,
  searchPlaceholder = 'Buscar...',
  onClear,
  onExport,
  children
}: FilterBarProps) {
  return (
    <div className="bg-white border border-gray-100 shadow-sm rounded-xl p-5 mb-6">
      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
        
        {/* Search Field */}
        {onSearchChange !== undefined && (
          <div className="lg:col-span-2">
            <label className="block text-[11px] font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Buscar</label>
            <div className="relative">
              <input 
                type="text" 
                className="w-full text-sm border border-gray-200 rounded-lg pl-9 pr-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 bg-white" 
                placeholder={searchPlaceholder} 
                value={searchTerm || ''}
                onChange={(e) => onSearchChange(e.target.value)}
              />
              <LuSearch className="absolute left-3 top-2.5 text-gray-400 size-4" />
            </div>
          </div>
        )}

        {/* Custom filters dropdowns passed as children */}
        {children}

        {/* Action Buttons */}
        <div className="flex gap-2 w-full">
          {onClear && (
            <button 
              onClick={onClear}
              className="flex-1 flex items-center justify-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold py-2 px-3 rounded-lg transition-colors border border-gray-200"
            >
              <LuRefreshCw className="size-3.5" /> Limpiar
            </button>
          )}
          {onExport && (
            <button 
              onClick={onExport}
              className="flex-1 flex items-center justify-center gap-1.5 bg-[#e6ffd6] hover:bg-[#d6fca6] text-[#2e800f] text-xs font-semibold py-2 px-3 rounded-lg transition-colors border border-[#c3f0ac]"
            >
              <LuFileSpreadsheet className="size-3.5" /> Exportar
            </button>
          )}
        </div>

      </div>
    </div>
  );
}
