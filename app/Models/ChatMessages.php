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

class ChatMessages extends Model
{
    protected $table = 'chat_message';

    public $timestamps = true; //by default timestamp false

    protected $fillable = ['room_id','uid','from_id','message','timestamp','message_type','status','extra_field'];

    protected $hidden = [
        'updated_at', 'created_at',
    ];

    protected $casts = [
        'status' => 'integer',
    ];
}
