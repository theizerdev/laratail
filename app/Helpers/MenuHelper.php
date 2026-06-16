<?php

namespace App\Helpers;

class MenuHelper
{
    public static function getPermissionSectors(): array
    {
        return [
            'seguridad' => [
                'name' => '🔒 Seguridad',
                'description' => 'Gestión de usuarios, roles, grupos y permisos',
                'color' => 'emerald',
                'icon' => 'heroicons:shield-check-solid',
                'modules' => [ 'usuarios', 'roles', 'grupos']
            ],
            'catalogo' => [
                'name' => '🛒 Catálogo',
                'description' => 'Productos, categorías, marcas, atributos e inventario',
                'color' => 'indigo',
                'icon' => 'heroicons:shopping-bag-solid',
                'modules' => ['categorias', 'marcas', 'productos', 'atributos']
            ],
            'ventas' => [
                'name' => '💰 Ventas',
                'description' => 'Clientes, pedidos, cotizaciones y cupones',
                'color' => 'amber',
                'icon' => 'heroicons:currency-dollar-solid',
                'modules' => ['clientes', 'pedidos', 'cotizaciones', 'cupones']
            ],
            'inventario' => [
                'name' => '📦 Inventario',
                'description' => 'Proveedores, movimientos, kardex, órdenes de compra y envíos',
                'color' => 'cyan',
                'icon' => 'heroicons:cube-solid',
                'modules' => ['proveedores', 'movimientos', 'kardex', 'alertas_stock', 'ordenes_compra', 'envios']
            ],
            'configuracion' => [
                'name' => '⚙️ Configuración',
                'description' => 'Configuración del sistema, países, regiones',
                'color' => 'purple',
                'icon' => 'heroicons:cog-solid',
                'modules' => ['paises', 'empresas', 'sucursales']
            ],
            'integraciones' => [
                'name' => '🔌 Integraciones',
                'description' => 'Conexiones con servicios externos, WhatsApp CRM',
                'color' => 'green',
                'icon' => 'heroicons:circle-stack-solid',
                'modules' => ['integraciones', 'whatsapp']
            ],
            'monitoreo' => [
                'name' => '📡 Monitoreo',
                'description' => 'Estadísticas del servidor, historial de login y actividades',
                'color' => 'teal',
                'icon' => 'heroicons:presentation-chart-line-solid',
                'modules' => ['monitoreo']
            ],
        ];
    }

    public static function getSectorPermissions(string $sector): \Illuminate\Support\Collection
    {
        return \Spatie\Permission\Models\Permission::where('sector', $sector)
            ->orderBy('module')
            ->orderBy('name')
            ->get();
    }

    public static function getUserSectors($user): array
    {
        $permissions = $user->permissions()->pluck('sector')->unique()->filter()->values();
        $sectors = self::getPermissionSectors();

        return $permissions->mapWithKeys(function ($sector) use ($sectors) {
            return [$sector => $sectors[$sector] ?? ['name' => ucfirst($sector)]];
        })->toArray();
    }

    public static function hasSectorAccess($user, string $sector): bool
    {
        return $user->permissions()->where('sector', $sector)->exists();
    }

    public static function getSectorColor(string $sector): string
    {
        $sectors = self::getPermissionSectors();
        return $sectors[$sector]['color'] ?? 'gray';
    }

    public static function getSectorIcon(string $sector): string
    {
        $sectors = self::getPermissionSectors();
        return $sectors[$sector]['icon'] ?? 'heroicons:folder-solid';
    }

    public static function formatSectorName(string $sector, bool $withIcon = true): string
    {
        $sectors = self::getPermissionSectors();
        $name = $sectors[$sector]['name'] ?? ucfirst($sector);

        if (!$withIcon) {
            return preg_replace('/^[^\w\s]+/', '', $name);
        }

        return $name;
    }

