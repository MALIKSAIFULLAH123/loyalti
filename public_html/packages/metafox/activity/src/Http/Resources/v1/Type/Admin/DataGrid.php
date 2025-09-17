<?php

namespace MetaFox\Activity\Http\Resources\v1\Type\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Activity\Models\Type;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;
use Illuminate\Support\Str;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'feed';
    protected string $resourceName = 'type';

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchTypeForm());

        $this->dynamicRowHeight();
        $this->setRowsPerPage(20, [20, 50, 100]);

        $this->addColumn('type')
            ->header(__p('activity::admin.type'))
            ->width(350);

        $this->addColumn('title')
            ->header(__p('activity::phrase.title'))
            ->flex();

        $this->addColumn('package.title')
            ->header(__p('core::phrase.package_name'))
            ->width(150);

        $this->addColumn('is_active')
            ->header(__p('activity::admin.enabled'))
            ->asToggleActive()
            ->minWidth(150);

        $this->addColumn('can_create_feed')
            ->header(__p('activity::phrase.activity_type_can_create_feed'))
            ->asToggle('toggleCreateFeed')
            ->minWidth(150);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'toggleActive']);
            $actions->add('toggleCreateFeed')->apiUrl(apiUrl(
                'admin.feed.type.ability.toggleActive',
                ['id' => ':id', 'ability' => Str::camel(Type::CAN_CREATE_FEED_TYPE)]
            ));
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
        });
    }
}
