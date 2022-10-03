<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferProduct extends Model
{
    public function product()
    {
        return $this->belongsTo(Products::class,'product_id');
    }
}
