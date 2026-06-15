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