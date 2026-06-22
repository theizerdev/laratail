import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import axios from '@/lib/axios';

interface User {
  id: number;
  name: string;
  email: string;
  roles_list?: string[];
  permissions_list?: string[];
}

interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (user: User) => void;
  logout: () => Promise<void>;
  checkAuth: () => Promise<void>;
  hasRole: (role: string) => boolean;
  can: (permission: string) => boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider = ({ children }: { children: ReactNode }) => {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const checkAuth = async () => {
    try {
      setIsLoading(true);
      const response = await axios.get('/api/user');
      setUser(response.data);
    } catch (error) {
      setUser(null);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    checkAuth();
  }, []);

  const login = (user: User) => {
    setUser(user);
  };

  const logout = async () => {
    try {
      await axios.post('/api/logout');
    } finally {
      setUser(null);
      // Redirigir o limpiar estado si es necesario
    }
  };

  const hasRole = (role: string) => {
    if (!user || !user.roles_list) return false;
    return user.roles_list.includes(role);
  };

  const can = (permission: string) => {
    if (!user || !user.permissions_list) return false;
    // Si queremos darle superpoderes al super-admin:
    if (user.roles_list?.includes('super-admin')) return true;
    return user.permissions_list.includes(permission);
  };

  return (
    <AuthContext.Provider value={{ user, isAuthenticated: !!user, isLoading, login, logout, checkAuth, hasRole, can }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
