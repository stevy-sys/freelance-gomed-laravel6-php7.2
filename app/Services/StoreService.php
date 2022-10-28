<?php

namespace App\Services;

use App\Models\BellyPoint;
use App\Models\Stores;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;

class StoreService 
{
    public function TopStoreInCountrie($countrie){
        return Stores::where(['status' => 1,'countrie_id'=>$countrie->id])->get();
    }

    public function getAllbellyPoint($countrie)
    {
        return BellyPoint::whereHas('store',function ($q) use($countrie){
           $q->where(['status' => 1,'countrie_id'=>$countrie->id]);
        })->get();
    }

    public function getbellyPoint($request)
    {
        return BellyPoint::with(['store','products'])->find($request->id);
    }
}
