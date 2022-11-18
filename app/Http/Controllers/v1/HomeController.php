<?php

namespace App\Http\Controllers\v1;

use Illuminate\Http\Request;
use App\Services\StoreService;
use App\Services\BannersService;
use App\Services\ProductService;
use App\Services\CountrieService;
use App\Services\CategorieService;
use App\Http\Controllers\Controller;
use App\Models\Countrie;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public $productService ;
    public $storeService ;
    public $categoryService ;

    public function __construct() {
        $this->productService = new ProductService;
        $this->storeService = new StoreService;
        $this->categoryService = new CategorieService;
        $this->bannersService = new BannersService;
        $this->countrieService = new CountrieService;
    }

    public function initData(Request $request) {
        $countrie= Countrie::where('code_pays',$request->code_country)->first();
        $country_banner= Countrie::where('code_pays',$request->country_banner)->first();
        if (!isset($countrie)) {
            $countrie = Countrie::first();
        }
        $products = [] ;
        $store = $this->storeService->TopStoreInCountrie($countrie);
        $bellyPoint = $this->storeService->getAllbellyPoint($country_banner,$request);
        // $banners = $this->bannersService->getBanners();
        $products = $this->productService->getProductInStoreViaCountrie($countrie);
        $categorie = $this->categoryService->getCategorieWithSub();
        $countrie = $this->countrieService->getAllCountrie();
        $myCurrency = $this->countrieService->getMyCurrency($request);
        $data = [
            'status' => 200,
            'stores' => $store,
            'banners' => $bellyPoint,
            'category' => $categorie,
            'topProducts' => $products,
            'homeProducts' => null,
            'inOffers' => null,
            'storeIds' => null,
            'cityInfo' => $countrie,
            'myCurrency' => $myCurrency->currency,

        ];

        return response()->json($data,200);
    }

    public function getMyCurrency(Request $request)
    {
        $myCurrency = $this->countrieService->getMyCurrency($request);
        $data = [
            'myCurrency' => $myCurrency->currency ,
            'convertCurrency' => $myCurrency->otherCurrency ,
        ];
        return response()->json($data,200);
    }
}
