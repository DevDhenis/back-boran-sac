<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Access extends Model
{
    use HasFactory;

    protected $table = 'accesses';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'path',
        'icon',
        'access_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function parent()
    {
        return $this->belongsTo(Access::class, 'access_id');
    }

    public function children()
    {
        return $this->hasMany(Access::class, 'access_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'access_roles');
    }
}
