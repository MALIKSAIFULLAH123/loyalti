<?php

namespace MetaFox\Importer\Http\Resources\v1\Bundle\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

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
    protected string $appName      = 'importer';
    protected string $resourceName = 'bundle';

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchBundleForm());

        $this->setDataSource(apiUrl('admin.importer.bundle.index'), [
            'status' => ':status',
        ]);

        $this->addColumn('filename')
            ->header(__p('importer::phrase.filename'))
            ->linkTo('links.entry')
            ->flex();

        $this->addColumn('total_entry')
            ->header(__p('importer::phrase.total_entry'))
            ->asNumber()
            ->sortable()
            ->width(100);

        $this->addColumn('priority')
            ->header(__p('importer::phrase.priority'))
            ->sortable()
            ->width(100);

        $this->addColumn('status')
            ->header(__p('importer::phrase.status'))
            ->width(100);

        $this->addColumn('start_time')
            ->header(__p('importer::phrase.start_time'))
            ->asDateTime();

        $this->addColumn('end_time')
            ->header(__p('importer::phrase.end_time'))
            ->asDateTime();

        $this->addColumn('created_at')
            ->header(__p('importer::phrase.created_at'))
            ->asDateTime();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('retry')
                ->apiUrl(apiUrl('admin.importer.bundle.retry', ['bundle' => ':id']))
                ->asGet();
        });

        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('retry')
                ->label(__p('importer::phrase.retry'))
                ->value('row/request')
                ->action('retry');
        });
    }
}
