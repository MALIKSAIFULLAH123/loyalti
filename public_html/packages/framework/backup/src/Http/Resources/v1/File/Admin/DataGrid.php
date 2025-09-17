<?php

namespace MetaFox\Backup\Http\Resources\v1\File\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Html\BuiltinAdminSearchForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'backup';
    protected string $resourceName = 'file';

    protected function initialize(): void
    {
        $this->setDataSource(apiUrl('admin.backup.file.index'), ['q' => ':q']);

        $this->setSearchForm(new BuiltinAdminSearchForm());
        $this->inlineSearch(['id', 'filename']);

        $this->addColumn('filename')
            ->header(__p('backup::phrase.filename'))
            ->flex();

        $this->addColumn('filesize')
            ->header(__p('backup::phrase.filesize'))
            ->asNumeral('0.0 b')
            ->width(200);

        $this->addColumn('created_at')
            ->header(__p('backup::phrase.created_at'))
            ->asDateTime();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['destroy']);
            $actions->add('download')
                ->downloadUrl(downloadUrl('admin.backup.file.download', ['file' => ':id']));
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('download')
                ->label(__p('backup::phrase.download'))
                ->asDownload()
                ->params(['action' => 'download']);
            $menu->withDelete();
        });
    }
}
