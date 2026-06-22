import { ReactNode, useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import Footer from '@/components/layouts/Footer';
import Sidebar from '@/components/layouts/SideNav';
import Topbar from '@/components/layouts/topbar';
import Customizer from '@/components/layouts/customizer';

const PageWrapper = ({ children }: { children: ReactNode }) => {
  const location = useLocation();

  useEffect(() => {
    // Cuando el PageWrapper se monta (es decir, el AuthLoader desaparece),
    // debemos forzar a Preline a re-inicializar para que enganche eventos en SideNav, Accordions, etc.
    const timeout = setTimeout(() => {
      if (window.HSStaticMethods) {
        window.HSStaticMethods.autoInit();
      }
    }, 100);
    return () => clearTimeout(timeout);
  }, []);

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

export default PageWrapper;
