<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessRole extends Model
{
    use HasFactory;

    protected $table = 'access_roles';
    protected $primaryKey = 'id';

    protected $fillable = [
        'role_id',
        'access_id',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function access()
    {
        return $this->belongsTo(Access::class);
    }
}
