<?php

namespace App\Services;

use App\Jobs\OfferProduct;
use App\Models\Countrie;
use App\Models\Stores;
use App\Models\Products;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;

class ProductService 
{
    public $serviceMedia ;

    public function __construct() {
        $this->serviceMedia = new MediaService;
    }
    public function getProductInStoreViaCountrie($countrie)
    {
        $products = Products::whereHas('store',function ($q) use($countrie){
            $q->wherehas('countrie',function ($q) use($countrie) {
                $q->where('id',$countrie->id);
            });
        })->with(['offer','quantity','store.countrie','couverture'])->where('status',1)->orderBy('stars', 'desc')->take(10)->get();
        return $products ;
    }

    public function createProduct($request,$user)
    {
        $data = $request->all();
        $media = $data['media'];
        $data = Arr::except($data, ['media']);
        $data['store_id'] = $user->store->id ;
        $data['tva_id'] = $request->tva ;
        $data['medical_prescription'] = $request->medical_prescription ;
        
        $data = Products::create($data);
        if ($request->offer) {
            if (Carbon::parse($request->start_offer) <= Carbon::now()) {
                OfferProduct::dispatch($data,$request)->delay(Carbon::now());
            }
            else{
                OfferProduct::dispatch($data,$request)->delay(Carbon::parse($request->start_offer));
            }
        }

        if ($request->quantity != null) {
            $quantity = $data->quantity()->create([
                'stock' => $request->quantity,
                'in_stock' => true,
            ]);
        }

        if (isset($media)) {
            if (isset($media["couverture"])) {
                $response = $this->serviceMedia->decodebase64($media["couverture"],'product');
                $data->mediable()->create([
                    'file' => $response['path'],
                    'status' => 1,
                    'type' => 'couverture',
                    'extention' => $response['type'],
                ]);
            }

            if (isset($media['file'])) {
                foreach ($media['file'] as $file) {
                    $response = $this->serviceMedia->decodebase64($file,'product');
                    $data->mediable()->create([
                        '' => ''
                    ]);
                }
            }
        }

        // a rectifier cette sleep
        sleep(3);
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
        $countrie = Countrie::with('otherCurrency')->where('currency',$to)->first();
        $curr = $countrie->otherCurrency;
        if ($to == 'MGA') {
            if ($currency == '€') {
                $price = $priceCountr/$curr->Euro;
            }
            if ($currency == '$') {
                $price = $priceCountr/$curr->Dollard;
            }
            if ($currency == 'MGA') {
                $price = $priceCountr;
            }
        }

        if ($to == '€') {
            if ($currency == 'MGA') {
                $price = $priceCountr/$curr->Mga;
            }
            if ($currency == '$') {
                $price = $priceCountr/$curr->Dollard;
            }
            if ($currency == '€') {
                $price = $priceCountr;
            }
        }

        if ($to == '$') {
            if ($currency == 'MGA') {
                $price = $priceCountr/$curr->Mga;
            }
            if ($currency == '€') {
                $price = $priceCountr/$curr->Euro;
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
