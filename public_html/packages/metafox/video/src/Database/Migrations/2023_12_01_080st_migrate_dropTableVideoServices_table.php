<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Core\Repositories\DriverRepositoryInterface;

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
        resolve(DriverRepositoryInterface::class)
            ->getModel()
            ->newModelQuery()
            ->where('package_id', '=', 'metafox/video')
            ->where('name', '=', 'video_service')
            ->delete();
        // to do here video_service
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasTable('video_services')) {
            Schema::dropIfExists('video_services');
        }
    }
};
