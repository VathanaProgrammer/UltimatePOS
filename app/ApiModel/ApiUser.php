<?php

namespace App\ApiModel;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class ApiUser extends Authenticatable implements JWTSubject
{
    protected $fillable = [
        "username", "phone"
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
