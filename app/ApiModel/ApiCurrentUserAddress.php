<?php

namespace App\ApiModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiCurrentUserAddress extends Model
{
    use HasFactory;
    protected $table = 'api_current_user_addresses';

    protected $fillable = [
        'label',
        'phone',
        'details',
        'coordinates',
        'short_address'
    ];

    protected $casts = [
        'coordinates' => 'array',
    ];
}
