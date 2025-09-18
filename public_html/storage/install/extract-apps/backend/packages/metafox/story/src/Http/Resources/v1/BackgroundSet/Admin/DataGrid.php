<?php

namespace MetaFox\Story\Http\Resources\v1\BackgroundSet\Admin;

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
 *
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'story';
    protected string $resourceName = 'background-set';

    protected function initialize(): void
    {
        $this->enableCheckboxSelection();
        $this->dynamicRowHeight();

        $this->setDataSource('/admincp/story/background-set', ['q' => ':q']);

        $this->addColumn('name')
            ->header(__p('story::phrase.collection_name'))
            ->flex();

        $this->addColumn('preview')
            ->linkTo('links.editItem')
            ->variant('square')
            ->renderAs('UrlPreviewCell')
            ->setAttribute('sizePrefers', 'origin')
            ->aspectRatio('9:16')
            ->setAttribute('width', 200)
            ->setAttribute('maxWidth', 150)
            ->header(__p('story::phrase.main_image'))
            ->alignCenter();

        $this->addColumn('total_background')
            ->header(__p('story::phrase.number_of_image'))
            ->linkTo('links.editItem')
            ->alignCenter()
            ->width(200);

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->reload()
            ->width(200);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'destroy']);
            $actions->addEditPageUrl();
            $route = apiUrl(sprintf('admin.%s.%s.%s', $this->appName, $this->resourceName, 'toggleActive'), [
                'background_set' => ':id',
            ]);

            $actions->add('toggleActive')
                ->apiUrl($route)
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('story::phrase.change_active_background_collection_confirm'),
                ]);

            $this->actionDelete($actions);

            $actions->add('orderItem')
                ->asPost()
                ->apiUrl('admincp/story/background-set/order');
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
            $menu->withDelete()
                ->showWhen(['falsy', 'item.is_active']);
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->addItem('addItem')
                ->icon('ico-plus')
                ->label(__p('story::phrase.add_background_set'))
                ->disabled(false)
                ->to('story/background-set/create')
                ->params(['action' => 'addItem']);
        });
    }

    protected function actionDelete(Actions $actions): void
    {
        $actions->add('batchDelete')
            ->asDelete()
            ->asFormDialog(false)
            ->apiUrl('admincp/story/background-set?id=[:id]');
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

    public function boot(?int $parentId = null): void
    {
        $this->withActions(function (Actions $actions) {
            $actions->add('addItem')
                ->apiUrl(apiUrl('admin.story.background-set.create'));
        });
    }
}
