<?php

use Illuminate\Database\Migrations\Migration;
use MetaFox\BackgroundStatus\Models\BgsBackground;
use MetaFox\Storage\Models\StorageFile;
use Illuminate\Support\Str;
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
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('storage_files')) {
            return;
        }

        $storageFileId = BgsBackground::query()
            ->where('image_path', 'like', '%o1/bg18%')
            ->get(['image_file_id'])
            ->collect()
            ->pluck('image_file_id')
            ->first();

        // Case 1: If this is a fresh install => skip
        // Case 2: If data is ok, running it again just still update the record again. No big affect
        if (!$storageFileId) {
            return;
        }

        $variants = app('storage')->getByOriginals($storageFileId);
        $variants->each(function (mixed $file) {
            if (!$file instanceof StorageFile) {
                return false;
            }

            if ($file->variant === 'origin') {
                return true;
            }

            $file->update(['path' => Str::replace('bg19', 'bg18', $file->path)]);

            return true;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('update_typo_status_bg_variants');
    }
};
