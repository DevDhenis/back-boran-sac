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
        'name',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'status',
    ];

    public function persons()
    {
        return $this->hasMany(Person::class);
    }
}
