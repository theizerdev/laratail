import { lazy } from 'react';

// dashboard

const Dashboard = lazy(() => import('@/app/(admin)/(app)/dashboard/index'));

// perfil
const Perfil = lazy(() => import('@/app/(admin)/(app)/perfil/index'));

// estudiantes
const EstudiantesIndex = lazy(() => import('@/app/(admin)/(app)/estudiantes/index'));

//auth
const BasicCreatePassword = lazy(() => import('@/app/(auth)/basic-create-password'));
const BasicLogin = lazy(() => import('@/app/(auth)/basic-login'));
const BasicRegister = lazy(() => import('@/app/(auth)/basic-register'));
const BasicResetPassword = lazy(() => import('@/app/(auth)/basic-reset-password'));
const BasicVerifyEmail = lazy(() => import('@/app/(auth)/basic-verify-email'));
const BasicLogout = lazy(() => import('@/app/(auth)/basic-logout'));
const BasicTwoStep = lazy(() => import('@/app/(auth)/basic-two-steps'));

//  landing
const OnePageLanding = lazy(() => import('@/app/(landing)/onepage-landing'));
const ProductLanding = lazy(() => import('@/app/(landing)/product-landing'));

//Other

const Error404 = lazy(() => import('@/app/(others)/404'));
const Error403 = lazy(() => import('@/app/(others)/403'));
const CommingSoon = lazy(() => import('@/app/(others)/coming-soon'));
const Maintenance = lazy(() => import('@/app/(others)/maintenance'));
const Offline = lazy(() => import('@/app/(others)/offline'));

// Seguridad (Usuarios y Roles)
const UsuariosIndex = lazy(() => import('@/app/(admin)/(app)/seguridad/usuarios/index'));
const RolesIndex = lazy(() => import('@/app/(admin)/(app)/seguridad/roles/index'));
const RoleCreate = lazy(() => import('@/app/(admin)/(app)/seguridad/roles/create'));
const RoleEdit = lazy(() => import('@/app/(admin)/(app)/seguridad/roles/edit'));
const PaisesIndex = lazy(() => import('@/app/(admin)/(app)/seguridad/paises/index'));

// Configuracion (Empresas)
const EmpresasIndex = lazy(() => import('@/app/(admin)/(app)/configuracion/empresas/index'));
const EmpresaCreate = lazy(() => import('@/app/(admin)/(app)/configuracion/empresas/create'));
const EmpresaEdit = lazy(() => import('@/app/(admin)/(app)/configuracion/empresas/edit'));

const SucursalesIndex = lazy(() => import('@/app/(admin)/(app)/configuracion/sucursales/index'));

// Integraciones
const IntegracionesIndex = lazy(() => import('@/app/(admin)/(app)/integraciones/index'));
const WhatsAppCrm = lazy(() => import('@/app/(admin)/(app)/integraciones/whatsapp/index'));

// Monitoreo
const MonitoreoDatabase = lazy(() => import('@/app/(admin)/(app)/monitoreo/database/index'));
const MonitoreoServidor = lazy(() => import('@/app/(admin)/(app)/monitoreo/servidor/index'));
const MonitoreoSesiones = lazy(() => import('@/app/(admin)/(app)/monitoreo/sesiones/index'));
const MonitoreoAuditoria = lazy(() => import('@/app/(admin)/(app)/monitoreo/auditoria/index'));

import ProtectedRoute from '@/components/ProtectedRoute';

