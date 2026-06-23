<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutRouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guest visiting /checkout is redirected to /acceso.
     */
    public function test_guest_is_redirected_to_acceso(): void
    {
        $response = $this->get('/checkout');

        $response->assertRedirect('/acceso');
    }

    /**
     * Authenticated user visiting /checkout with empty cart is redirected to /carrito.
     */
    public function test_authenticated_user_with_empty_cart_redirects_to_carrito(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/checkout');

        // Since cart is empty, mount() redirects to /carrito
        $response->assertRedirect('/carrito');
    }
}
