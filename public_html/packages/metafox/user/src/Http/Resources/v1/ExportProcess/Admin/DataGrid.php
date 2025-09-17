<?php

namespace MetaFox\User\Http\Resources\v1\ExportProcess\Admin;

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
    protected string $appName      = 'user';
    protected string $resourceName = 'export-process';

    protected function initialize(): void
    {
        $this->enableCheckboxSelection();
        $this->setRowsPerPage(20, [20, 50, 100, 200, 500]);
        $this->setDataSource(sprintf('/admincp/%s/%s', $this->appName, $this->resourceName), [
            'q'          => ':q',
            'process_id' => ':process_id',
            'status'     => ':status',
            'sort'       => ':sort',
            'sort_type'  => ':sort_type',
        ]);

        $this->addColumn('filename')
            ->header(__p('log::file.filename'))
            ->flex();

        $this->addColumn('user.display_name')
            ->header(__p('user::phrase.user'))
            ->flex()
            ->linkTo('user.url')
            ->target('_blank');

        $this->addColumn('status')
            ->header(__p('core::phrase.status'))
            ->flex();

        $this->addColumn('total_user')
            ->header(__p('user::phrase.total_users'))
            ->asNumber()
            ->flex();

        $this->addColumn('creation_date')
            ->header(__p('core::phrase.creation_date'))
            ->sortable()
            ->sortableField('created_at')
            ->asDateTime()
            ->flex();

        /**
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $this->getDownloadActionMenu($actions);
            $this->getDeleteActionMenu($actions);
            $this->getBatchDeleteActionMenu($actions);
        });

        /**
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            $this->getBatchDeleteActionMenu($menu);
        });

        /**
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $this->getDownloadActionMenu($menu);
            $this->getDeleteActionMenu($menu);
        });

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
                    'message' => __p('core::phrase.delete_confirm'),
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
                ->apiUrl("admincp/{$this->appName}/{$this->resourceName}/:id");
        }

        if ($actionMenu instanceof ItemActionMenu) {
            $actionMenu->withDelete()
                ->params(['action' => 'deleteItem'])
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('core::phrase.delete_confirm'),
                ]);
        }
    }

    protected function getDownloadActionMenu(ItemActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('download')
                ->downloadUrl(downloadUrl("admin.{$this->appName}.{$this->resourceName}.download", ['id' => ':id']));
        }

        if ($actionMenu instanceof ItemActionMenu) {
            $actionMenu->addItem('download')
                ->label(__p('backup::phrase.download'))
                ->asDownload()
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_download'],
                ])
                ->params(['action' => 'download']);;
        }
    }
}
