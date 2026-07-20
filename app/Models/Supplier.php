<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'ruc',
        'contact_name',
        'phone',
        'email',
        'address',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
