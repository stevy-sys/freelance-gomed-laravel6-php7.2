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

class Category extends Model
{
    protected $table = 'category';

    public $timestamps = true; //by default timestamp false

    protected $fillable = ['name','cover','status','extra_field'];

    protected $hidden = [
        'updated_at', 'created_at',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function subCategory() {
        return $this->hasMany(SubCategory::class,'cate_id');
    }

    public function media()
    {
        return $this->morphOne(Media::class,'mediable');
    }
}
