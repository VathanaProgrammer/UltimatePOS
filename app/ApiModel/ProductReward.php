<?php

namespace App\ApiModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Product;

class ProductReward extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'is_active', 'points_required'];

    public function product(){
        return $this->hasOne(Product::class, "product_id");
    }
}