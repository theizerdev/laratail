import { ReactNode } from 'react';

interface TableCardProps {
  title: string;
  icon: ReactNode;
  children: ReactNode;
}

export default function TableCard({ title, icon, children }: TableCardProps) {
  return (
    <div className="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden">
      {/* Header */}
      <div className="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
        {icon}
        <h2 className="text-xs font-bold text-gray-600 uppercase tracking-wider">{title}</h2>
      </div>

      {/* Content */}
      <div className="overflow-x-auto">
        {children}
      </div>
    </div>
  );
}
