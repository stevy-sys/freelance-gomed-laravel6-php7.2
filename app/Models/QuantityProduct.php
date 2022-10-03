<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuantityProduct extends Model
{

    protected $guarded = [] ;
    public function product()
    {
        return $this->belongsTo(Products::class,'product');
    }
}
