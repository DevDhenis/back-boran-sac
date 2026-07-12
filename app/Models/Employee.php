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
        'work_schedule',
        'salary',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'status',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
