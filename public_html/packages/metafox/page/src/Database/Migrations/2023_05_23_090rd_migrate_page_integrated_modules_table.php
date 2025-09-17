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
        if (Schema::hasTable('page_integrated_modules')) {
            return;
        }
        Schema::create('page_integrated_modules', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->unsignedInteger('page_id')->index();

            DbTableHelper::moduleColumn($table);
            $table->string('name', 200);
            $table->string('label', 255)->nullable();
            $table->unsignedInteger('ordering')->default(0);
            $table->unsignedTinyInteger('is_active')->default(1);
        });
        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('page_integrated_modules');
    }
};
