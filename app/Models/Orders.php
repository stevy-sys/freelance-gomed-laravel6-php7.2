<?php
/*
  Authors : Sayna (Rahul Jograna)
  Website : https://sayna.io/
  App Name : Grocery Delivery App
  This App Template Source code is licensed as per the
  terms found in the Website https://sayna.io/license
  Copyright and Good Faith Purchasers © 2021-present Sayna.
*/
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $table = 'orders';

    public $timestamps = true; //by default timestamp false
    protected $appends = ['product','humansUpdatedAt'];

    protected $fillable = ['uid','store_id','date_time','paid_method','order_to','orders','notes','address',
    'driver_id','assignee','total','tax','grand_total','discount','delivery_charge','wallet_used','wallet_price',
    'extra','pay_key','coupon_code','status','payStatus','extra_field'];

    protected $hidden = [
        // 'created_at',
    ];

    protected $casts = [
        'wallet_used' => 'integer',
        'payStatus' => 'integer'
    ];

    public function user(){
        return $this->belongsTo(User::class,'uid');
    }

    public function store(){
        return $this->belongsTo(Stores::class,'store_id');
    }

    public function getProductAttribute()
    {
        return json_decode($this->orders);
    }

    public function getHumansUpdatedAtAttribute() {
        $date = Carbon::parse($this->created_at)->diffForHumans();
        return $date ;
    }
}
