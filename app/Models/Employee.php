<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';
    protected $primaryKey = 'id';

    protected $fillable = [
        'person_id',
        'horario_laboral',
        'sueldo',
        'estado_registro',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'estado_registro',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
