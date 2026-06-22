import { ReactNode } from 'react';
import { Link } from 'react-router';

// Custom inline home icon
const HomeIcon = ({ className }: { className?: string }) => (
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}>
    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
    <polyline points="9 22 9 12 15 12 15 22" />
  </svg>
);

interface ModuleHeaderProps {
  title: string;
  description: string;
  icon: ReactNode;
  bannerBgClass?: string;
  breadcrumbs?: Array<{ label: string; active?: boolean; href?: string }>;
  actionButton?: {
    label: string;
    onClick: () => void;
  };
}

export default function ModuleHeader({
  title,
  description,
  icon,
  bannerBgClass = 'bg-[#6366f1]',
  breadcrumbs = [],
  actionButton
}: ModuleHeaderProps) {
  return (
    <div className="mb-6">
      {/* Breadcrumbs */}
      <div className="flex items-center gap-2 text-xs text-gray-500 mb-4 select-none">
        <Link to="/index" className="hover:text-gray-700 transition-colors flex items-center gap-1">
          <HomeIcon className="size-3.5 text-gray-400" />
          <span>Dashboard</span>
        </Link>
        {breadcrumbs.map((bc, i) => (
          <span key={i} className="flex items-center gap-2">
            <span className="text-gray-300">/</span>
            {bc.href ? (
              <Link to={bc.href} className="hover:text-gray-700 transition-colors">
                {bc.label}
              </Link>
            ) : (
              <span className={bc.active ? 'text-gray-600 font-medium' : ''}>{bc.label}</span>
            )}
          </span>
        ))}
      </div>

      {/* Banner */}
      <div className={`w-full ${bannerBgClass} rounded-2xl p-6 md:p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 shadow-sm`}>
        <div className="flex items-center gap-4">
          <div className="p-3 bg-white/10 rounded-xl text-white">
            {icon}
          </div>
          <div>
            <h1 className="text-2xl md:text-3xl font-bold text-white tracking-tight">{title}</h1>
            <p className="text-xs md:text-sm text-indigo-100 font-light mt-0.5">{description}</p>
          </div>
        </div>
        {actionButton && (
          <button 
            onClick={actionButton.onClick}
            className="bg-white hover:bg-gray-50 text-gray-800 font-semibold px-4 py-2.5 rounded-lg text-xs shadow-sm transition-colors border border-gray-200 flex items-center gap-1.5 focus:outline-none"
          >
            <span className="text-sm font-bold">+</span> {actionButton.label}
          </button>
        )}
      </div>
    </div>
  );
}
