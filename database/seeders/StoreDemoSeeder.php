<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class StoreDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Categories
        $catHombre = Category::firstOrCreate(['slug' => 'hombre'], [
            'nombre' => 'Hombre',
            'descripcion' => 'Ropa y accesorios para hombre.',
            'status' => true,
        ]);

        $catMujer = Category::firstOrCreate(['slug' => 'mujer'], [
            'nombre' => 'Mujer',
            'descripcion' => 'Ropa y accesorios para mujer.',
            'status' => true,
        ]);

        $catAccesorios = Category::firstOrCreate(['slug' => 'accesorios'], [
            'nombre' => 'Accesorios',
            'descripcion' => 'Complementos perfectos para cualquier estilo.',
            'status' => true,
        ]);

        $catCalzado = Category::firstOrCreate(['slug' => 'calzado'], [
            'nombre' => 'Calzado',
            'descripcion' => 'Zapatos, zapatillas y botas.',
            'status' => true,
        ]);

        // 2. Create Products
        $productsData = [
            [
                'nombre' => 'Camisa Oxford Classic',
                'sku' => 'SHIRT-OX-001',
                'precio' => 45.00,
                'precio_oferta' => null,
                'stock' => 100,
                'category_id' => $catHombre->id,
                'destacado' => true,
                'nuevo' => true,
                'status' => true,
                'imagen_principal' => 'https://images.unsplash.com/photo-1596755094514-f87e32f85e23?q=80&w=600&auto=format&fit=crop',
                'descripcion_corta' => 'Camisa clásica de algodón para cualquier ocasión.',
                'descripcion' => 'Nuestra Camisa Oxford Classic está hecha con 100% algodón premium, asegurando durabilidad y un ajuste perfecto. Ideal para la oficina o salidas casuales.',
            ],
            [
                'nombre' => 'Bolso de Cuero Minimalista',
                'sku' => 'BAG-LTR-002',
                'precio' => 120.00,
                'precio_oferta' => null,
                'stock' => 50,
                'category_id' => $catAccesorios->id,
                'destacado' => true,
                'nuevo' => false,
                'status' => true,
                'imagen_principal' => 'https://images.unsplash.com/photo-1584916201218-f4242ceb4809?q=80&w=600&auto=format&fit=crop',
                'descripcion_corta' => 'Bolso elegante y resistente en cuero genuino.',
                'descripcion' => 'Diseñado con un estilo minimalista, este bolso ofrece el espacio perfecto para tus básicos diarios. Costuras reforzadas y un acabado impecable.',
            ],
            [
                'nombre' => 'Zapatillas Urban Explorer',
                'sku' => 'SHOE-URB-003',
                'precio' => 89.99,
                'precio_oferta' => null,
                'stock' => 200,
                'category_id' => $catCalzado->id,
                'destacado' => true,
                'nuevo' => false,
                'status' => true,
                'imagen_principal' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=600&auto=format&fit=crop',
                'descripcion_corta' => 'Comodidad superior para la ciudad.',
                'descripcion' => 'Suela ergonómica y materiales transpirables hacen de las Urban Explorer el calzado definitivo para largas caminatas urbanas.',
            ],
            [
                'nombre' => 'Reloj Classic Chrono',
                'sku' => 'WATCH-CHR-004',
                'precio' => 195.00,
                'precio_oferta' => 156.00,
                'stock' => 30,
                'category_id' => $catAccesorios->id,
                'destacado' => true,
                'nuevo' => true,
                'status' => true,
                'imagen_principal' => 'https://images.unsplash.com/photo-1524592094714-0f0654e20314?q=80&w=600&auto=format&fit=crop',
                'descripcion_corta' => 'Diseño atemporal con maquinaria de precisión.',
                'descripcion' => 'Reloj cronógrafo clásico con correa de cuero italiano y cristal de zafiro resistente a los rayones. Resistente al agua hasta 50 metros.',
            ],
            [
                'nombre' => 'Chaqueta Denim Vintage',
                'sku' => 'JCKT-DNM-005',
                'precio' => 75.00,
                'precio_oferta' => null,
                'stock' => 80,
                'category_id' => $catMujer->id,
                'destacado' => false,
                'nuevo' => false,
                'status' => true,
                'imagen_principal' => 'https://images.unsplash.com/photo-1516257984-b1b4d707412e?q=80&w=600&auto=format&fit=crop',
                'descripcion_corta' => 'Una prenda básica imprescindible.',
                'descripcion' => 'Chaqueta de mezclilla con un corte relajado y un ligero desgaste vintage. Perfecta para combinar con vestidos o jeans.',
            ],
            [
                'nombre' => 'Gafas de Sol Aviator',
                'sku' => 'SUN-AVI-006',
                'precio' => 55.00,
                'precio_oferta' => 45.00,
                'stock' => 150,
                'category_id' => $catAccesorios->id,
                'destacado' => false,
                'nuevo' => false,
                'status' => true,
                'imagen_principal' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?q=80&w=600&auto=format&fit=crop',
                'descripcion_corta' => 'Protección UV400 con mucho estilo.',
                'descripcion' => 'El clásico diseño de aviador modernizado con montura ultraligera de aleación metálica y lentes polarizadas.',
            ],
        ];

        foreach ($productsData as $data) {
            Product::firstOrCreate(
                ['sku' => $data['sku']],
                $data
            );
        }
    }
}
