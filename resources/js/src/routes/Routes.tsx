import { lazy } from 'react';

// admin Ecommerce

const Cart = lazy(() => import('@/app/(admin)/(app)/(ecommerce)/cart'));
const Checkout = lazy(() => import('@/app/(admin)/(app)/(ecommerce)/checkout'));
const OrderOverview = lazy(() => import('@/app/(admin)/(app)/(ecommerce)/order-overview'));
const Orders = lazy(() => import('@/app/(admin)/(app)/(ecommerce)/orders'));
const ProductCreate = lazy(() => import('@/app/(admin)/(app)/(ecommerce)/product-create'));
const ProductGrid = lazy(() => import('@/app/(admin)/(app)/(ecommerce)/product-grid'));
const ProductList = lazy(() => import('@/app/(admin)/(app)/(ecommerce)/product-list'));
const ProductOverview = lazy(() => import('@/app/(admin)/(app)/(ecommerce)/product-overview'));
const Sellers = lazy(() => import('@/app/(admin)/(app)/(ecommerce)/sellers'));

// admin Hr

const Attendances = lazy(() => import('@/app/(admin)/(app)/(hr)/attendance'));
const AttemdanceMain = lazy(() => import('@/app/(admin)/(app)/(hr)/attendance-main'));
const CreateLeave = lazy(() => import('@/app/(admin)/(app)/(hr)/create-leave'));
const CreateLeaveEmployee = lazy(() => import('@/app/(admin)/(app)/(hr)/create-leave-employee'));
const CreatePayslip = lazy(() => import('@/app/(admin)/(app)/(hr)/create-payslip'));
const Department = lazy(() => import('@/app/(admin)/(app)/(hr)/department'));
const Employee = lazy(() => import('@/app/(admin)/(app)/(hr)/employee'));
const Holidays = lazy(() => import('@/app/(admin)/(app)/(hr)/holidays'));
const Leave = lazy(() => import('@/app/(admin)/(app)/(hr)/leave'));
const LeaveEmployee = lazy(() => import('@/app/(admin)/(app)/(hr)/leave-employee'));
const PayrollEmplyoeeSalary = lazy(
  () => import('@/app/(admin)/(app)/(hr)/payroll-employee-salary')
);
const PayRollSlip = lazy(() => import('@/app/(admin)/(app)/(hr)/payroll-payslip'));
const SalesEstimate = lazy(() => import('@/app/(admin)/(app)/(hr)/sales-estimates'));
const SalesExpense = lazy(() => import('@/app/(admin)/(app)/(hr)/sales-expenses'));
const SalePayment = lazy(() => import('@/app/(admin)/(app)/(hr)/sales-payments'));

// admin invoice

const InvoiceAddNew = lazy(() => import('@/app/(admin)/(app)/(invoice)/add-new'));
const InvoiceList = lazy(() => import('@/app/(admin)/(app)/(invoice)/list'));
const InvoiceOverview = lazy(() => import('@/app/(admin)/(app)/(invoice)/overview'));

// USers

const UserGrid = lazy(() => import('@/app/(admin)/(app)/(users)/users-grid'));
const UserList = lazy(() => import('@/app/(admin)/(app)/(users)/users-list'));

const Calender = lazy(() => import('@/app/(admin)/(app)/calendar'));

const MailBox = lazy(() => import('@/app/(admin)/(app)/mailbox'));
const Notes = lazy(() => import('@/app/(admin)/(app)/notes'));

// dashboard
const Analytics = lazy(() => import('@/app/(admin)/(dashboards)/analytics'));
const Email = lazy(() => import('@/app/(admin)/(dashboards)/email'));
const Hr = lazy(() => import('@/app/(admin)/(dashboards)/hr'));
const Ecommerce = lazy(() => import('@/app/(admin)/(dashboards)/index'));

// layouts
const DarkMode = lazy(() => import('@/app/(admin)/(layouts)/dark-mode'));
const RTL = lazy(() => import('@/app/(admin)/(layouts)/rtl-mode'));
const SideNavCompact = lazy(() => import('@/app/(admin)/(layouts)/sidenav-compact'));
const SideNavDark = lazy(() => import('@/app/(admin)/(layouts)/sidenav-dark'));
const SideNavHidden = lazy(() => import('@/app/(admin)/(layouts)/sidenav-hidden'));
const SideNavHover = lazy(() => import('@/app/(admin)/(layouts)/sidenav-hover'));
const SideNavHoverActive = lazy(() => import('@/app/(admin)/(layouts)/sidenav-hover-active'));
const SideOffcanvas = lazy(() => import('@/app/(admin)/(layouts)/sidenav-offcanvas'));
const SideNavSmall = lazy(() => import('@/app/(admin)/(layouts)/sidenav-small'));

