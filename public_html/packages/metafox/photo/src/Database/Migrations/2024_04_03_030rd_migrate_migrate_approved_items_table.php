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
        $this->migratePhotoGroupItem();
    }

    protected function migratePhotoGroupItem(): void
    {
        if (!Schema::hasTable('photo_group_items')) {
            return;
        }

        Schema::table('photo_group_items', function (Blueprint $table) {
            DbTableHelper::approvedColumn($table);
        });

        \MetaFox\Photo\Jobs\MigrateApprovedPhotoGroupItemJob::dispatch();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
