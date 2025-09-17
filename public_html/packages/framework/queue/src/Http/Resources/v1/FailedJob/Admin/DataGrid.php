<?php

namespace MetaFox\Queue\Http\Resources\v1\FailedJob\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
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
    protected string $appName      = 'queue';
    protected string $resourceName = 'failed_job';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->addColumn('id')
            ->header('ID')
            ->width(80);

        $this->addColumn('uuid')
            ->header(__p('queue::failed_job.uuid'))
            ->width(300);

        $this->addColumn('failed_at')
            ->header(__p('queue::failed_job.failed_at'))
            ->asDateTime()
            ->width(200);

        $this->addColumn('queue')
            ->header(__p('queue::failed_job.queue'))
            ->width(200);

        $this->addColumn('connection')
            ->header(__p('queue::failed_job.connection'))
            ->width(200);

        $this->addColumn('failed_at')
            ->header(__p('queue::failed_job.failed_at'))
            ->width(200);

        $this->addColumn('exception')
            ->header(__p('queue::phrase.exception'))
            ->truncateLines()
            ->flex(1);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['retry', 'delete', 'destroy']);

            $actions->add('retry')
                ->apiUrl(apiUrl('admin.queue.failed_job.retry', ['failed_job' => ':id']))
                ->asPost()
                ->asFormDialog(false);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('retry')
                ->icon('ico-pencil-o')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('queue::failed_job.retry'))
                ->params(['action' => 'retry']);

            $menu->withDelete();
        });
    }
}
