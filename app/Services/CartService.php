<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartService
{
    /**
     * Get or create the active cart for the current session/user.
     */
    public function getOrCreate(): Cart
    {
        $cartId = session('cart_id');

        // Try to find existing cart
        if ($cartId) {
            $cart = Cart::with('items.product', 'coupon')->find($cartId);
            if ($cart && $cart->estado === 'activo') {
                // If user just logged in, link cart to customer
                if (Auth::check() && $cart->customer_id === null) {
                    $customer = $this->getOrCreateCustomer(Auth::user());
                    $cart->customer_id = $customer->id;
                    $cart->save();
                }
                return $cart;
            }
        }

        // Try to find an active cart for the logged-in user
        if (Auth::check()) {
            $customer = $this->getOrCreateCustomer(Auth::user());
            $cart = Cart::with('items.product', 'coupon')
                ->where('customer_id', $customer->id)
                ->where('estado', 'activo')
                ->latest()
                ->first();

            if ($cart) {
                session(['cart_id' => $cart->id]);
                return $cart;
            }
        }

        // Create new cart
        $cart = Cart::create([
            'estado' => 'activo',
            'subtotal' => 0,
            'total' => 0,
        ]);

        session(['cart_id' => $cart->id]);

        return $cart->load('items.product', 'coupon');
    }

    /**
     * Get current cart (nullable).
     */
    public function getCart(): ?Cart
    {
        $cartId = session('cart_id');
        if (!$cartId) {
            // Try by auth user
            if (Auth::check()) {
                $customer = Customer::where('user_id', Auth::id())->first();
                if ($customer) {
                    $cart = Cart::with('items.product', 'coupon')
                        ->where('customer_id', $customer->id)
                        ->where('estado', 'activo')
                        ->latest()
                        ->first();
                    if ($cart) {
                        session(['cart_id' => $cart->id]);
                        return $cart;
                    }
                }
            }
            return null;
        }

        return Cart::with('items.product', 'coupon')->find($cartId);
    }

    /**
     * Get the total number of items in the cart.
     */
    public function getCartCount(): int
    {
        $cart = $this->getCart();
        if (!$cart) return 0;
        return $cart->items->sum('cantidad');
    }

    /**
     * Add a product to the cart.
     */
    public function addItem(Product $product, int $qty = 1, ?int $variantId = null): CartItem
    {
        $cart = $this->getOrCreate();

        // Obtener la fuente del precio (variante si existe, sino el producto)
        $source = $product;
        if ($variantId) {
            $variant = $product->variants()->find($variantId);
            if ($variant) {
                $source = $variant;
            }
        }

        // Obtener precio correcto según la moneda actual
        $currency = strtolower(get_current_currency());
        if (is_venezuela_company() && ($currency === 'bs' || $currency === 'ves')) {
            // Si la moneda es Bs./VES, usar el precio_bs de la fuente (producto o variante)
            $precioBaseUsd = $source->tiene_descuento ? $source->precio_oferta : $source->precio;
            $precioBaseOriginal = $source->precio;
            
            if ($precioBaseOriginal > 0 && $source->precio_bs > 0) {
                // Aplicar misma proporción de descuento al precio_bs
                $precio = $precioBaseUsd / $precioBaseOriginal * $source->precio_bs;
            } else {
                $precio = $precioBaseUsd;
            }
        } else {
            // Para USD, usar el precio normal
            $precio = $source->tiene_descuento ? $source->precio_oferta : $source->precio;
        }

        // Check if item already exists in cart
        $existingItem = $cart->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($existingItem) {
            $existingItem->cantidad += $qty;
            $existingItem->precio = $precio;
            $existingItem->save();
            $item = $existingItem;
        } else {
            $item = $cart->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variantId,
                'cantidad' => $qty,
                'precio' => $precio,
            ]);
        }

        $cart->recalcular();

        return $item;
    }

    /**
     * Update item quantity.
     */
    public function updateItemQty(CartItem $item, int $qty): void
    {
        if ($qty <= 0) {
            $this->removeItem($item);
            return;
        }

        $item->cantidad = $qty;
        $item->save();
        $item->cart->recalcular();
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(CartItem $item): void
    {
        $cart = $item->cart;
        $item->delete();
        $cart->recalcular();
    }

    /**
     * Apply a coupon to the cart.
     */
    public function applyCoupon(string $code): bool
    {
        $cart = $this->getOrCreate();
        $coupon = Coupon::where('codigo', $code)->first();

        if (!$coupon || !$coupon->es_valido) {
            return false;
        }

        if ($coupon->compra_minima && $cart->subtotal < $coupon->compra_minima) {
            return false;
        }

        $cart->coupon_id = $coupon->id;
        $cart->save();
        $cart->recalcular();

        return true;
    }

    /**
     * Remove coupon from cart.
     */
    public function removeCoupon(): void
    {
        $cart = $this->getOrCreate();
        $cart->coupon_id = null;
        $cart->save();
        $cart->recalcular();
    }

    /**
     * Clear the cart and remove from session.
     */
    public function clearCart(): void
    {
        $cart = $this->getCart();
        if ($cart) {
            $cart->items()->delete();
            $cart->estado = 'convertido';
            $cart->save();
        }
        session()->forget('cart_id');
    }

    /**
     * Get or create a Customer record for a User.
     */
    protected function getOrCreateCustomer($user): Customer
    {
        $customer = Customer::where('user_id', $user->id)->first();
        if (!$customer) {
            $customer = Customer::create([
                'user_id' => $user->id,
                'nombre' => $user->name,
                'email' => $user->email,
                'telefono' => $user->telefono,
                'activo' => true,
                'empresa_id' => $user->empresa_id ?: 1,
            ]);
        }
        return $customer;
    }
}