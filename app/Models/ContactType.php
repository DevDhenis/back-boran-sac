<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactType extends Model
{
    use HasFactory;

    protected $table = 'contact_types';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'regex',
        'icon',
    ];

    public function persons()
    {
        return $this->belongsToMany(Person::class, 'contact_type_person')->withPivot('value');
    }
}
