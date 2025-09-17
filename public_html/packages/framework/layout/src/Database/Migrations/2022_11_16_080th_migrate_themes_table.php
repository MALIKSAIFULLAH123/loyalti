<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

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
        if (!Schema::hasTable('layout_themes')) {
            Schema::create('layout_themes', function (Blueprint $table) {
                $table->id('id');
                $table->string('theme_id');
                $table->string('resolution')->default('web');
                $table->string('title');
                $table->unsignedTinyInteger('is_active')->default(0);
                $table->unsignedTinyInteger('is_system')->default(0);
                $table->unsignedInteger('total_variant')->default(0);
                $table->string('thumb_url')->nullable();
                DbTableHelper::moduleColumn($table);
                $table->timestamps();
                $table->unique(['theme_id']);
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
        Schema::dropIfExists('layout_themes');
    }
};
