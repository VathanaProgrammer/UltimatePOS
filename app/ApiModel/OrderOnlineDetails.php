<?php

namespace App\ApiModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Product;

class OrderOnlineDetails extends Model
{
    use HasFactory;

    protected $table = 'order_online_details';

    protected $fillable = [
        'order_online_id',
        'product_id',
        'qty',
        'price_at_order',
        'total_line',
        'image_url'
    ];

    public function order()
    {
        return $this->belongsTo(OnlineOrder::class, 'order_online_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
