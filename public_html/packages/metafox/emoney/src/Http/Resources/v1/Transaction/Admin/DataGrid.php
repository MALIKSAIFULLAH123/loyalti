<?php

namespace MetaFox\EMoney\Http\Resources\v1\Transaction\Admin;

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
    protected string $appName      = 'emoney';
    protected string $resourceName = 'transaction';

    protected function initialize(): void
    {
        $this->addColumn('creation_date')
            ->header(__p('core::phrase.creation_date'))
            ->asDateTime()
            ->width(250);

        $this->addColumn('source')
            ->header(__p('ewallet::web.source'))
            ->width(200);

        $this->addColumn('sender.display_name')
            ->header(__p('ewallet::admin.sender'))
            ->linkTo('sender.url')
            ->width(200)
            ->truncateLines();

        $this->addColumn('receiver.display_name')
            ->header(__p('ewallet::admin.receiver'))
            ->linkTo('receiver.url')
            ->width(200)
            ->truncateLines();

        $this->addColumn('type')
            ->header(__p('ewallet::web.action'))
            ->width(250);

        $this->addColumn('reference')
            ->header(__p('ewallet::web.reference'))
            ->width(200);

        $this->addColumn('status')
            ->header(__p('core::web.status'))
            ->width(200);

        $this->addColumn('gross')
            ->header(__p('ewallet::web.gross'))
            ->width(200);

        $this->addColumn('fee')
            ->header(__p('ewallet::web.fee'))
            ->width(200);

        $this->addColumn('net')
            ->header(__p('ewallet::web.net'))
            ->width(200);

        $this->addColumn('balance')
            ->header(__p('ewallet::web.currency_conversion'))
            ->width(200);
    }
}
