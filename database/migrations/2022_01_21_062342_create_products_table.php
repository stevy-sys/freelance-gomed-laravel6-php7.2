<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('store_id');
            $table->text('cover');
            $table->text('name');
            $table->text('images');
            $table->double('original_price',10,2)->nullable();
            $table->double('sell_price',10,2)->nullable();
            $table->double('discount',10,2)->nullable();
            $table->tinyInteger('kind')->nullable();
            $table->integer('cate_id')->nullable();
            $table->integer('sub_cate_id')->nullable();
            $table->tinyInteger('in_home')->nullable();
            $table->tinyInteger('is_single')->nullable();
            $table->tinyInteger('have_gram')->nullable();
            $table->string('gram')->nullable();
            $table->tinyInteger('have_kg')->nullable();
            $table->string('kg')->nullable();
            $table->tinyInteger('have_pcs')->nullable();
            $table->string('pcs')->nullable();
            $table->tinyInteger('have_liter')->nullable();
            $table->string('liter')->nullable();
            $table->tinyInteger('have_ml')->nullable();
            $table->string('ml')->nullable();
            $table->text('descriptions')->nullable();
            $table->text('key_features')->nullable();
            $table->text('disclaimer')->nullable();
            $table->date('exp_date')->nullable();
            $table->tinyInteger('type_of')->default(2); // 1  = veg  // 0 = non
            $table->tinyInteger('in_offer')->default(2);
            $table->tinyInteger('in_stoke')->default(0);
            $table->double('rating',10,2)->nullable();
            $table->integer('total_rating')->nullable();
            $table->text('variations')->nullable();
            $table->tinyInteger('size')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->text('extra_field')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
