<?php

namespace MetaFox\Payment\Http\Resources\v1\Transaction\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\GridConfig as Grid;

/**
 * Class DetailDataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DetailDataGrid extends Grid
{
    protected function initialize(): void
    {
        $this->isHidden();
    }
}
