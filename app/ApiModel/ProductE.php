<?php

namespace App\ApiModel;

use App\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductE extends Model
{
    use HasFactory;

    protected $table = 'products_E';

    protected $fillable = [
        'product_id',
        'is_active',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Categories_E::class, 'category_id', 'id');
    }

}
