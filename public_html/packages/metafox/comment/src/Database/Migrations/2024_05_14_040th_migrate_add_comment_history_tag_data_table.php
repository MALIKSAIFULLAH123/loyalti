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
        if (!Schema::hasTable('comment_histories')) {
            return;
        }

        DbTableHelper::createTagDataTable('comment_history_tag_data');

        if (!Schema::hasTable('comment_history_tag_data')) {
            return;
        }

        \MetaFox\Comment\Jobs\MigrateCommentHistoryHashTagJob::dispatch();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_history_tag_data');
    }
};
