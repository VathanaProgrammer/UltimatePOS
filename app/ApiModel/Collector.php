<?php

namespace App\ApiModel;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Collector extends Authenticatable implements JWTSubject
{
    protected $table = 'collector';
    protected $fillable = ['username','phone','password','status','role'];
    protected $hidden = ['password'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}