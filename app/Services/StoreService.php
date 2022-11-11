<?php

namespace App\Services;

use App\Models\BellyPoint;
use App\Models\OptionProduct;
use App\Models\Stores;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;

class StoreService 
{
    public function TopStoreInCountrie($countrie){
        $store = Stores::whereHas('products',function ($query){
            $query->whereHas('offer');
        })->with('media')->where('countrie_id',$countrie->id)->get()->sortByDesc('product.offer.rates')->all();
        return $store ;
        //return Stores::where(['status' => 1,'countrie_id'=>$countrie->id])->get();
    }

    public function getAllbellyPoint($countrie,$request)
    {
        $store =  Stores::with('media')->where(['status' => 1,'countrie_id'=>$countrie->id])->get();
        $store = $store->filter(function ($s) use ($request){
                if (getDistanceBetweenPoints($request->lat,$request->lng,$s->lat,$s->lng) <= 10000) {
                    $dist = getDistanceBetweenPoints($request->lat,$request->lng,$s->lat,$s->lng) ;
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
