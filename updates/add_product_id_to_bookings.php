<?php

namespace Tohur\Bookings\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class AddProductIdToMallBookings extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tohur_bookings_bookings')) {
            Schema::table('tohur_bookings_bookings', function (Blueprint $table) {
                $table->unsignedInteger('product_id')->nullable()->after('id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('tohur_bookings_bookings')) {
            Schema::table('tohur_bookings_bookings', function (Blueprint $table) {
                $table->dropColumn('product_id');
            });
        }
    }
}
