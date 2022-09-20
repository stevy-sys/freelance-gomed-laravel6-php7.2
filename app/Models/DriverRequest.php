<?php
/*
  Authors : Sayna (Rahul Jograna)
  Website : https://sayna.io/
  App Name : Grocery Delivery App
  This App Template Source code is licensed as per the
  terms found in the Website https://sayna.io/license
  Copyright and Good Faith Purchasers © 2021-present Sayna.
*/
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverRequest extends Model
{
    protected $table = 'driver_request';

    public $timestamps = true; //by default timestamp false

    protected $fillable = ['first_name','last_name','email','password','country_code','mobile','cover',
    'lat','lng','gender','city','address','status','extra_field'];

    protected $hidden = [
        'updated_at', 'created_at',
    ];

    protected $casts = [
        'status' => 'integer',
        'gender' => 'integer',
    ];
}
