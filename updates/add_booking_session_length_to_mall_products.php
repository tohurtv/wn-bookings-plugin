<?php namespace Tohur\Bookings\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class AddBookingSessionLengthToMallProducts extends Migration
{
    public function up()
    {
        if (Schema::hasTable('offline_mall_products')) {
            Schema::table('offline_mall_products', function (Blueprint $table) {
                $table->integer('booking_session_length')->nullable()->after('isbookable');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('offline_mall_products')) {
            Schema::table('offline_mall_products', function (Blueprint $table) {
                $table->dropColumn('booking_session_length');
            });
        }
    }
}
