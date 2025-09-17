<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
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
        if (!Schema::hasTable('activity_histories')) {
            return;
        }

        DbTableHelper::createTagDataTable('activity_history_tag_data');

        if (!Schema::hasTable('activity_history_tag_data')) {
            return;
        }

        \MetaFox\Activity\Jobs\MigrateActivityHistoryHashTagJob::dispatch();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_history_tag_data');
    }
};
