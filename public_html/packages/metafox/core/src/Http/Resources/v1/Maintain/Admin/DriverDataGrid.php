<?php

namespace MetaFox\Core\Http\Resources\v1\Maintain\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\GridConfig as Grid;

/**
 * Class DriverDataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DriverDataGrid extends Grid
{
    protected function initialize(): void
    {
        $this->setSearchForm(new SearchDriverForm());

        $this->setDataSource('admincp/core/maintain/drivers', ['q' => ':q']);

        $this->addColumn('id')
            ->header(__p('core::phrase.id'))
            ->width(80);

        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->width(300);

        $this->addColumn('type_label')
            ->header(__p('core::phrase.type'))
            ->alignCenter()
            ->width(150);

        $this->addColumn('package_name')
            ->header(__p('core::phrase.package_name'))
            ->alignCenter()
            ->width(250);

        $this->addColumn('version')
            ->header(__p('core::phrase.version'))
            ->alignCenter()
            ->width(150);

        $this->addColumn('driver')
            ->header(__p('core::phrase.driver_class'))
            ->width(500);

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asYesNoIcon()
            ->width(100);
    }
}
