<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $primaryKey = 'id';

    protected $fillable = [
        'internal_code',
        'name',
        'description',
        'stock',
        'minimum_quantity',
        'on_promotion',
        'unit_price',
        'wholesale_unit_price',
        'wholesale_min_quantity',
        'discount',
        'image',
        'unit_id',
        'product_category_id',
        'status',
        'final_price',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'on_promotion' => 'boolean',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function getPrecioAttribute()
    {
        return $this->attributes['unit_price'] ?? null;
    }

    public function getPreFinAttribute()
    {
        if (! is_null($this->attributes['discount']) && $this->attributes['discount'] > 0) {
            $descuentoDecimal = $this->attributes['discount'] / 100;
            $precioConDescuento = $this->attributes['unit_price'] * (1 - $descuentoDecimal);

            return round($precioConDescuento, 2);
        }

        return $this->attributes['unit_price'];
    }
}
