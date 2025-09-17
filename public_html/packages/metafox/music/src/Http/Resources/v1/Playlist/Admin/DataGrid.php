<?php

namespace MetaFox\Music\Http\Resources\v1\Playlist\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'music';
    protected string $resourceName = 'playlist';

    protected function initialize(): void
    {
        $this->enableCheckboxSelection();
        $this->dynamicRowHeight();
        $this->setRowsPerPage(20, [20, 50, 100, 200, 500]);

        $this->setDataSource("/admincp/{$this->appName}/{$this->resourceName}");

        $this->addColumn('name')
            ->header(__p('core::phrase.title'))
            ->linkTo('url')
            ->target('_blank')
            ->flex();

        $this->addColumn('description')
            ->header(__p('core::phrase.description'))
            ->truncateLines(5)
            ->flex();

        $this->addColumn('user.display_name')
            ->header(__p('core::phrase.posted_by'))
            ->linkTo('user.url')
            ->target('_blank')
            ->alignCenter()
            ->width(200);

        $this->addColumn('creation_date')
            ->header(__p('core::phrase.creation_date'))
            ->asDateTime()
            ->sortable()
            ->sortableField('recent')
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $this->getDeleteActionMenu($actions);
            $this->getBatchDeleteActionMenu($actions);
        });
        /*
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            $this->getBatchDeleteActionMenu($menu);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $this->getDeleteActionMenu($menu);
        });

        $this->withExtraData([
            'show_total'        => true,
            'total_item_phrase' => 'total_value_playlists',
        ]);
    }

    protected function getBatchDeleteActionMenu(BatchActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('batchDelete')
                ->asDelete()
                ->asFormDialog(false)
                ->apiUrl("admincp/{$this->appName}/{$this->resourceName}/batch-delete?id=[:id]")
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('music::phrase.delete_confirm', ['item_type' => 'music_album']),
                ]);
        }

        if ($actionMenu instanceof BatchActionMenu) {
            $actionMenu->addItem('batchDelete')
                ->action('batchDelete')
                ->icon('ico-trash-o')
                ->label(__p('core::phrase.delete'))
                ->reload()
                ->asBatchEdit();
        }
    }

    protected function getDeleteActionMenu(ItemActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('deleteItem')
                ->asDelete()
                ->apiUrl("{$this->appName}/{$this->resourceName}/:id");
        }

        if ($actionMenu instanceof ItemActionMenu) {
            $actionMenu->withDelete()
                ->params(['action' => 'deleteItem'])
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('music::phrase.delete_confirm', ['item_type' => 'music_playlist']),
                ]);
        }
    }
}
