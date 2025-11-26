<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\ApiModel\ApiUser;
use App\Contact;
use App\Transaction;

class RewardHistory extends Model
{
    use HasFactory;
    
    protected $table = 'reward_point_histories';
    protected $fillable = [
        'contact_id',
        'api_user_id',
        'transaction_id',
        'online_order_id',
        'points',
        'type',
        'description',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function apiUser()
    {
        return $this->belongsTo(ApiUser::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}