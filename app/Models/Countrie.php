<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Countrie extends Model
{
    public $timestamps = true;

    protected $guarded = [] ;

    public function otherCurrency()
    {
        return $this->hasOne(Currency::class,'countrie_id');
    }

    public function stores()
    {
        return $this->hasMany(Stores::class,'countrie_id');
    }
}
