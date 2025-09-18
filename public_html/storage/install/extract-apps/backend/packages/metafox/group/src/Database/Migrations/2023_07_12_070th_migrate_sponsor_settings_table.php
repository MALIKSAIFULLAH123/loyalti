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
        $this->removeDeprecatedSponsorSettings();
        $this->updateSponsorMenuLabel();
    }

    protected function removeDeprecatedSponsorSettings(): void
    {
        $table = config('permission.table_names.permissions');

        if (!$table || !Schema::hasTable($table)) {
            return;
        }

        app('events')->dispatch('authorization.permission.delete', ['group', 'purchase_sponsor', \MetaFox\Group\Models\Group::ENTITY_TYPE]);
        app('events')->dispatch('authorization.permission.delete', ['group', 'purchase_sponsor_price', \MetaFox\Group\Models\Group::ENTITY_TYPE]);
    }

    protected function updateSponsorMenuLabel(): void
    {
        $repository = resolve(\MetaFox\Menu\Repositories\MenuItemRepositoryInterface::class);

        $wheres = [
            [
                'menu' => 'group.group.itemActionMenu',
                'resolution' => 'web',
                'ordering' => 11,
            ],
            [
                'menu' => 'group.group.itemActionMenu',
                'resolution' => 'mobile',
                'ordering' => 11,
            ],
            [
                'menu' => 'group.group.profileActionMenu',
                'resolution' => 'web',
                'ordering'   => 10,
            ],
            [
                'menu' => 'group.group.profileActionMenu',
                'resolution' => 'mobile',
                'ordering'   => 13,
            ],
            [
                'menu' => 'group.group.detailActionMenu',
                'resolution' => 'mobile',
                'ordering'   => 11,
            ],
        ];

        $label = 'group::phrase.sponsor_this_item';

        foreach ($wheres as $where) {
            $item = $repository->getMenuItemByName(\Illuminate\Support\Arr::get($where, 'menu'), 'sponsor', \Illuminate\Support\Arr::get($where, 'resolution'), '');

            if (!$item) {
                return;
            }

            $repository->updateMenuItem($item->entityId(), [
                'label' => $label,
                'ordering' => \Illuminate\Support\Arr::get($where, 'ordering'),
            ]);
        }
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
