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
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Drivers as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;
class Drivers extends Model implements JWTSubject
{
    use Notifiable,HasWallet;
    protected $table = 'drivers';

    public $timestamps = true; //by default timestamp false

    protected $fillable = ['first_name','last_name','email','password','country_code','mobile','cover',
    'lat','lng','gender','verified','fcm_token','current','others','stripe_key','date','city','address','status','extra_field'];

    protected $hidden = [
        'updated_at', 'created_at','password'
    ];

    protected $casts = [
        'status' => 'integer',
        'gender' => 'integer',
        'verified' => 'integer',
    ];

     /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Override the mail body for reset password notification mail.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\MailResetPasswordNotification($token));
    }
}
