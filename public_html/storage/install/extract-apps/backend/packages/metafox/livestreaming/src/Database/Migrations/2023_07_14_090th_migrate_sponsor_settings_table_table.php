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
        $table = config('permission.table_names.permissions');

        if (!$table || !Schema::hasTable($table)) {
            return;
        }

        app('events')->dispatch('authorization.permission.delete', ['livestreaming', 'purchase_sponsor', \MetaFox\LiveStreaming\Models\LiveVideo::ENTITY_TYPE]);
        app('events')->dispatch('authorization.permission.delete', ['livestreaming', 'purchase_sponsor_price', \MetaFox\LiveStreaming\Models\LiveVideo::ENTITY_TYPE]);
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
