import { ReactNode, useEffect } from 'react';
import Footer from '@/components/layouts/Footer';
import Sidebar from '@/components/layouts/SideNav';
import Topbar from '@/components/layouts/topbar';
import Customizer from '@/components/layouts/customizer';

const PageWrapper = ({ children }: { children: ReactNode }) => {
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
          {children}
          <Footer />
        </div>
      </div>
      <Customizer />
    </>
  );
};

export default PageWrapper;
