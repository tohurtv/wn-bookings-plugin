<?php namespace Tohur\Bookings\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class AddSessionLengthToMallBookings extends Migration
{
    public function up()
    {
    if (Schema::hasTable('tohur_bookings_bookings')) {
   Schema::table('tohur_bookings_bookings', function (Blueprint $table) {
    $table->integer('session_length')->default(30)->comment('Length in minutes');
    });
   
    }
}

    public function down()
    {
        if (Schema::hasTable('tohur_bookings_bookings')) {
            Schema::table('tohur_bookings_bookings', function (Blueprint $table) {
                $table->dropColumn('session_length');
            });
        }
    }
}
