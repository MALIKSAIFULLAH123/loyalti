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
        if (!Schema::hasTable('apt_action_types')) {
            Schema::create('apt_action_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('package_id')->index();
                $table->string('name', 150)->index();
                $table->string('label_phrase', 255);
                $table->timestamps();

                $table->unique(['package_id', 'name'], 'unique_package_id_name');
            });
        }

        if (!Schema::hasTable('apt_transactions')) {
            return;
        }

        Schema::table('apt_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('action_id')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('apt_action_types');
    }
};
