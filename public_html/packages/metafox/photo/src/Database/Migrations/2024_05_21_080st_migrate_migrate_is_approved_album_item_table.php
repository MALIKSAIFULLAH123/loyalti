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
        if (!Schema::hasTable('photo_album_item')) {
            return;
        }

        if (Schema::hasColumn('photo_album_item', 'is_approved')) {
            return;
        }

        Schema::table('photo_album_item', function (Blueprint $table) {
            DbTableHelper::approvedColumn($table);
        });

        \MetaFox\Photo\Jobs\MigrateApprovedPhotoAlbumItemJob::dispatch();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('photo_album_item')) {
            return;
        }

        if (!Schema::hasColumn('photo_album_item', 'is_approved')) {
            return;
        }

        Schema::dropColumns('photo_album_item', ['is_approved']);
    }
};
