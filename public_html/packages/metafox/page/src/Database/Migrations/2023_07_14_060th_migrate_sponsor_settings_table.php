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
        $this->updateMenuItemLabel();
    }

    protected function updateMenuItemLabel(): void
    {
        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'module_id' => 'page',
                'name'      => 'sponsor',
            ])
            ->whereIn('resolution', [\MetaFox\Platform\MetaFoxConstant::RESOLUTION_WEB, \MetaFox\Platform\MetaFoxConstant::RESOLUTION_MOBILE])
            ->update([
                'label' => 'page::phrase.sponsor_this_item',
            ]);

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'module_id' => 'page',
            ])
            ->whereIn('resolution', [\MetaFox\Platform\MetaFoxConstant::RESOLUTION_WEB, \MetaFox\Platform\MetaFoxConstant::RESOLUTION_MOBILE])
            ->whereIn('name', ['unsponsor', 'unsponsor_in_feed', 'sponsor_in_feed'])
            ->delete();

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'module_id' => 'page',
                'name'      => 'sponsor',
            ])
            ->whereIn('resolution', [\MetaFox\Platform\MetaFoxConstant::RESOLUTION_WEB, \MetaFox\Platform\MetaFoxConstant::RESOLUTION_MOBILE])
            ->whereIn('menu', ['page.page.detailActionMenu', 'page.page.itemActionMenu'])
            ->update([
                'ordering' => 4,
            ]);

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'module_id' => 'page',
                'name'      => 'sponsor',
            ])
            ->where('resolution', \MetaFox\Platform\MetaFoxConstant::RESOLUTION_WEB)
            ->where('menu', 'page.page.profileActionMenu')
            ->update([
                'ordering' => 4,
            ]);
    }

    protected function removeDeprecatedSettings(): void
    {
        $table = config('permission.table_names.permissions');

        if (!$table || !Schema::hasTable($table)) {
            return;
        }

        app('events')->dispatch('authorization.permission.delete', ['page', 'purchase_sponsor', \MetaFox\Page\Models\Page::ENTITY_TYPE]);
        app('events')->dispatch('authorization.permission.delete', ['page', 'purchase_sponsor_price', \MetaFox\Page\Models\Page::ENTITY_TYPE]);
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
