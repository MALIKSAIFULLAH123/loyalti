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
 * @link \$PACKAGE_NAMESPACE$\Models
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('core_seo_meta')) {
            return;
        }

        if (!Schema::hasColumns('core_seo_meta', ['resource_name'])) {
            Schema::table('core_seo_meta', function (Blueprint $table) {
                $table->string('resource_name')->nullable();
            });
        }

        if (Schema::hasTable('core_seo_meta_schema')) {
            return;
        }

        Schema::create('core_seo_meta_schema', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('meta_id')->index();
            $table->json('schema')->nullable();
            $table->json('schema_default')->nullable();
            $table->boolean('is_modified')->default(false);
            $table->timestamps();
            $table->unique(['meta_id'], 'core_seo_meta_schema_uniq');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('core_seo_meta_schema');
        Schema::dropColumns('core_seo_meta', ['resource_name']);
    }
};
