<?php

namespace App\ApiModel;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class ApiUser extends Authenticatable implements JWTSubject
{
    protected $table = "api_users";
    
    protected $fillable = [
        'telegram_chat_id',
        'contact_id',
        'profile_url',
        'password'
    ];

    public function contact()
    {
        return $this->belongsTo(\App\Contact::class);
        // Make sure the namespace matches where your Contact model is
    }

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