//Pages

const Faq = lazy(() => import('@/app/(admin)/(pages)/faqs'));
const Pricing = lazy(() => import('@/app/(admin)/(pages)/pricing'));
const Starter = lazy(() => import('@/app/(admin)/(pages)/starter'));
const Timeline = lazy(() => import('@/app/(admin)/(pages)/timeline'));

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
  { path: '/', name: 'Ecommerce', element: <Ecommerce /> },

  { path: '/index', name: 'Ecommerce', element: <Ecommerce /> },
  { path: '/cart', name: 'Cart', element: <Cart /> },
  { path: '/checkout', name: 'Checkout', element: <Checkout /> },
  { path: '/order-overview', name: 'OrderOverview', element: <OrderOverview /> },
  { path: '/orders', name: 'Orders', element: <Orders /> },
  { path: '/product-create', name: 'ProductCreate', element: <ProductCreate /> },
  { path: '/product-grid', name: 'ProductGrid', element: <ProductGrid /> },
  { path: '/product-list', name: 'ProductList', element: <ProductList /> },
  { path: '/product-overview', name: 'ProductOverview', element: <ProductOverview /> },
  { path: '/sellers', name: 'Sellers', element: <Sellers /> },

  { path: '/attendance', name: 'Attendances', element: <Attendances /> },
  { path: '/attendance-main', name: 'AttemdanceMain', element: <AttemdanceMain /> },
  { path: '/create-leave', name: 'CreateLeave', element: <CreateLeave /> },
  { path: '/create-leave-employee', name: 'CreateLeaveEmployee', element: <CreateLeaveEmployee /> },
  { path: '/create-payslip', name: 'CreatePayslip', element: <CreatePayslip /> },
  { path: '/department', name: 'Department', element: <Department /> },
  { path: '/employee', name: 'Employee', element: <Employee /> },
  { path: '/holidays', name: 'Holidays', element: <Holidays /> },
  { path: '/leave', name: 'Leave', element: <Leave /> },
  { path: '/leave-employee', name: 'LeaveEmployee', element: <LeaveEmployee /> },
  {
    path: '/payroll-employee-salary',
    name: 'PayrollEmplyoeeSalary',
    element: <PayrollEmplyoeeSalary />,
  },
  { path: '/payroll-payslip', name: 'PayRollSlip', element: <PayRollSlip /> },
  { path: '/sales-estimates', name: 'SalesEstimate', element: <SalesEstimate /> },
  { path: '/sales-expenses', name: 'SalesExpense', element: <SalesExpense /> },
  { path: '/sales-payments', name: 'SalePayment', element: <SalePayment /> },

  { path: '/add-new', name: 'InvoiceAddNew', element: <InvoiceAddNew /> },
  { path: '/list', name: 'InvoiceList', element: <InvoiceList /> },
  { path: '/overview', name: 'InvoiceOverview', element: <InvoiceOverview /> },

  { path: '/users-grid', name: 'UserGrid', element: <UserGrid /> },
  { path: '/users-list', name: 'UserList', element: <UserList /> },

  { path: '/calendar', name: 'Calender', element: <Calender /> },

  { path: '/mailbox', name: 'MailBox', element: <MailBox /> },
  { path: '/notes', name: 'Notes', element: <Notes /> },

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


  { path: '/analytics', name: 'Analytics', element: <Analytics /> },
  { path: '/', name: 'Ecommerce', element: <Ecommerce /> },
  { path: '/email', name: 'Email', element: <Email /> },
  { path: '/hr', name: 'Hr', element: <Hr /> },

  { path: '/dark-mode', name: 'DarkMode', element: <DarkMode /> },
  { path: '/rtl-mode', name: 'RtlMode', element: <RTL /> },
  { path: '/sidenav-compact', name: 'SideNavCompact', element: <SideNavCompact /> },
  { path: '/sidenav-dark', name: 'SideNavDark', element: <SideNavDark /> },
  { path: '/sidenav-hidden', name: 'SideNavHidden', element: <SideNavHidden /> },
  { path: '/sidenav-hover', name: 'SideNavHover', element: <SideNavHover /> },
  { path: '/sidenav-offcanvas', name: 'SideNavOffcanvas', element: <SideOffcanvas /> },
  { path: '/sidenav-small', name: 'SideNavSmall', element: <SideNavSmall /> },
  { path: '/sidenav-hover-active', name: 'SideNavHoverActive', element: <SideNavHoverActive /> },

  { path: '/faqs', name: 'Faqs', element: <Faq /> },
  { path: '/pricing', name: 'Pricing', element: <Pricing /> },
  { path: '/starter', name: 'Starter', element: <Starter /> },
  { path: '/timeline', name: 'Timeline', element: <Timeline /> },
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
