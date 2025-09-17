<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Video\Models\Video;

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
        if (!Schema::hasTable('videos')) {
            return;
        }

        \MetaFox\Video\Jobs\UpdateVideoStatisticJob::dispatch();

        Video::withoutEvents(function () {
            Video::query()
                ->where('is_approved', 0)
                ->update([
                    'total_comment' => 0,
                    'total_like'    => 0,
                ]);
        });
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
