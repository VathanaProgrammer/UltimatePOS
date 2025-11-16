<?php

namespace App\ApiModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramStartToken extends Model
{
    use HasFactory;

    protected $table = 'telegram_start_tokens';

    protected $fillable = [
        'token',
        'order_online_id',
        'api_user_id',
        'expires_at',
        'used'
    ];
}