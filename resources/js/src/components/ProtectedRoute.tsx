import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext';
import React from 'react';

const ProtectedRoute = ({ children, permission }: { children: React.ReactNode, permission?: string }) => {
  const { isAuthenticated, isLoading, can } = useAuth();
  const location = useLocation();

  if (isLoading) {
    // Puedes reemplazar esto con un componente de Spinner/Loader bonito
    return (
      <div className="flex justify-center items-center h-screen bg-card">
        <div className="animate-spin inline-block size-8 border-[3px] border-current border-t-transparent text-primary rounded-full" role="status" aria-label="loading">
          <span className="sr-only">Loading...</span>
        </div>
      </div>
    );
  }

  if (!isAuthenticated) {
    // Redirigir al login si no está autenticado, guardando la ruta intentada
    return <Navigate to="/basic-login" state={{ from: location }} replace />;
  }

  if (permission && !can(permission)) {
    // Redirigir a 403 si no tiene permiso
    return <Navigate to="/403" replace />;
  }

  return <>{children}</>;
};

export default ProtectedRoute;
