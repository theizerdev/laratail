<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Seed the permissions grouped by module.
     */
    public function run(): void
    {
        $permissions = [
            // Sector: Seguridad
            'seguridad' => [
                // Módulo: Dashboard
                'dashboard.view' => 'Ver Dashboard',

                // Módulo: Usuarios
                'users.view' => 'Ver Usuarios',
                'users.create' => 'Crear Usuario',
                'users.edit' => 'Editar Usuario',
                'users.delete' => 'Eliminar Usuario',

                // Módulo: Roles
                'roles.view' => 'Ver Roles',
                'roles.create' => 'Crear Rol',
                'roles.edit' => 'Editar Rol',
                'roles.delete' => 'Eliminar Rol',

                // Módulo: Grupos
                'groups.view' => 'Ver Grupos',
                'groups.create' => 'Crear Grupo',
                'groups.edit' => 'Editar Grupo',
                'groups.delete' => 'Eliminar Grupo',
            ],

            // Sector: Catálogo
            'catalogo' => [
                // Módulo: Categorías
                'categorias.view' => 'Ver Categorías',
                'categorias.create' => 'Crear Categoría',
                'categorias.edit' => 'Editar Categoría',
                'categorias.delete' => 'Eliminar Categoría',

                // Módulo: Marcas
                'marcas.view' => 'Ver Marcas',
                'marcas.create' => 'Crear Marca',
                'marcas.edit' => 'Editar Marca',
                'marcas.delete' => 'Eliminar Marca',

                // Módulo: Productos
                'productos.view' => 'Ver Productos',
                'productos.create' => 'Crear Producto',
                'productos.edit' => 'Editar Producto',
                'productos.delete' => 'Eliminar Producto',

                // Módulo: Atributos
                'atributos.view' => 'Ver Atributos',
                'atributos.create' => 'Crear Atributo',
                'atributos.edit' => 'Editar Atributo',
                'atributos.delete' => 'Eliminar Atributo',
            ],

            // Sector: Configuración
            'configuracion' => [
                // Módulo: Países
                'paises.view' => 'Ver Países',
                'paises.create' => 'Crear País',
                'paises.edit' => 'Editar País',
                'paises.delete' => 'Eliminar País',

                // Módulo: Empresas
                'empresas.view' => 'Ver Empresas',
                'empresas.create' => 'Crear Empresa',
                'empresas.edit' => 'Editar Empresa',
                'empresas.delete' => 'Eliminar Empresa',

                // Módulo: Sucursales
                'sucursales.view' => 'Ver Sucursales',
                'sucursales.create' => 'Crear Sucursal',
                'sucursales.edit' => 'Editar Sucursal',
                'sucursales.delete' => 'Eliminar Sucursal',
            ],

            // Sector: Integraciones
            'integraciones' => [
                // Módulo: Integraciones
                'integraciones.view' => 'Ver Integraciones',

                // Módulo: WhatsApp CRM
                'whatsapp.view' => 'Ver WhatsApp CRM',
                'whatsapp.manage' => 'Gestionar WhatsApp',
                'whatsapp.send' => 'Enviar Mensajes WhatsApp',
            ],

            // Sector: Monitoreo
            'monitoreo' => [
                // Módulo: Monitoreo
                'monitoreo.view' => 'Ver Monitoreo',
                'monitoreo.server' => 'Ver Stats del Servidor',
                'monitoreo.logins' => 'Ver Historial de Login',
                'monitoreo.activities' => 'Ver Actividades',
                'monitoreo.database' => 'Gestionar Base de Datos',
                'monitoreo.backup' => 'Crear Respaldo de BD',
                'monitoreo.import' => 'Importar Base de Datos',
            ],

            // Sector: Ventas
            'ventas' => [
                // Módulo: Clientes
                'clientes.view' => 'Ver Clientes',
                'clientes.create' => 'Crear Cliente',
                'clientes.edit' => 'Editar Cliente',
                'clientes.delete' => 'Eliminar Cliente',

                // Módulo: Pedidos
                'pedidos.view' => 'Ver Pedidos',
                'pedidos.create' => 'Crear Pedido',
                'pedidos.edit' => 'Editar Pedido',
                'pedidos.delete' => 'Eliminar Pedido',

                // Módulo: Cotizaciones
                'cotizaciones.view' => 'Ver Cotizaciones',
                'cotizaciones.create' => 'Crear Cotización',
                'cotizaciones.edit' => 'Editar Cotización',
                'cotizaciones.delete' => 'Eliminar Cotización',
                'cotizaciones.convert' => 'Convertir Cotización a Pedido',

                // Módulo: Cupones
                'cupones.view' => 'Ver Cupones',
                'cupones.create' => 'Crear Cupón',
                'cupones.edit' => 'Editar Cupón',
                'cupones.delete' => 'Eliminar Cupón',
            ],

            // Sector: Inventario
            'inventario' => [
                // Módulo: Proveedores
                'proveedores.view' => 'Ver Proveedores',
                'proveedores.create' => 'Crear Proveedor',
                'proveedores.edit' => 'Editar Proveedor',
                'proveedores.delete' => 'Eliminar Proveedor',

                // Módulo: Movimientos de Inventario
                'movimientos.view' => 'Ver Movimientos',
                'movimientos.create' => 'Crear Movimiento',
                'movimientos.edit' => 'Editar Movimiento',
                'movimientos.delete' => 'Eliminar Movimiento',

                // Módulo: Kardex
                'kardex.view' => 'Ver Kardex',

                // Módulo: Alertas de Stock
                'alertas_stock.view' => 'Ver Alertas de Stock',

                // Módulo: Órdenes de Compra
                'ordenes_compra.view' => 'Ver Órdenes de Compra',
                'ordenes_compra.create' => 'Crear Orden de Compra',
                'ordenes_compra.edit' => 'Editar Orden de Compra',
                'ordenes_compra.delete' => 'Eliminar Orden de Compra',
                'ordenes_compra.recibir' => 'Recibir Mercancía',

                // Módulo: Envíos
                'envios.view' => 'Ver Envíos',
                'envios.create' => 'Crear Envío',
                'envios.edit' => 'Editar Envío',
                'envios.delete' => 'Eliminar Envío',
                'envios.guia' => 'Generar Guía de Despacho',
            ],

            // Sector: Pagos & Facturación
            'pagos' => [
                // Módulo: Pagos
                'pagos.view' => 'Ver Pagos',
                'pagos.create' => 'Crear Pago',
                'pagos.edit' => 'Editar Pago',
                'pagos.delete' => 'Eliminar Pago',

                // Módulo: Facturas
                'facturas.view' => 'Ver Facturas',
                'facturas.create' => 'Crear Factura',
                'facturas.edit' => 'Editar Factura',
                'facturas.anular' => 'Anular Factura',

                // Módulo: Notas de Crédito
                'notas_credito.view' => 'Ver Notas de Crédito',
                'notas_credito.create' => 'Crear Nota de Crédito',
                'notas_credito.edit' => 'Editar Nota de Crédito',
                'notas_credito.anular' => 'Anular Nota de Crédito',

                // Módulo: Caja
                'caja.view' => 'Ver Caja',
                'caja.open' => 'Abrir Caja',
                'caja.close' => 'Cerrar Caja',
                'caja.movements' => 'Gestionar Movimientos de Caja',

                // Módulo: Conciliación
                'conciliacion.view' => 'Ver Conciliación',
                'conciliacion.manage' => 'Gestionar Conciliación',
            ],
        ];

        foreach ($permissions as $sector => $sectorPermissions) {
            foreach ($sectorPermissions as $permission => $slug) {
                // Determinar el módulo basado en el prefijo del permiso
                $module = match (true) {
                    str_starts_with($permission, 'dashboard.') => 'dashboard',
                    str_starts_with($permission, 'categorias.') => 'categorias',
                    str_starts_with($permission, 'marcas.') => 'marcas',
                    str_starts_with($permission, 'productos.') => 'productos',
                    str_starts_with($permission, 'atributos.') => 'atributos',
                    str_starts_with($permission, 'users.') => 'usuarios',
                    str_starts_with($permission, 'roles.') => 'roles',
                    str_starts_with($permission, 'groups.') => 'grupos',
                    str_starts_with($permission, 'paises.') => 'paises',
                    str_starts_with($permission, 'empresas.') => 'empresas',
                    str_starts_with($permission, 'sucursales.') => 'sucursales',
                    str_starts_with($permission, 'integraciones.') => 'integraciones',
                    str_starts_with($permission, 'whatsapp.') => 'whatsapp',
                    str_starts_with($permission, 'monitoreo.') => 'monitoreo',
                    str_starts_with($permission, 'clientes.') => 'clientes',
                    str_starts_with($permission, 'pedidos.') => 'pedidos',
                    str_starts_with($permission, 'cotizaciones.') => 'cotizaciones',
                    str_starts_with($permission, 'cupones.') => 'cupones',
                    str_starts_with($permission, 'proveedores.') => 'proveedores',
                    str_starts_with($permission, 'movimientos.') => 'movimientos',
                    str_starts_with($permission, 'kardex.') => 'kardex',
                    str_starts_with($permission, 'alertas_stock.') => 'alertas_stock',
                    str_starts_with($permission, 'ordenes_compra.') => 'ordenes_compra',
                    str_starts_with($permission, 'envios.') => 'envios',
                    str_starts_with($permission, 'pagos.') => 'pagos',
                    str_starts_with($permission, 'facturas.') => 'facturas',
                    str_starts_with($permission, 'notas_credito.') => 'notas_credito',
                    str_starts_with($permission, 'caja.') => 'caja',
                    str_starts_with($permission, 'conciliacion.') => 'conciliacion',
                    default => 'general',
                };

                Permission::updateOrCreate(
                    ['name' => $permission, 'guard_name' => 'web'],
                    [
                        'slug' => $slug,
                        'module' => $module,
                        'sector' => $sector,
                    ]
                );
            }
        }

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
