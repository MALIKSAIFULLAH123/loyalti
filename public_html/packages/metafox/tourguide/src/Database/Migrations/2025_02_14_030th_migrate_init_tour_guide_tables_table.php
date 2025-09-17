<?php

use MetaFox\Platform\Support\DbTableHelper;
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
        if (!Schema::hasTable('tour_guides')) {
            Schema::create('tour_guides', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                $table->string('name');
                $table->text('url');
                $table->string('page_name');
                $table->unsignedTinyInteger('privacy')->default(0);
                $table->boolean('is_auto')->default(true);
                $table->boolean('is_active')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tour_guide_steps')) {
            Schema::create('tour_guide_steps', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tour_guide_id')->index();
                $table->text('title_var');
                $table->text('desc_var');
                $table->unsignedInteger('ordering')->default(0);
                $table->unsignedInteger('delay')->default(0);
                $table->string('background_color', 50)->nullable()->default(null);
                $table->string('font_color', 50)->nullable()->default(null);
                $table->text('element');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tour_guide_hidden')) {
            Schema::create('tour_guide_hidden', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tour_guide_id')->index();
                $table->unsignedBigInteger('user_id')->index();
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
        Schema::dropIfExists('tour_guides');
        Schema::dropIfExists('tour_guide_steps');
        Schema::dropIfExists('tour_guide_hidden');
    }
};
