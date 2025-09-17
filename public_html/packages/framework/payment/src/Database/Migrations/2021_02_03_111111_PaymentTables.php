<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * @ignore
 * @codeCoverageIgnore
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (!Schema::hasTable('payment_gateway')) {
            Schema::create('payment_gateway', function (Blueprint $table) {
                $table->increments('id');
                $table->string('service', 50)->unique();
                $table->boolean('is_active')->default(0);
                $table->boolean('is_test')->default(0);
                $table->string('title', 100);
                $table->mediumText('description');
                $table->mediumText('config');
                $table->text('service_class');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_gateway');
    }
};