    public static function getSectorStats(): array
    {
        $sectors = self::getPermissionSectors();
        $stats = [];

        foreach ($sectors as $key => $sector) {
            $totalPermissions = \Spatie\Permission\Models\Permission::where('sector', $key)->count();
            $totalRoles = \Spatie\Permission\Models\Role::whereHas('permissions', function ($query) use ($key) {
                $query->where('sector', $key);
            })->count();

            $stats[$key] = [
                'name' => $sector['name'],
                'total_permissions' => $totalPermissions,
                'total_roles' => $totalRoles,
                'color' => $sector['color'],
                'description' => $sector['description']
            ];
        }

        return $stats;
    }

    public static function getSectorMenuItems(): array
    {
        return [
            'seguridad' => [
                'label' => 'Seguridad',
                'icon' => 'heroicons:shield-check-solid',
                'items' => [
                    [
                        'label' => 'Usuarios',
                        'icon' => 'heroicons:users-solid',
                        'permissions' => ['users.view', 'users.create', 'users.edit', 'users.delete'],
                        'active' => 'admin.users*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'users.view', 'route' => 'admin.users', 'active' => 'admin.users'],
                            ['label' => 'Nuevo usuario', 'permission' => 'users.create', 'route' => 'admin.users.create', 'active' => 'admin.users.create'],
                        ]
                    ],
                    [
                        'label' => 'Roles',
                        'icon' => 'heroicons:identification-solid',
                        'permissions' => ['roles.view', 'roles.create', 'roles.edit', 'roles.delete'],
                        'active' => 'admin.roles*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'roles.view', 'route' => 'admin.roles', 'active' => 'admin.roles'],
                            ['label' => 'Nuevo rol', 'permission' => 'roles.create', 'route' => 'admin.roles.create', 'active' => 'admin.roles.create'],
                        ]
                    ],
                    [
                        'label' => 'Grupos',
                        'icon' => 'heroicons:user-group-solid',
                        'permissions' => ['groups.view', 'groups.create', 'groups.edit', 'groups.delete'],
                        'active' => 'admin.grupos*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'groups.view', 'route' => 'admin.grupos', 'active' => 'admin.grupos'],
                            ['label' => 'Nuevo grupo', 'permission' => 'groups.create', 'route' => 'admin.grupos.create', 'active' => 'admin.grupos.create'],
                        ]
                    ],
                ],
            ],
            'catalogo' => [
                'label' => 'Catálogo',
                'icon' => 'heroicons:shopping-bag-solid',
                'items' => [
                    [
                        'label' => 'Productos',
                        'icon' => 'heroicons:square-3-stack-3d-solid',
                        'permissions' => ['productos.view', 'productos.create', 'productos.edit', 'productos.delete'],
                        'active' => 'admin.productos*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'productos.view', 'route' => 'admin.productos', 'active' => 'admin.productos'],
                            ['label' => 'Nuevo producto', 'permission' => 'productos.create', 'route' => 'admin.productos.create', 'active' => 'admin.productos.create'],
                        ]
                    ],
                    [
                        'label' => 'Categorías',
                        'icon' => 'heroicons:tag-solid',
                        'permissions' => ['categorias.view', 'categorias.create', 'categorias.edit', 'categorias.delete'],
                        'active' => 'admin.categorias*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'categorias.view', 'route' => 'admin.categorias', 'active' => 'admin.categorias'],
                        ]
                    ],
                    [
                        'label' => 'Marcas',
                        'icon' => 'heroicons:bookmark-solid',
                        'permissions' => ['marcas.view', 'marcas.create', 'marcas.edit', 'marcas.delete'],
                        'active' => 'admin.marcas*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'marcas.view', 'route' => 'admin.marcas', 'active' => 'admin.marcas'],
                        ]
                    ],
                    [
                        'label' => 'Atributos',
                        'icon' => 'heroicons:swatch-solid',
                        'permissions' => ['atributos.view', 'atributos.create', 'atributos.edit', 'atributos.delete'],
                        'active' => 'admin.atributos*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'atributos.view', 'route' => 'admin.atributos', 'active' => 'admin.atributos'],
                        ]
                    ],
                ],
            ],
            'ventas' => [
                'label' => 'Ventas',
                'icon' => 'heroicons:currency-dollar-solid',
                'items' => [
                    [
                        'label' => 'Clientes',
                        'icon' => 'heroicons:user-group-solid',
                        'permissions' => ['clientes.view', 'clientes.create', 'clientes.edit', 'clientes.delete'],
                        'active' => 'admin.clientes*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'clientes.view', 'route' => 'admin.clientes', 'active' => 'admin.clientes'],
                        ]
                    ],
                    [
                        'label' => 'Pedidos',
                        'icon' => 'heroicons:shopping-cart-solid',
                        'permissions' => ['pedidos.view', 'pedidos.create', 'pedidos.edit', 'pedidos.delete'],
                        'active' => 'admin.pedidos*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'pedidos.view', 'route' => 'admin.pedidos', 'active' => 'admin.pedidos'],
                            ['label' => 'Nuevo pedido', 'permission' => 'pedidos.create', 'route' => 'admin.pedidos.create', 'active' => 'admin.pedidos.create'],
                        ]
                    ],
                    [
                        'label' => 'Cotizaciones',
                        'icon' => 'heroicons:document-text-solid',
                        'permissions' => ['cotizaciones.view', 'cotizaciones.create', 'cotizaciones.edit', 'cotizaciones.delete'],
                        'active' => 'admin.cotizaciones*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'cotizaciones.view', 'route' => 'admin.cotizaciones', 'active' => 'admin.cotizaciones'],
                            ['label' => 'Nueva cotización', 'permission' => 'cotizaciones.create', 'route' => 'admin.cotizaciones.create', 'active' => 'admin.cotizaciones.create'],
                        ]
                    ],
                    [
                        'label' => 'Cupones',
                        'icon' => 'heroicons:ticket-solid',
                        'permissions' => ['cupones.view', 'cupones.create', 'cupones.edit', 'cupones.delete'],
                        'active' => 'admin.cupones*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'cupones.view', 'route' => 'admin.cupones', 'active' => 'admin.cupones'],
                        ]
                    ],
                ],
            ],
            'inventario' => [
                'label' => 'Inventario',
                'icon' => 'heroicons:cube-solid',
                'items' => [
                    [
                        'label' => 'Proveedores',
                        'icon' => 'heroicons:building-office-solid',
                        'permissions' => ['proveedores.view', 'proveedores.create', 'proveedores.edit', 'proveedores.delete'],
                        'active' => 'admin.proveedores*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'proveedores.view', 'route' => 'admin.proveedores', 'active' => 'admin.proveedores'],
                        ]
                    ],
                    [
                        'label' => 'Movimientos',
                        'icon' => 'heroicons:arrows-up-down-solid',
                        'permissions' => ['movimientos.view', 'movimientos.create'],
                        'active' => 'admin.movimientos*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'movimientos.view', 'route' => 'admin.movimientos', 'active' => 'admin.movimientos'],
                        ]
                    ],
                    [
                        'label' => 'Kardex',
                        'icon' => 'heroicons:document-chart-bar-solid',
                        'permissions' => ['kardex.view'],
                        'active' => 'admin.kardex*',
                        'route' => 'admin.kardex',
                    ],
                    [
                        'label' => 'Alertas Stock',
                        'icon' => 'heroicons:exclamation-triangle-solid',
                        'permissions' => ['alertas_stock.view'],
                        'active' => 'admin.alertas-stock*',
                        'route' => 'admin.alertas-stock',
                    ],
                    [
                        'label' => 'Órdenes de Compra',
                        'icon' => 'heroicons:shopping-bag-solid',
                        'permissions' => ['ordenes_compra.view', 'ordenes_compra.create', 'ordenes_compra.edit'],
                        'active' => 'admin.ordenes-compra*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'ordenes_compra.view', 'route' => 'admin.ordenes-compra', 'active' => 'admin.ordenes-compra'],
                            ['label' => 'Nueva orden', 'permission' => 'ordenes_compra.create', 'route' => 'admin.ordenes-compra.create', 'active' => 'admin.ordenes-compra.create'],
                        ]
                    ],
                    [
                        'label' => 'Envíos',
                        'icon' => 'heroicons:truck-solid',
                        'permissions' => ['envios.view', 'envios.create', 'envios.edit'],
                        'active' => 'admin.envios*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'envios.view', 'route' => 'admin.envios', 'active' => 'admin.envios'],
                            ['label' => 'Nuevo envío', 'permission' => 'envios.create', 'route' => 'admin.envios.create', 'active' => 'admin.envios.create'],
                        ]
                    ],
                ],
            ],
            'configuracion' => [
                'label' => 'Configuración',
                'icon' => 'heroicons:cog-solid',
                'items' => [
                    [
                        'label' => 'Países',
                        'icon' => 'heroicons:globe-alt-solid',
                        'permissions' => ['paises.view', 'paises.create', 'paises.edit', 'paises.delete'],
                        'active' => 'admin.paises*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'paises.view', 'route' => 'admin.paises', 'active' => 'admin.paises'],
                        ]
                    ],
                    [
                        'label' => 'Empresas',
                        'icon' => 'heroicons:building-office-2-solid',
                        'permissions' => ['empresas.view', 'empresas.create', 'empresas.edit', 'empresas.delete'],
                        'active' => 'admin.empresas*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'empresas.view', 'route' => 'admin.empresas', 'active' => 'admin.empresas'],
                            ['label' => 'Nueva empresa', 'permission' => 'empresas.create', 'route' => 'admin.empresas.create', 'active' => 'admin.empresas.create'],
                        ]
                    ],
                    [
                        'label' => 'Sucursales',
                        'icon' => 'heroicons:building-storefront-solid',
                        'permissions' => ['sucursales.view', 'sucursales.create', 'sucursales.edit', 'sucursales.delete'],
                        'active' => 'admin.sucursales*',
                        'children' => [
                            ['label' => 'Listado', 'permission' => 'sucursales.view', 'route' => 'admin.sucursales', 'active' => 'admin.sucursales'],
                            ['label' => 'Nueva sucursal', 'permission' => 'sucursales.create', 'route' => 'admin.sucursales.create', 'active' => 'admin.sucursales.create'],
                        ]
                    ],
                ],
            ],
            'integraciones' => [
                'label' => 'Integraciones',
                'icon' => 'heroicons:circle-stack-solid',
                'items' => [
                    [
                        'label' => 'Integraciones',
                        'icon' => 'heroicons:squares-plus-solid',
                        'permissions' => ['integraciones.view'],
                        'active' => 'admin.integraciones',
                        'route' => 'admin.integraciones',
                    ],
                    [
                        'label' => 'WhatsApp CRM',
                        'icon' => 'mdi:whatsapp',
                        'permissions' => ['whatsapp.view'],
                        'active' => 'admin.integraciones.whatsapp',
                        'route' => 'admin.integraciones.whatsapp',
                    ],
                ],
            ],
            'monitoreo' => [
                'label' => 'Monitoreo',
                'icon' => 'heroicons:presentation-chart-line-solid',
                'items' => [
                    [
                        'label' => 'Monitoreo del Sistema',
                        'icon' => 'heroicons:server-stack-solid',
                        'permissions' => ['monitoreo.view'],
                        'active' => 'admin.monitoreo',
                        'route' => 'admin.monitoreo',
                    ],
                    [
                        'label' => 'Base de Datos',
                        'icon' => 'heroicons:circle-stack-solid',
                        'permissions' => ['monitoreo.database'],
                        'active' => 'admin.monitoreo.base-datos',
                        'route' => 'admin.monitoreo.base-datos',
                    ],
                ],
            ],
        ];
    }

    public static function isMenuItemActive(array $item): bool
    {
        if (isset($item['active'])) {
            $pattern = $item['active'];
            if (request()->routeIs($pattern)) {
                return true;
            }
        }
        return false;
    }

    public static function isSectorActive(array $sectorItems): bool
    {
        foreach ($sectorItems as $item) {
            if (self::isMenuItemActive($item)) {
                return true;
            }
            if (isset($item['children'])) {
                foreach ($item['children'] as $child) {
                    if (self::isMenuItemActive($child)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public static function hasPermission(string|array $permissions): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        $permissions = is_array($permissions) ? $permissions : [$permissions];

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    public static function hasAnyPermission(array $permissions): bool
    {
        return self::hasPermission($permissions);
    }
}