<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Storage\Models\Asset;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\Storage\Repositories\AssetRepositoryInterface;

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

        $names = [
            'rlt_single',
            'rlt_divorced',
            'rlt_complicated',
            'rlt_open',
            'rlt_married',
            'rlt_widow',
            'rlt_relationship',
            'rlt_separated',
            'rlt_engage',
        ];

        resolve(AssetRepositoryInterface::class)
            ->getModel()
            ->newModelQuery()
            ->whereIn('name', $names)
            ->where('package_id', 'metafox/user')
            ->get()
            ->collect()
            ->each(function ($asset) {
                if (!$asset instanceof Asset) {
                    return true;
                }

                $file = $asset->file()->first();

                if ($file instanceof StorageFile) {
                    $file->delete();
                }

                $asset->delete();
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
