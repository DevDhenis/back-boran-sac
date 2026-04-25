<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactTypePerson extends Model
{
    use HasFactory;

    protected $table = 'contact_type_person';
    protected $primaryKey = 'id';

    protected $fillable = [
        'person_id',
        'contact_type_id',
        'valor',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function contactType()
    {
        return $this->belongsTo(ContactType::class);
    }
}
