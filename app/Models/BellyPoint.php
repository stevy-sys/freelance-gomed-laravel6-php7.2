<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BellyPoint extends Model
{
    protected $guarded = [] ;

    public function store()
    {
        return $this->belongsTo(Stores::class,'store_id');
    }

    public function products()
    {
        return $this->belongsToMany(Products::class);
    }
}
