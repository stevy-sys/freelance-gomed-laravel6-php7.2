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
        return Countrie::where('code_pays',$request->code_country)->first();
    }
}
