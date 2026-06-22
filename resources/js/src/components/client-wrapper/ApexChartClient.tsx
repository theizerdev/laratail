import type { ApexOptions } from 'apexcharts';
import { useMemo, useState, useEffect } from 'react';
import ReactApexChart from 'react-apexcharts';

type PropsType = {
  type?: ApexChart['type'];
  height?: number | string;
  width?: number | string;
  getOptions: () => ApexOptions;
  series: ApexOptions['series'];
  className?: string;
};

const ApexChartClient = ({
  type,
  height,
  width = '100%',
  getOptions,
  series,
  className,
}: PropsType) => {
  const options = useMemo(() => {
    const baseOptions = getOptions();
    return {
      ...baseOptions,
      chart: {
        ...baseOptions.chart,
        animations: {
          enabled: false,
        },
      },
    };
  }, []);
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  if (!mounted) {
    return <div style={{ height, width }} className={className} />;
  }

  return (
    <ReactApexChart
      type={type}
      height={height}
      width={width}
      options={options}
      series={series}
      className={className}
    />
  );
};

export default ApexChartClient;
