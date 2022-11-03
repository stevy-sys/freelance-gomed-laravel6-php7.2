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

use Illuminate\Database\Eloquent\Model;

class Stores extends Model
{
    protected $table = 'store';

    public $timestamps = true; //by default timestamp false

    protected $fillable = ['uid','name','mobile','lat','lng','verified','address','descriptions','images',
    'cover','commission','open_time','close_time','isClosed','certificate_url','certificate_type',
    'rating','total_rating','cid','zipcode','status','extra_field','countrie_id'];

    protected $hidden = [
        'updated_at', 'created_at'
    ];

    protected $casts = [
        'status' => 'integer',
        'verified' => 'integer',
        'isClosed' => 'integer',
    ];

    public function bellyPoint()
    {
        return $this->hasMany(BellyPoint::class,'store_id');
    }

    public function media()
    {
        return $this->morphOne(Media::class,'mediable');
    }

    public function user(){
        return $this->belongsTo(User::class,'uid');
    }

    public function orders(){
        return $this->hasMany(Orders::class,'store_id');
    }

    public function products() {
        return $this->hasMany(Products::class,'store_id');
    }

    public function countrie()
    {
        return $this->belongsTo(Countrie::class,'countrie_id');
    }
}
