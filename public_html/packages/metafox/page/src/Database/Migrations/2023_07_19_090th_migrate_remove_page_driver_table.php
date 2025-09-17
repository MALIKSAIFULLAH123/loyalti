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
        if (!Schema::hasTable('core_drivers')) {
            return;
        }

        \MetaFox\Core\Models\Driver::query()
            ->where([
                'module_id'  => 'page',
                'type'       => \MetaFox\Core\Constants::DRIVER_TYPE_FORM,
                'resolution' => 'web',
            ])
            ->whereIn('name', ['page.about', 'page.info', 'page.permission'])
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
