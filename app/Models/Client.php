<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';
    protected $primaryKey = 'id';

    protected $fillable = [
        'person_id',
        'cantidad_compras',
        'cantidad_compras_aceptadas',
        'cantidad_compras_rechazadas',
        'cantidad_compras_devueltas',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
