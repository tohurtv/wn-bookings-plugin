<?php namespace Tohur\Bookings\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class AddCartProductIdToOrderProducts extends Migration
{
    public function up()
    {
        if (Schema::hasTable('offline_mall_order_products')) {
            Schema::table('offline_mall_order_products', function (Blueprint $table) {
                $table->unsignedInteger('cart_product_id')->nullable()->after('id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('offline_mall_order_products')) {
            Schema::table('offline_mall_order_products', function (Blueprint $table) {
                $table->dropColumn('cart_product_id');
            });
        }
    }
}
