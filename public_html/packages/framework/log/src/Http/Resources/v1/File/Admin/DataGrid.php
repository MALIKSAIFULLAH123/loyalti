<?php

namespace MetaFox\Log\Http\Resources\v1\File\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Html\BuiltinAdminSearchForm;
use MetaFox\Platform\Resource\GridConfig as Grid;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'log';
    protected string $resourceName = 'file';

    protected function initialize(): void
    {
        $this->inlineSearch(['filename', 'id']);
        $this->setSearchForm(new BuiltinAdminSearchForm());
        $this->rowHeight(56);

        $this->setDefaultDataSource();

        $this->addColumn('filename')
            ->header(__p('log::file.filename'))
            ->linkTo('links.pageUrl')
            ->flex();

        $this->addColumn('filesize')
            ->header(__p('log::file.filesize'))
            ->asNumeral('0 b')
            ->width(150);

        $this->addColumn('modified_at')
            ->header(__p('log::file.modified_at'))
            ->asDateTime()
            ->width(200);
    }
}
