<?php

namespace App\ApiModel;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class ApiUser extends Authenticatable implements JWTSubject
{
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'phone', 'profile_url',
    ];

    // 🔹 Required by JWTAuth
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
