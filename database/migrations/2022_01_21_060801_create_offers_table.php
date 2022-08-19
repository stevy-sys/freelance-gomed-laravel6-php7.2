<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->nullable();
            $table->double('off',10,2)->nullable();
            $table->string('type')->nullable();
            $table->double('upto',10,2)->nullable();
            $table->double('min',10,2)->nullable();
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->string('date_time')->nullable();
            $table->text('descriptions')->nullable();
            $table->text('image')->nullable();
            $table->tinyInteger('manage')->default(0); /// 0 = admin // 1 = store
            $table->integer('store_id')->nullable();
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
        Schema::dropIfExists('offers');
    }
}
