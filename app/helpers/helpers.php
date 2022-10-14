<?php

use App\Services\ProductService;

function convertCurrencyUser($detailPaiment,$request){
    $producSerice = new ProductService();
    $detailPaiment->orderUser = $detailPaiment->orderUser->map(function ($element) use($request,$producSerice){
        $element->product->priceLocale = $producSerice->convertCurrency(
            $request->myCurrency,
            $element->product->store->countrie->currency,
            $element->product->original_price
    );
        return $element ;
    });

    $totalLocal = 0 ;
    $orders = $detailPaiment->orderUser;
    foreach ($orders as $or) {
        $totalLocal += ($or->product->priceLocale*$or->quantity) ;
    }
    $detailPaiment->totalLocal = $totalLocal ;
    return $detailPaiment ;
}