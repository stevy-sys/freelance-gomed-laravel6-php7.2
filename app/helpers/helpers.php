<?php

use App\Services\ProductService;

function convertCurrencyUser($detailPaiment,$request){
    $producSerice = new ProductService();
    $detailPaiment->orderUser = $detailPaiment->orderUser->map(function ($element) use($request,$producSerice){
        $element->product->priceLocale = $producSerice->convertCurrency(
                $request->myCurrency,
                $element->product->store->countrie->currency,
                $element->product->offer ? ($element->product->original_price - ($element->product->original_price * $element->product->offer->rates)/100) : $element->product->original_price
        );
        $element->totalLocal = $producSerice->convertCurrency(
                $request->myCurrency,
                $element->product->store->countrie->currency,
                $element->total
        );
        return $element ;
    });

    $totalLocal = 0 ;
    $orders = $detailPaiment['orderUser'];
    foreach ($orders as $or) {
        $totalLocal += ($or->product->priceLocale*$or->quantity) ;
    }
    $detailPaiment->totalLocal = $totalLocal ;
    return $detailPaiment ;
}


// function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'miles') {
//     $theta = $longitude1 - $longitude2; 
//     $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta))); 
//     $distance = acos($distance); 
//     $distance = rad2deg($distance); 
//     $distance = $distance * 60 * 1.1515; 
//     switch($unit) { 
//       case 'miles': 
//         break; 
//       case 'kilometers' : 
//         $distance = $distance * 1.609344; 
//     } 
//     return (round($distance,2)); 
// }

function getDistanceBetweenPointsNew($lat1, $lng1, $lat2, $lng2) {
    $earth_radius = 6378137;   // Terre = sphère de 6378km de rayon
    $rlo1 = deg2rad($lng1);
    $rla1 = deg2rad($lat1);
    $rlo2 = deg2rad($lng2);
    $rla2 = deg2rad($lat2);
    $dlo = ($rlo2 - $rlo1) / 2;
    $dla = ($rla2 - $rla1) / 2;
    $a = (sin($dla) * sin($dla)) + cos($rla1) * cos($rla2) * (sin($dlo) * sin($dlo));
    $d = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return ($earth_radius * $d);
  }