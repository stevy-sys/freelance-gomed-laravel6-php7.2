<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPaimentUser extends Model
{
    public $timestamps = true;

    protected $guarded = [] ;

    public function orderUser()
    {
        return $this->hasMany(OrderUser::class,'detail_id');
    }

    public function orderStore()
    {
        return $this->hasMany(OrderStore::class,'detail_id');
    }

    public function userOwner()
    {
        return $this->belongsTo(User::class,'user_owner');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'uid');
    }
}
