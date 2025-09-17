<?php

namespace MetaFox\User\Http\Resources\v1\CancelReason\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'user';
    protected string $resourceName = 'cancel-reason';

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchCancelReasonForm());
        $this->sortable();

        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->flex();
        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'toggleActive', 'destroy']);
            $actions->add('orderItem')
                ->asPost()
                ->apiUrl('admincp/user/cancel-reason/order');
        });

        /*
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            // $menu->asButton();
            // $menu->withDelete();
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('user::phrase.add_new_reason'))
                ->removeAttribute('value')
                ->to('user/cancel-reason/create');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->withDelete();
        });
    }
}
