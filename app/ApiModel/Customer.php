<?php

namespace App\ApiModel;

use App\ApiModel\Photo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $table = 'c_customers';
    protected $fillable = [
        'name',
        'phone',
    ];

    public function photos()
    {
        return $this->hasMany(Photo::class, 'customer_id', 'id');
    }
}