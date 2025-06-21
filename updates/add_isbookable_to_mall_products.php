<?php namespace Tohur\Bookings\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class AddIsBookableToMallProducts extends Migration
{
    public function up()
    {
        if (Schema::hasTable('offline_mall_products')) {
            Schema::table('offline_mall_products', function (Blueprint $table) {
                $table->boolean('isbookable')->default(false);
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('offline_mall_products')) {
            Schema::table('offline_mall_products', function (Blueprint $table) {
                $table->dropColumn('isbookable');
            });
        }
    }
}
