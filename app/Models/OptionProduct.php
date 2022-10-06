<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptionProduct extends Model
{
    public $timestamps = true; //by default timestamp false
    protected $guarded = [] ;

    public function product()
    {
        return $this->belongsTo(Products::class,'product_id');
    }
}