export const layoutsRoutes = [
  { path: '/', name: 'Dashboard', element: <Dashboard /> },

  { path: '/index', name: 'Dashboard', element: <Dashboard /> },
  { path: '/perfil', name: 'Perfil', element: <Perfil /> },
  { path: '/estudiantes', name: 'Estudiantes', element: <EstudiantesIndex /> },

  { path: '/admin/seguridad/usuarios', name: 'Usuarios', element: <ProtectedRoute permission="users.view"><UsuariosIndex /></ProtectedRoute> },
  { path: '/admin/seguridad/roles', name: 'Roles', element: <ProtectedRoute permission="roles.view"><RolesIndex /></ProtectedRoute> },
  { path: '/admin/seguridad/roles/create', name: 'RoleCreate', element: <ProtectedRoute permission="roles.create"><RoleCreate /></ProtectedRoute> },
  { path: '/admin/seguridad/roles/:id/edit', name: 'RoleEdit', element: <ProtectedRoute permission="roles.edit"><RoleEdit /></ProtectedRoute> },
  { path: '/admin/seguridad/paises', name: 'Paises', element: <ProtectedRoute permission="countries.view"><PaisesIndex /></ProtectedRoute> },

  { path: '/admin/configuracion/empresas', name: 'Empresas', element: <ProtectedRoute permission="empresas.view"><EmpresasIndex /></ProtectedRoute> },
  { path: '/admin/configuracion/empresas/create', name: 'EmpresaCreate', element: <ProtectedRoute permission="empresas.create"><EmpresaCreate /></ProtectedRoute> },
  { path: '/admin/configuracion/empresas/:id/edit', name: 'EmpresaEdit', element: <ProtectedRoute permission="empresas.edit"><EmpresaEdit /></ProtectedRoute> },
  { path: '/admin/configuracion/sucursales', name: 'Sucursales', element: <ProtectedRoute permission="sucursales.view"><SucursalesIndex /></ProtectedRoute> },

  { path: '/admin/integraciones', name: 'Integraciones', element: <ProtectedRoute permission="integraciones.view"><IntegracionesIndex /></ProtectedRoute> },
  { path: '/admin/integraciones/whatsapp', name: 'WhatsAppCrm', element: <ProtectedRoute permission="integraciones.whatsapp.view"><WhatsAppCrm /></ProtectedRoute> },

  { path: '/admin/monitoreo/database', name: 'MonitoreoDatabase', element: <ProtectedRoute permission="monitoreo.view"><MonitoreoDatabase /></ProtectedRoute> },
  { path: '/admin/monitoreo/servidor', name: 'MonitoreoServidor', element: <ProtectedRoute permission="monitoreo.view"><MonitoreoServidor /></ProtectedRoute> },
  { path: '/admin/monitoreo/sesiones', name: 'MonitoreoSesiones', element: <ProtectedRoute permission="monitoreo.view"><MonitoreoSesiones /></ProtectedRoute> },
  { path: '/admin/monitoreo/auditoria', name: 'MonitoreoAuditoria', element: <ProtectedRoute permission="monitoreo.view"><MonitoreoAuditoria /></ProtectedRoute> },







];

export const singlePageRoutes = [
  { path: '/basic-login', name: 'BasicLogin', element: <BasicLogin /> },
  { path: '/basic-register', name: 'BasicRegister', element: <BasicRegister /> },
  { path: '/basic-create-password', name: 'BasicCreatePassword', element: <BasicCreatePassword /> },
  { path: '/basic-reset-password', name: 'BasicResetPassword', element: <BasicResetPassword /> },
  { path: '/basic-verify-email', name: 'BasicVerifyEmail', element: <BasicVerifyEmail /> },
  { path: '/basic-logout', name: 'BasicLogout', element: <BasicLogout /> },
  { path: '/basic-two-steps', name: 'BasicTwoStep', element: <BasicTwoStep /> },

  { path: '/onepage-landing', name: 'OnePageLanding', element: <OnePageLanding /> },
  { path: '/product-landing', name: 'ProductLanding', element: <ProductLanding /> },

  { path: '/403', name: '403', element: <Error403 /> },
  { path: '/404', name: '404', element: <Error404 /> },
  { path: '/coming-soon', name: 'ComingSoon', element: <CommingSoon /> },
  { path: '/maintenance', name: 'Maintenance', element: <Maintenance /> },
  { path: '/offline', name: 'Offline', element: <Offline /> },
];
