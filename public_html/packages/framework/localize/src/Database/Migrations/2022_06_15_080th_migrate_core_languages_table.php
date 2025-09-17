<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models\
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('core_languages')) {
            Schema::create('core_languages', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('language_code');
                $table->string('charset')->default('utf-8');
                $table->string('direction')->default('ltr');
                $table->smallInteger('is_default')->default(0);
                $table->smallInteger('is_active')->default(1);
                $table->smallInteger('is_master')->default(0);
                $table->integer('store_id')->default(0);
                $table->timestamps();
            });
        }

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('core_languages');
    }
};
