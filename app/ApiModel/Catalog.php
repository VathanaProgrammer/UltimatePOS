<?php

namespace App\ApiModel;

use App\ApiModel\ProductE;
use App\ApiModel\Categories_E;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catalog extends Model
{

    use HasFactory;

    protected $table = 'catologs';
    
    protected $fillable = [
        'name',
        'description',
    ];

    public function categories()
    {
        return $this->hasMany(Categories_E::class, 'catalog_id', 'id');
    }
}
