<?php

namespace App\ApiModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\ApiModel;

class Categories_E extends Model
{
    use HasFactory;

    protected $table = 'categories_E';
    protected $fillable = [
        "name",
        "catalog_id",
        "description",
    ];

    public function catolog(){
        return $this->belongTo(Catalog::class, 'catolog_id', 'id');
    }

    public function product_e(){
        return $this->hasMany(ProductE::class, 'category_id', 'id');
    }
}
