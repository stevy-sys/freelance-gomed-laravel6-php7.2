<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    public $timestamps = true;

    protected $guarded = [] ;

    public function countrie()
    {
        return $this->belongsTo(Countrie::class,'countrie_id');
    }
}
