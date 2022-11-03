<?php
namespace App\Services;
use App\Models\Countrie;

class CountrieService
{
    public function getAllCountrie()
    {
        return Countrie::all();
    }

    public function getMyCurrency($request)
    {
        // return Countrie::where('code_pays',$request->code_country)->first();
        $currency = Countrie::where('code_pays',$request->code_country)->first();
        if (isset($currency)) {
            return $currency;
        }else{
            return Countrie::first();
        }
    }

    public function createCountrie($request)
    {
        $countrie = Countrie::create([
            'currency' => $request->currency,
            'code_pays' => $request->code_pays,
            'pays' => $request->pays,
            'value_usd' => $request->value_usd
        ]);
        return [
            'data' => $countrie,
            'status' => 201
        ];
    }

    public function updateCountrie($request)
    {
        $countrie = Countrie::find($request->id);
        $countrie->update([
            'currency' => $request->currency,
            'code_pays' => $request->code_pays,
            'pays' => $request->pays,
            'value_usd' => $request->value_usd
        ]);
        return [
            'data' => $countrie,
            'status' => 201
        ];
    }
}
