<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $table = 'persons';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'direccion',
        'imagen',
        'numero_documento',
        'document_type_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function client()
    {
        return $this->hasOne(Client::class);
    }

    public function contactTypes()
    {
        return $this->belongsToMany(ContactType::class, 'contact_type_person')->withPivot('valor');
    }
}
