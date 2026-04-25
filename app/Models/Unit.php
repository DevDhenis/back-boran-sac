<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'abreviatura',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
