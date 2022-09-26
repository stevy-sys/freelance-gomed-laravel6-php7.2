<?php

namespace App\Services;

use App\Models\Stores;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;

class ProductService 
{
    public function getProductInStoreViaCountrie($request)
    {
        $products = Stores::with(['products' => function ($query){
            $query->with(['OrderUser' => function ($q){
                $q->with(['detailPaiment'=>function ($q1){
                    $q1->where('uid',Auth::id());
                }]);
            }])->where('status' , 1)->orderBy('rating', 'desc')->limit(15);
        }])->where(['status' => 1, 'cid' => $request->countrie])->get()->pluck('products')->all()[0];

        return $products;
    }
}
