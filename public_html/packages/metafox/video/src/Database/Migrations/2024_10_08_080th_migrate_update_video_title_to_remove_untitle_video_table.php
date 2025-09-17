<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Video\Jobs\MigrateVideoTitle;

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
        if (!Schema::hasTable('videos')) {
            return;
        }

        if (!Schema::hasTable('photo_groups')) {
            return;
        }

        if (!Schema::hasTable('activity_feeds')) {
            return;
        }

        MigrateVideoTitle::dispatch();
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
