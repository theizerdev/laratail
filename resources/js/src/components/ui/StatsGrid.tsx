import { ReactNode } from 'react';

export interface StatItem {
  label: string;
  value: string | number;
  icon: ReactNode;
  iconBgClass: string;
}

interface StatsGridProps {
  stats: StatItem[];
}

export default function StatsGrid({ stats }: StatsGridProps) {
  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      {stats.map((stat, i) => (
        <div key={i} className="bg-white border border-gray-100 rounded-xl p-5 flex items-center gap-4 shadow-sm hover:shadow-md transition-shadow">
          <div className={`p-3 ${stat.iconBgClass} rounded-xl flex items-center justify-center size-12`}>
            {stat.icon}
          </div>
          <div>
            <p className="text-[10px] tracking-wider font-semibold text-gray-400 uppercase">{stat.label}</p>
            <h3 className="text-xl font-bold text-gray-800 mt-0.5">{stat.value}</h3>
          </div>
        </div>
      ))}
    </div>
  );
}
