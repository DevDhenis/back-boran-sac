<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingCartItem extends Model
{
    protected $table = 'shopping_cart_items';

    protected $fillable = [
        'shopping_cart_id',
        'product_id',
        'cantidad',
        'precio_final',
        'descuento',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad'          => 'decimal:2',
        'precio_unitario'   => 'decimal:2',
        'subtotal'          => 'decimal:2',
        'precio_final'      => 'decimal:2',
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
        if (!is_null($this->attributes['descuento']) && $this->attributes['descuento'] > 0) {
            $descuentoDecimal = $this->attributes['descuento'] / 100;
            $precioConDescuento = $this->attributes['precio_unitario'] * (1 - $descuentoDecimal);
            return round($precioConDescuento, 2);
        }
        return $this->attributes['precio_unitario'];
    }
}
