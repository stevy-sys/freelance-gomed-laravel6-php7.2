<?php

namespace App\Services;

use App\Jobs\OfferProduct;
use App\Models\Stores;
use App\Models\Products;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ProductService 
{
    public function getProductInStoreViaCountrie($countrie)
    {
        $products = Stores::with(['products' => function ($query){
            $query->with(['offer','quantity'])->where('status' , 1)->orderBy('rating', 'desc')->limit(15);
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
            $offer = $data->offer()->create([
                'rates' => $request->offer,
                'exp_offer' => $request->exp_offer,
                'start_offer' => $request->start_offer
            ]);
           OfferProduct::dispatch($offer)->delay(Carbon::parse($offer->exp_offer));
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
            'status' => 200
        ];
        return $response ;
    }

    public function convertCurrency($to,$currency,$priceCountr)
    {
        $price = null ;
        if ($to == 'MGA') {
            if ($currency == '€') {
                $price = $priceCountr/0.00024;
            }
            if ($currency == '$') {
                $price = $priceCountr/0.00023;
            }
            if ($currency == 'MGA') {
                $price = $priceCountr;
            }
        }

        if ($to == '€') {
            if ($currency == 'MGA') {
                $price = $priceCountr/4160.57;
            }
            if ($currency == '$') {
                $price = $priceCountr/0.97;
            }
            if ($currency == '€') {
                $price = $priceCountr;
            }
        }

        if ($to == '$') {
            if ($currency == 'MGA') {
                $price = $priceCountr/4276.09;
            }
            if ($currency == '€') {
                $price = $priceCountr/1.03;
            }
            if ($currency == '$') {
                $price = $priceCountr;
            }
        }
        return $price ;
        // return [
        //     'data' => $price,
        //     'status' => 200
        // ];
    }

}
