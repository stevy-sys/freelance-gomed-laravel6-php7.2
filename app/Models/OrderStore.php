<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStore extends Model
{
    public $timestamps = true;

    protected $guarded = [] ;

    public function mediable()
    {
        return $this->morphOne(Media::class,'mediable');
    }
    
    public function product()
    {
        return $this->belongsTo(Products::class,'product_id');
    }

    public function detailPaiment()
    {
        return $this->belongsTo(DetailPaimentUser::class,'detail_id');
    }
}
