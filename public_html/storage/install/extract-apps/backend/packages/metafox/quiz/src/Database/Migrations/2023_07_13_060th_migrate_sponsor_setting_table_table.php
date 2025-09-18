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
        \MetaFox\Menu\Models\MenuItem::query()
            ->where('module_id', 'quiz')
            ->whereIn('name', ['unsponsor', 'unsponsor_in_feed'])
            ->delete();
        // to do here
        $table = config('permission.table_names.permissions');

        if (!$table || !Schema::hasTable($table)) {
            return;
        }

        app('events')->dispatch('authorization.permission.delete', ['quiz', 'purchase_sponsor', \MetaFox\Quiz\Models\Quiz::ENTITY_TYPE]);
        app('events')->dispatch('authorization.permission.delete', ['quiz', 'purchase_sponsor_price', \MetaFox\Quiz\Models\Quiz::ENTITY_TYPE]);
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
