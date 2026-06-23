<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogQuickViewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that clicking detail sets quickViewProductId and displays product details in the modal.
     */
    public function test_quick_view_modal_loads_product_details(): void
    {
        // 1. Create a product (use null tenant foreign keys to bypass constraint failures in SQLite)
        $product = Product::create([
            'nombre' => 'Teclado Mecanico Premium',
            'sku' => 'SKU-KBD-001',
            'precio' => 125.50,
            'status' => true,
            'stock' => 10,
            'tiene_variantes' => false,
            'descripcion_corta' => 'Un teclado mecanico con switches lineares muy silenciosos.',
            'empresa_id' => null,
            'sucursal_id' => null,
        ]);

        // 2. Test Volt Catalog Component
        $component = Livewire::test('store.catalog')
            ->assertSet('quickViewProductId', null)
            ->assertDontSee('Un teclado mecanico con switches lineares muy silenciosos.');

        // 3. Call openQuickView
        $component->call('openQuickView', $product->id)
            ->assertSet('quickViewProductId', $product->id)
            ->assertHasNoErrors();

        // 4. Assert that product info is now visible in the rendered output
        $component->assertSee('Teclado Mecanico Premium')
            ->assertSee('$125.50')
            ->assertSee('Un teclado mecanico con switches lineares muy silenciosos.')
            ->assertSee('Añadir al carrito');
    }
}
