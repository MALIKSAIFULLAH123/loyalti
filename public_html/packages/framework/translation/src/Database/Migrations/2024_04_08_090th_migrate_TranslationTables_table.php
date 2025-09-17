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
        if (!Schema::hasTable('translation_gateway')) {
            Schema::create('translation_gateway', function (Blueprint $table) {
                $table->increments('id');
                $table->string('service', 50)->unique();
                $table->boolean('is_active')->default(0);
                $table->string('title', 100);
                $table->mediumText('description');
                $table->mediumText('config');
                $table->text('service_class');
                $table->text('module_id')->nullable()->default(null);
            });
        }

        if (!Schema::hasTable('translation_text')) {
            Schema::create('translation_text', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->string('entity_type')->nullable();
                $table->string('language_id')->nullable();
                $table->text('text');
                $table->index('entity_id');
                $table->index(['entity_id', 'entity_type'], 'ix_entity_translated_text');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_gateway');
        Schema::dropIfExists('translation_text');
    }
};
