<?php namespace Tohur\Bookings\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class AddIsBookableToProducts extends Migration
{
    public function up()
    {
    if (Schema::hasTable('offline_mall_products') && !Schema::hasColumn('offline_mall_products', 'isbookable')) {
        Schema::table('offline_mall_products', function ($table) {
            $table->boolean('isbookable')->default(false);
        });
    }
    }


    public function down()
    {
    if (Schema::hasTable('offline_mall_products') && Schema::hasColumn('offline_mall_products', 'isbookable')) {
        Schema::table('offline_mall_products', function ($table) {
            $table->dropColumn('isbookable');
        });
    }
   }
}
