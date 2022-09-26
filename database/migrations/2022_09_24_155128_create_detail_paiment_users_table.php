<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailPaimentUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_paiment_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('uid');
            $table->integer('user_owner')->nullable();
            $table->string('type');
            $table->integer('grand_total')->default(0);
            $table->string('delivery_option')->nullable();
            $table->string('type_receive')->nullable();
            $table->datetime('paid_at')->nullable();
            $table->integer('queue_id')->nullable();
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
        Schema::dropIfExists('detail_paiment_users');
    }
}
