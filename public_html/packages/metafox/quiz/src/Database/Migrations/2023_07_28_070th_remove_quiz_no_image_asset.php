<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Storage\Models\Asset;
use MetaFox\Storage\Models\StorageFile;
use Illuminate\Support\Arr;

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
        if (!Schema::hasTable('storage_assets')) {
            return;
        }

        if (!Schema::hasTable('storage_files')) {
            return;
        }

        $assets = Asset::query()
            ->where([
                'module_id' => 'quiz',
                'name'      => 'no_image',
            ])
            ->get(['id', 'file_id'])
            ->toArray();

        StorageFile::query()
            ->whereIn('id', Arr::pluck($assets, 'file_id'))
            ->delete();

        Asset::query()
            ->whereIn('id', Arr::pluck($assets, 'id'))
            ->delete();
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
