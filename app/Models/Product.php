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
        'codigo_interno',
        'nombre',
        'descripcion',
        'stock',
        'cantidad_minima',
        'en_promocion',
        'pre_uni',
        'pre_uni_may',
        'can_min_may',
        'descuento',
        'imagen',
        'unit_id',
        'product_category_id',
        'estado_registro',
        'pre_fin',
    ];

    protected $casts = [
        'en_promocion' => 'boolean',
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
        return $this->attributes['pre_uni'] ?? null;
    }

    public function getPreFinAttribute()
    {
        if (!is_null($this->attributes['descuento']) && $this->attributes['descuento'] > 0) {
            $descuentoDecimal = $this->attributes['descuento'] / 100;
            $precioConDescuento = $this->attributes['pre_uni'] * (1 - $descuentoDecimal);
            return round($precioConDescuento, 2);
        }
        return $this->attributes['pre_uni'];
    }
}
