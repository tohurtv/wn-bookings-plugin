<?php namespace Tohur\Bookings\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class AddBookingDataToMallCartProducts extends Migration
{
    public function up()
    {
        if (Schema::hasTable('offline_mall_cart_products')) {
            Schema::table('offline_mall_cart_products', function (Blueprint $table) {
                $table->integer('booking_data')->nullable()->after('isbookable');
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
