<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $table = 'document_types';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'estado_registro',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'estado_registro',
    ];

    public function persons()
    {
        return $this->hasMany(Person::class);
    }
}
