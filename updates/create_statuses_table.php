<?php

namespace Tohur\Bookings\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class CreateStatusesTable extends Migration
{
    public function up()
    {
        Schema::create('tohur_bookings_statuses', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('name', 300);
            $table->string('ident', 300);
            $table->char('color', 7)->nullable();
            $table->boolean('enabled')->default(true);
            $table->smallInteger('sort_order')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tohur_bookings_statuses');
    }
}
