<?php

namespace App\ApiModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiUserAddress extends Model
{
    use HasFactory;

    protected $table = 'api_user_addresses';

    protected $fillable = [
        'api_user_id',
        'label',
        'details',
        'phone',
        'coordinates',
    ];

    // Relationship to user
    public function api_user()
    {
        return $this->belongsTo(ApiUser::class, 'api_user_id');
    }

    // Relationship to orders that use this address
    public function orders()
    {
        return $this->hasMany(OnlineOrder::class, 'saved_address_id');
    }

    // Accessor for coordinates to automatically decode JSON
    public function getCoordinatesAttribute($value)
    {
        return json_decode($value, true);
    }

    // Mutator for coordinates to automatically encode JSON
    public function setCoordinatesAttribute($value)
    {
        $this->attributes['coordinates'] = json_encode($value);
    }
}
