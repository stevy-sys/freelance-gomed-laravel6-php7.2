<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStore extends Model
{
    public $timestamps = true;

    protected $guarded = [] ;

    public function product()
    {
        return $this->belongsTo(Products::class,'product_id');
    }
}
