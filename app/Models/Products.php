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

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $table = 'products';

    public $timestamps = true; //by default timestamp false

    protected $fillable = ['store_id','cover','name','images','original_price','sell_price','discount','kind','cate_id',
    'sub_cate_id','in_home','is_single','have_gram','gram','have_kg','kg','have_pcs','pcs','have_liter','liter','have_ml',
    'ml','descriptions','key_features','disclaimer','exp_date','type_of','in_offer','in_stoke','rating','total_rating',
    'variations','size','status','extra_field','tva_id'];

    protected $hidden = [
        'updated_at', 'created_at',
    ];
    protected $appends = ['myQuantity','disponibility'];
    protected $casts = [
        'kind' => 'integer',
        'in_home' => 'integer',
        'is_single' => 'integer',
        'have_gram' => 'integer',
        'have_kg' => 'integer',
        'have_pcs' => 'integer',
        'have_liter' => 'integer',
        'have_ml' => 'integer',
        'type_of' => 'integer',
        'in_offer' => 'integer',
        'in_stoke' => 'integer',
        'size' => 'integer',
        'status' => 'integer',
    ];


    public function tva()
    {
        return $this->belongsTo(Tva::class,'tva_id');
    }

    public function store()
    {
        return $this->belongsTo(Stores::class,'store_id');
    }

    public function OrderUser()
    {
        return $this->hasOne(OrderUser::class,'product_id');
    }

    public function option()
    {
        return $this->hasOne(OptionProduct::class,'product_id');
    }

    public function quantity()
    {
        return $this->hasOne(QuantityProduct::class,'product_id');
    }

    public function getMyQuantityAttribute()
    {
        $detail = DetailPaimentUser::where(['uid'=>Auth::id(),'type' => 'user','paid_at' => null])->first();
        if (Auth::check() && Auth::user()->type == 'user') {
            $product = $detail->orderUser()->where('product_id',$this->id)->first();
            if (isset($product)) {
                return  $product->quantity;
            }
            return null ;
        }
        return null ;
    }

    public function getDisponibilityAttribute()
    {
        if ($this->quantity) {
            return $this->quantity->in_stock;
        }
        return null ;
    }

    
   
}
