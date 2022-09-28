<?php

namespace App\Services;

use App\Models\Stores;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;

class ProductService 
{
    public function getProductInStoreViaCountrie($countrie)
    {
        $products = Stores::with(['products' => function ($query){
            $query->where('status' , 1)->orderBy('rating', 'desc')->limit(15);
        },'countrie'])->where(['status' => 1 , 'countrie_id' => $countrie->id])->get()->pluck('products')->all()[0];
        
        return $products ;
    }
}
