<?php
namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Services\CurrencyService;

class CountrieController extends Controller
{
    public $service ;

    public function __construct() {
        $this->service = new CurrencyService;
    }

    public function getAllCountrie()
    {
        try {
            $response = $this->service->gelAllCurrency();
            return response()->json($response,$response['status']);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ],500);
        }
    }

    public function updateCurrency()
    {
        try {
            $response = $this->service->updateCurrency();

            return response()->json($response,$response['status']);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ],500);
        }
    }
}
