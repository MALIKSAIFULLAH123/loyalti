<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\BackgroundStatus\Models\BgsBackground;
use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\Storage\Models\StorageFile;

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
        if (Schema::hasTable('bgs_backgrounds')) {
            $collection = BgsCollection::query()->where('title', 'System')
                ->first();

            if (!$collection) {
                return;
            }

            $backgrounds = BgsBackground::query()->where('collection_id', $collection->getKey());
            $fileIds = $backgrounds->pluck('image_file_id');

            $backgrounds->delete();
            $collection->delete();
            
            StorageFile::query()->whereIn('origin_id', $fileIds)->delete();
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
    }
};
