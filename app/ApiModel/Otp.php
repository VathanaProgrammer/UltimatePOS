<?php

namespace App\ApiModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;
    protected $fillable = ['phone', 'code', 'expires_at'];
}
