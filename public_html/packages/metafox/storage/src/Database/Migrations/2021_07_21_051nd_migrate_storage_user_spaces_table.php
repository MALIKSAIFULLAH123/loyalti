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
        // track user space
        if (!Schema::hasTable('storage_user_space')) {
            Schema::create('storage_user_space', function (Blueprint $table) {
                $table->unsignedBigInteger('id')
                ->primary();

                $table->unsignedBigInteger('space_size')
                ->default(0);
            });
        }

        if (!Schema::hasTable('storage_user_space_data')) {
            Schema::create('storage_user_space_data', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('user_id');

                $table->unsignedInteger('space_type')
                ->default(0);

                $table->unsignedBigInteger('space_size')
                ->default(0);

                $table->unique(['user_id', 'space_type']);
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
        Schema::dropIfExists('storage_user_space');
        Schema::dropIfExists('storage_user_space_data');
    }
};
