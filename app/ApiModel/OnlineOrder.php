<?php

namespace App\ApiModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\ApiModel;

class OnlineOrder extends Model
{
    use HasFactory;

    protected $table = 'online_orders';

    protected $fillable = [
        'api_user_id',
        'saved_address_id',
        'address_type', // current or saved
        // Current address fields
        'current_house_number',
        'current_road',
        'current_village',
        'current_town',
        'current_city',
        'current_state',
        'current_postcode',
        'current_country',
        'current_country_code',
        // Payment
        'payment',
        'total_qty',
        'total',
        // Items stored as JSON
        'items',
        'status',
    ];

    // Cast items to array automatically
    protected $casts = [
        'items' => 'array',
    ];

    // Relationship to saved address
    public function savedAddress()
    {
        return $this->belongsTo(ApiUserAddress::class, 'saved_address_id');
    }

    // Relationship to user
    public function api_user()
    {
        return $this->belongsTo(ApiUser::class, 'api_user_id');
    }

        // Relationship to order details
    public function order_online_details()
    {
        return $this->hasMany(OrderOnlineDetails::class, 'order_online_id');
    }
}
