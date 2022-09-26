<?php

namespace App\Services;

use App\Models\Stores;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;

class StoreService 
{
    public function TopStoreInCountrie($request){
        return Stores::where(['status' => 1, 'cid' => $request->countrie])->get();
    }
}
