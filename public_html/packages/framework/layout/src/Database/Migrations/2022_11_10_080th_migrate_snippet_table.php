<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * stub: /packages/database/migration.stub.
 */

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
        if (!Schema::hasTable('layout_snippets')) {
            Schema::create('layout_snippets', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('theme');
                $table->string('type');
                $table->string('variant')->nullable();
                $table->string('snippet')->nullable();
                $table->string('name');
                $table->mediumText('data');
                $table->unsignedTinyInteger('is_active')->default(0);
                $table->unsignedInteger('revision_id')->default(0);
                $table->timestamps();
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
        Schema::dropIfExists('layout_snippets');
    }
};
