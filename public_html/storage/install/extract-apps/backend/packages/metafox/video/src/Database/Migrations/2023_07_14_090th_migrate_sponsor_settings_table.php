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
        $this->removeDeprecatedSettings();
        $this->updateSettings();
    }

    protected function updateSettings()
    {
        \MetaFox\Menu\Models\MenuItem::query()
            ->where('module_id', 'video')
            ->whereIn('name', ['sponsor', 'purchase_sponsor', 'unsponsor'])
            ->update(['ordering' => 4]);

        \MetaFox\Menu\Models\MenuItem::query()
            ->where('module_id', 'video')
            ->whereIn('name', ['unsponsor_in_feed', 'purchase_sponsor_in_feed', 'sponsor_in_feed'])
            ->update(['ordering' => 3]);
    }

    protected function removeDeprecatedSettings(): void
    {
        $table = config('permission.table_names.permissions');

        if (!$table || !Schema::hasTable($table)) {
            return;
        }

        app('events')->dispatch('authorization.permission.delete', ['video', 'purchase_sponsor', \MetaFox\Video\Models\Video::ENTITY_TYPE]);
        app('events')->dispatch('authorization.permission.delete', ['video', 'purchase_sponsor_price', \MetaFox\Video\Models\Video::ENTITY_TYPE]);
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
