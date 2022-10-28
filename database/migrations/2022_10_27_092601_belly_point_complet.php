<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BellyPointComplet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('belly_points', function (Blueprint $table) {
            $table->integer('store_id');
            $table->string('name');
            $table->integer('lng')->nullable();
            $table->integer('lat')->nullable();
            $table->string('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('belly_points', function (Blueprint $table) {
            $table->dropColumn('store_id');
            $table->dropColumn('name');
            $table->dropColumn('lng');
            $table->dropColumn('lat');
            $table->dropColumn('description');
        });
    }
}
