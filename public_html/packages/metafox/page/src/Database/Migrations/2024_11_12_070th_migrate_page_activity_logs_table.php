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
        if (Schema::hasTable('page_activities')) {
            return;
        }

        Schema::create('page_activities', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->unsignedInteger('page_id')->index();
            DbTableHelper::morphUserColumn($table);
            DbTableHelper::morphItemColumn($table);

            $table->timestamps();
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
        Schema::dropIfExists('page_activities');
    }
};
