<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCalculationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that adding items to the cart recalculates subtotal and total correctly.
     */
    public function test_cart_total_calculation_with_multiple_items(): void
    {
        // 1. Create two test products
        $product1 = Product::create([
            'nombre' => 'Product 1',
            'sku' => 'SKU-001',
            'precio' => 45.00,
            'status' => true,
            'stock' => 10,
            'tiene_variantes' => false,
            'empresa_id' => null,
            'sucursal_id' => null,
        ]);

        $product2 = Product::create([
            'nombre' => 'Product 2',
            'sku' => 'SKU-002',
            'precio' => 156.00,
            'status' => true,
            'stock' => 10,
            'tiene_variantes' => false,
            'empresa_id' => null,
            'sucursal_id' => null,
        ]);

        // 2. Instantiate CartService
        $cartService = app(CartService::class);

        // Get fresh active cart (subtotal = 0, total = 0)
        $cart = $cartService->getOrCreate();
        $this->assertEquals(0, $cart->total);

        // 3. Add first product
        $cartService->addItem($product1, 1);
        $cart = $cartService->getCart();
        $this->assertEquals(45.00, $cart->total);
        $this->assertEquals(1, $cartService->getCartCount());

        // 4. Add second product
        $cartService->addItem($product2, 1);
        $cart = $cartService->getCart();
        
        // The total must be 45 + 156 = 201
        $this->assertEquals(201.00, $cart->total);
        $this->assertEquals(2, $cartService->getCartCount());

        // 5. Add another of the first product
        $cartService->addItem($product1, 1);
        $cart = $cartService->getCart();

        // The total must be (45 * 2) + 156 = 246
        $this->assertEquals(246.00, $cart->total);
        $this->assertEquals(3, $cartService->getCartCount());
    }
}
