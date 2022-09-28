<?php
namespace App\Services;
use App\Models\Countrie;

class CountrieService
{
    public function getAllCountrie()
    {
        return Countrie::all();
    }
}
