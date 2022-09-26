<?php

namespace App\Services;

use App\Models\Stores;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;

class CategorieService 
{
    public function getCategorieWithSub(){
       return Category::with('subCategory')->get() ;
    }
}
