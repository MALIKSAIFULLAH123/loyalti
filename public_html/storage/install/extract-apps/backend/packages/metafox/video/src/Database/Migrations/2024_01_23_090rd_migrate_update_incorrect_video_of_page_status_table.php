<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Eloquent\Builder;
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

        $query = Video::query()->where('user_type', 'page')
            ->whereNull('video_url')
            ->where('in_process', Video::STATUS_FAILED)
            ->where(function (Builder $query) {
                $query->whereNotNull('video_file_id')
                    ->where('video_file_id', '>', 0);
            });

        foreach ($query->cursor() as $video) {
            if (!$video instanceof Video) {
                continue;
            }

            $video->updateQuietly(['in_process' => Video::STATUS_READY]);
        }
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
