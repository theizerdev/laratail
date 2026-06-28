<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminProductsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an admin can create a product with precio_bs.
     */
    public function test_admin_can_create_product_with_precio_bs(): void
    {
        $user = User::factory()->create();
        // Give permissions or bypass auth since it uses role:admin|super-admin middleware
        
        $component = Livewire::actingAs($user)
            ->test('admin.catalogo.productos.create')
            ->set('nombre', 'Harina Pan')
            ->set('precio', 1.20)
            ->set('precio_bs', 45.50)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.productos'));

        $this->assertDatabaseHas('products', [
            'nombre' => 'Harina Pan',
            'precio' => 1.20,
            'precio_bs' => 45.50,
        ]);
    }

    /**
     * Test that an admin can update a product's precio_bs.
     */
    public function test_admin_can_update_product_precio_bs(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'nombre' => 'Arroz Primor',
            'sku' => 'ARR-001',
            'precio' => 1.50,
            'precio_bs' => 55.00,
            'status' => true,
            'empresa_id' => null,
            'sucursal_id' => null,
        ]);

        $component = Livewire::actingAs($user)
            ->test('admin.catalogo.productos.edit', ['id' => $product->id])
            ->assertSet('nombre', 'Arroz Primor')
            ->assertSet('precio_bs', 55.00)
            ->set('precio_bs', 58.20)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.productos'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'precio_bs' => 58.20,
        ]);
    }
}
