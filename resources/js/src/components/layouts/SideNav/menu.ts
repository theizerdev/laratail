import type { IconType } from 'react-icons/lib';
import {
  LuCalendar1,
  LuCircuitBoard,
  LuClipboardList,
  LuCodesandbox,
  LuFileText,
  LuFingerprint,
  LuLayoutPanelLeft,
  LuLock,
  LuMail,
  LuMessagesSquare,
  LuMonitorDot,
  LuActivity,
  LuPackage,
  LuPictureInPicture2,
  LuSettings,
  LuShare2,
  LuShieldCheck,
  LuShoppingBag,
  LuSquareUserRound,
} from 'react-icons/lu';

export type MenuItemType = {
  key: string;
  label: string;
  isTitle?: boolean;
  href?: string;
  children?: MenuItemType[];

  icon?: IconType;
  parentKey?: string;
  target?: string;
  isDisabled?: boolean;
};

export const menuItemsData: MenuItemType[] = [
  {
    key: 'Overview',
    label: 'Overview',
    isTitle: true,
  },
  {
    key: 'Dashboards',
    label: 'Dashboards',
    icon: LuMonitorDot,
    children: [
      { key: 'Analytics', label: 'Analytics', href: '/analytics' },
      { key: 'Ecommerce', label: 'Ecommerce', href: '/index' },
      { key: 'Email', label: 'Email', href: '/email' },
      { key: 'HR', label: 'HR', href: '/hr' },
    ],
  },
  {
    key: 'Landing Page',
    label: 'Landing Page',
    icon: LuPictureInPicture2,
    children: [
      { key: 'One Page', label: 'One Page', href: '/onepage-landing', target: '_blank' },
      { key: 'Product', label: 'Product', href: '/product-landing', target: '_blank' },
    ],
  },

  {
    key: 'Seguridad',
    label: 'Seguridad',
    icon: LuShieldCheck,
    children: [
      { key: 'Usuarios', label: 'Usuarios', href: '/admin/seguridad/usuarios' },
      { key: 'Roles', label: 'Roles', href: '/admin/seguridad/roles' },
      { key: 'Países', label: 'Países', href: '/admin/seguridad/paises' },
    ],
  },
  {
    key: 'Integraciones',
    label: 'Integraciones',
    icon: LuShare2,
    href: '/admin/integraciones',
  },
  {
    key: 'Monitoreo',
    label: 'Monitoreo',
    icon: LuActivity,
    children: [
      { key: 'Base de Datos', label: 'Base de Datos', href: '/admin/monitoreo/database' },
      { key: 'Servidor', label: 'Servidor', href: '/admin/monitoreo/servidor' },
      { key: 'Sesiones', label: 'Sesiones', href: '/admin/monitoreo/sesiones' },
      { key: 'Auditoría', label: 'Auditoría', href: '/admin/monitoreo/auditoria' },
    ],
  },
  {
    key: 'Configuracion',
    label: 'Configuración',
    icon: LuSettings,
    children: [
      { key: 'Empresas', label: 'Empresas', href: '/admin/configuracion/empresas' },
      { key: 'Sucursales', label: 'Sucursales', href: '/admin/configuracion/sucursales' },
    ],
  },



  {
    key: 'Multi Level',
    label: 'Multi Level',
    icon: LuShare2,
    children: [
      { key: 'Item 1', label: 'Item 1', href: '#' },
      { key: 'Item 2', label: 'Item 2', href: '#' },
    ],
  },
];
