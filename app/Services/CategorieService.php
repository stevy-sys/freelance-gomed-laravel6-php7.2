<?php

namespace App\Services;

use App\Models\Stores;
use App\Models\Category;
use App\Models\Products;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Auth;

class CategorieService 
{
    public function getCategorieWithSub(){
       return Category::with('subCategory')->get() ;
    }

    public function getAllSubCategorie(){
        return SubCategory::get() ;
    }
}
