<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Core\Constants;
use MetaFox\Core\Models\Driver;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('core_drivers')) {
            return;
        }

        Driver::query()
            ->whereIn('type', [Constants::DRIVER_TYPE_RESOURCE_WEB, Constants::DRIVER_TYPE_RESOURCE_ACTIONS])
            ->where(['package_id' => 'metafox/attachment'])
            ->where(['name' => 'attachment'])
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
