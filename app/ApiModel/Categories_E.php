<?php

namespace App\ApiModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongTo(Catolog::class, 'catolog_id', 'id');
    }
}
