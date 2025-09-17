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
        if (!Schema::hasTable('activity_pins')) {
            Schema::create('activity_pins', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::setupResourceColumns($table, true, false, false, false);
                DbTableHelper::morphOwnerColumn($table, true);

                $table->unsignedBigInteger('feed_id')->index();

                $table->unique(['user_id', 'feed_id', 'owner_id']);

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
        Schema::dropIfExists('activity_pins');
    }
};
