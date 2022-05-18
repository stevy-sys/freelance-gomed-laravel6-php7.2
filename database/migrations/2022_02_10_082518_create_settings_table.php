<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('currencySymbol');
            $table->string('currencySide');
            $table->string('currencyCode');
            $table->string('appDirection');
            $table->string('logo');
            $table->string('sms_name');
            $table->text('sms_creds');
            $table->tinyInteger('delivery');
            $table->tinyInteger('findType')->default(0);
            $table->tinyInteger('makeOrders')->default(0);
            $table->tinyInteger('reset_pwd')->default(0);
            $table->tinyInteger('user_login')->default(0);
            $table->tinyInteger('store_login')->default(0);
            $table->tinyInteger('user_verify_with')->default(0);
            $table->double('search_radius',10,2)->default(10);
            $table->tinyInteger('driver_login')->default(0);
            $table->tinyInteger('web_login')->default(0);
            $table->tinyInteger('login_style')->default(1);
            $table->tinyInteger('register_style')->default(1);
            $table->tinyInteger('home_page_style_app')->default(1);
            $table->text('country_modal');
            $table->text('web_category');
            $table->string('default_country_code');
            $table->string('default_city_id')->nullable();
            $table->string('default_delivery_zip')->nullable();
            $table->text('social')->nullable();
            $table->text('app_color');
            $table->tinyInteger('app_status')->default(1);
            $table->tinyInteger('driver_assign')->default(0);
            $table->text('fcm_token')->nullable();
            $table->text('extra_field')->nullable();
            $table->tinyInteger('status');
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
        Schema::dropIfExists('settings');
    }
}
