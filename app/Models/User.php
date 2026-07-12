<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'users';

    protected $primaryKey = 'id';

    protected $fillable = [
        'username',
        'password',
        'email',
        'email_verified_at',
        'verification_code',
        'recovery_code',
        'status',
        'role_id',
        'person_id',
    ];

    protected $hidden = [
        'password',
        'verification_code',
        'status',
    ];

    public function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function client()
    {
        return $this->hasOne(Client::class, 'person_id', 'person_id');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'person_id', 'person_id');
    }
}
