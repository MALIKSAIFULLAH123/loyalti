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
                'module_id'  => 'group',
                'type'       => \MetaFox\Core\Constants::DRIVER_TYPE_FORM,
                'resolution' => 'web',
            ])
            ->whereIn('name', ['group.info', 'group.about', 'group.permission'])
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
