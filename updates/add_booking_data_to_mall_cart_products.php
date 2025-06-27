<?php

namespace Tohur\Bookings\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class AddBookingDataToMallCartProducts extends Migration
{
    public function up()
    {
        if (Schema::hasTable('offline_mall_cart_products')) {
            Schema::table('offline_mall_cart_products', function (Blueprint $table) {
                $table->json('booking_data')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('offline_mall_cart_products')) {
            Schema::table('offline_mall_cart_products', function (Blueprint $table) {
                $table->dropColumn('booking_data');
            });
        }
    }
}
