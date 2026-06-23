<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    protected $fillable = [
        'customer_id',
        'product_id',
        'product_variant_id',
    ];

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'product_id' => 'integer',
            'product_variant_id' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Toggle a product in the wishlist. Returns true if added, false if removed.
     */
    public static function toggle(int $customerId, int $productId, ?int $variantId = null): bool
    {
        $existing = static::where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($existing) {
            $existing->delete();
            return false;
        }

        static::create([
            'customer_id' => $customerId,
            'product_id' => $productId,
            'product_variant_id' => $variantId,
        ]);

        return true;
    }
}
