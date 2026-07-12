<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingCartItem extends Model
{
    protected $table = 'shopping_cart_items';

    protected $fillable = [
        'shopping_cart_id',
        'product_id',
        'quantity',
        'final_price',
        'discount',
        'unit_price',
        'subtotal',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'final_price' => 'decimal:2',
    ];

    public function cart()
    {
        return $this->belongsTo(ShoppingCart::class, 'shopping_cart_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getPrecioFinalAttribute()
    {
        if (! is_null($this->attributes['discount']) && $this->attributes['discount'] > 0) {
            $descuentoDecimal = $this->attributes['discount'] / 100;
            $precioConDescuento = $this->attributes['unit_price'] * (1 - $descuentoDecimal);

            return round($precioConDescuento, 2);
        }

        return $this->attributes['unit_price'];
    }
}
