<?php

namespace MetaFox\BackgroundStatus\Http\Resources\v1\BgsCollection\Admin;

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
    protected string $appName      = 'bgs';
    protected string $resourceName = 'collection';

    protected function initialize(): void
    {
        $this->setDataSource(apiUrl('admin.bgs.collection.index', ['q' => ':q']));
        $this->enableCheckboxSelection();
        $this->dynamicRowHeight();

        $this->addColumn('name')
            ->header(__p('backgroundstatus::phrase.collection_name'))
            ->flex();

        $this->addColumn('avatar')
            ->setAttribute('sizePrefers', 300)
            ->setAttribute('size', 150)
            ->variant('square')
            ->renderAs('AvatarCell')
            ->header(__p('backgroundstatus::phrase.main_image'))
            ->alignCenter()
            ->flex();

        $this->addColumn('total_background')
            ->header(__p('backgroundstatus::phrase.number_of_image'))
            ->alignCenter()
            ->width(200);

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->width(200);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'destroy', 'toggleActive']);
            $actions->addEditPageUrl();

            $this->actionDelete($actions);

            $actions->add('orderItem')
                ->asPost()
                ->apiUrl('admincp/bgs/collection/order');
        });

        /*
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            $this->batchDelete($menu);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->withDelete();
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('backgroundstatus::phrase.add_collection'))
                ->removeAttribute('value')
                ->to('bgs/collection/create');
        });
    }

    protected function actionDelete(Actions $actions): void
    {
        $actions->add('batchDelete')
            ->asDelete()
            ->asFormDialog(false)
            ->apiUrl('admincp/bgs/collections?id=[:id]');
    }

    protected function batchDelete(BatchActionMenu $menu): void
    {
        $menu->addItem('batchDelete')
            ->action('batchDelete')
            ->icon('ico-trash-o')
            ->label(__p('core::phrase.delete'))
            ->reload()
            ->asBatchEdit();
    }
}
