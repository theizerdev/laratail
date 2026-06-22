import { ReactNode, useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import LayoutProvider from '@/context/useLayoutContext';
import { AuthProvider } from '@/context/AuthContext';

import 'preline/preline';
import { IStaticMethods } from 'preline/preline';

declare global {
    interface Window {
        HSStaticMethods: IStaticMethods;
    }
}

const ProvidersWrapper = ({ children }: { children: ReactNode }) => {
    const location = useLocation();

    useEffect(() => {
        const timeout = setTimeout(() => {
            if (window.HSStaticMethods) {
                window.HSStaticMethods.autoInit();
            }
        }, 100);
        return () => clearTimeout(timeout);
    }, [location.pathname]);

    return (
        <AuthProvider>
            <LayoutProvider>
                {children}
            </LayoutProvider>
        </AuthProvider>
    );
};

export default ProvidersWrapper;
