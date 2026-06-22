import type { ReactNode } from 'react';
import { useLocation } from 'react-router-dom';
import Footer from '@/components/layouts/Footer';
import Sidebar from '@/components/layouts/SideNav';
import Topbar from '@/components/layouts/topbar';
import Customizer from '@/components/layouts/customizer';

const Layout = ({ children }: { children: ReactNode }) => {
  const location = useLocation();

  return (
    <>
      <div className="wrapper">
        <Sidebar />
        <div className="page-content">
          <Topbar />
          <div key={location.pathname} className="page-transition">
            {children}
          </div>
          <Footer />
        </div>
      </div>
      <Customizer />
    </>
  );
};

export default Layout;
