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
        'name',
        'abbreviation',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
