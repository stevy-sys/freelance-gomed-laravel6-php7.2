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

    public function getAllbellyPoint($countrie,$request)
    {
        $store =  Stores::with('media')->where(['status' => 1,'countrie_id'=>$countrie->id])->get();
        $store = $store->filter(function ($s) use ($request){
                if (getDistanceBetweenPoints($request->lng,$request->lat,$s->lat,$s->lng) <= 5000) {
                    $dist = getDistanceBetweenPoints($request->lng,$request->lat,$s->lat,$s->lng) ;
                    
                    $s->distance = $dist ;
                    return $s ;
                }
        })->values();
        return  $store;
    }

    public function getbellyPoint($request)
    {
        return BellyPoint::with(['store','products'])->find($request->id);
    }

    public function searchStore($request)
    {
        return Stores::where('name','like','%'.$request->search.'%');
    }
}
