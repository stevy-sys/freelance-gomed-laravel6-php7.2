<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePopupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('popup', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('shown')->nullable();
            $table->text('message')->nullable();
            $table->datetime('date_time')->nullable();
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
        Schema::dropIfExists('popup');
    }
}
