<?php

namespace App\Services;

use App\Jobs\OfferProduct;
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

    public function createProduct($request,$user)
    {

        $data = $request->all();
        $data['store_id'] = $user->store->id ;
        $data['tva_id'] = $request->tva ;
        $data['medical_prescription'] = $request->medical_prescription ;
        $data = Products::create($data);

        if ($request->offer) {
            $offer = $data->option()->create([
             'exp_offer' => $request->exp_offer,
             'start_offer' => $request->start_offer
           ]);
           OfferProduct::dispatch($offer)->delay($offer->exp_offer);
        }

        if ($request->quantity != null) {
            $quantity = $data->quantity()->create([
                'stock' => $request->quantity,
                'in_stock' => true,
            ]);
        }

        $response = [
            'success' => false,
            'message' => $data,
            'status' => 201
        ];
        return $response ;
    }
}
