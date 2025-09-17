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
        if (!Schema::hasTable(config('permission.table_names.permissions'))) {
            return;
        }

        app('events')->dispatch('authorization.permission.delete', ['advertise', 'approve', \MetaFox\Advertise\Models\Advertise::ENTITY_TYPE]);
        app('events')->dispatch('authorization.permission.delete', ['advertise', 'moderate', \MetaFox\Advertise\Models\Advertise::ENTITY_TYPE]);
        app('events')->dispatch('authorization.permission.delete', ['advertise', 'approve', \MetaFox\Advertise\Models\Sponsor::ENTITY_TYPE]);
        app('events')->dispatch('authorization.permission.delete', ['advertise', 'moderate', \MetaFox\Advertise\Models\Sponsor::ENTITY_TYPE]);
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
