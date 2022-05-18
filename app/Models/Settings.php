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

class Settings extends Model
{
    protected $table = 'settings';

    public $timestamps = true; //by default timestamp false

    protected $fillable = ['currencySymbol','currencySide','currencyCode','appDirection','logo','sms_name',
    'sms_creds','delivery','reset_pwd','user_login','store_login','driver_login','web_login',
    'country_modal','web_category','default_country_code','app_color','app_status','driver_assign','default_city_id','default_delivery_zip','social',
    'fcm_token','status','extra_field','findType','makeOrders','user_verify_with','search_radius','login_style','register_style','home_page_style_app'];

    protected $hidden = [
        'updated_at', 'created_at','sms_creds','fcm_token','search_radius'
    ];

    protected $casts = [
        'delivery' => 'integer',
        'findType' => 'integer',
        'makeOrders' => 'integer',
        'reset_pwd' => 'integer',
        'user_login' => 'integer',
        'store_login' => 'integer',
        'driver_login' => 'integer',
        'web_login' => 'integer',
        'app_status' => 'integer',
        'driver_assign' => 'integer',
        'status' => 'integer',
        'user_verify_with' => 'integer',
        'login_style' => 'integer',
        'register_style' => 'integer',
        'home_page_style_app' => 'integer'
    ];
}
