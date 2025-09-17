<?php

namespace MetaFox\Importer\Http\Resources\v1\Entry\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\GridConfig as Grid;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName = 'importer';
    protected string $resourceName = 'entry';

    protected function initialize(): void
    {
        $this->addColumn('id')
            ->header('ID')
            ->width(80);

        $this->addColumn('source')
            ->header(__p('importer::phrase.source'))
            ->width(200);

        $this->addColumn('ref_id')
            ->header(__p('importer::phrase.source_ref'))
            ->width(200);

        $this->addColumn('resource')
            ->header(__p('importer::phrase.metafox_resource'))
            ->width(200);

//        $this->addColumn('status')
//            ->header(__p('importer::phrase.status'))
//            ->width(200);

        $this->addColumn('last_updated')
            ->header(__p('importer::phrase.last_updated'))
            ->asDateTime()
            ->width(200);
    }
}
