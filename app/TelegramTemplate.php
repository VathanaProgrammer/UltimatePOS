<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Business;

class TelegramTemplate extends Model
{
    use HasFactory;

    protected $fillable = ["name", "greeting", 'business_id', "body", "footer", "auto_send"];

     public function business()
    {
        return $this->belongsTo(Business::class);
    }
}