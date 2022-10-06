<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Banners;

class BannersService
{
    public function getBanners()
    {
        return Banners::whereDate('from', '<=', Carbon::now())->whereDate('to', '>=', Carbon::now())->get();
    }
}
