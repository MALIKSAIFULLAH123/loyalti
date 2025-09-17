<?php

namespace MetaFox\User\Http\Resources\v1\InactiveProcess\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;
use MetaFox\User\Models\InactiveProcess;

/**
 * Class DataGrid.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'user';
    protected string $resourceName = 'inactive-process';

    protected function initialize(): void
    {
        $this->setRowsPerPage(20, [20, 50, 100]);

        $this->addColumn('user.display_name')
            ->header(__p('user::phrase.user'))
            ->minWidth(300)
            ->linkTo('user.url')
            ->target('_blank')
            ->truncateLines()
            ->flex();

        $this->addColumn('created_at')
            ->header(__p('core::phrase.created_at'))
            ->asDateTime()
            ->width(200);

        $this->addColumn('process_text')
            ->header(__p('user::phrase.process'))
            ->width(200);

        $this->addColumn('status_text')
            ->header(__p('core::phrase.status'))
            ->width(200);

        /**
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('process')
                ->asPatch()
                ->apiUrl(apiUrl('admin.user.inactive-process.process', ['id' => ':id']));
            $actions->add('stop')
                ->asPatch()
                ->apiUrl(apiUrl('admin.user.inactive-process.stop', ['id' => ':id']));
            $actions->add('resend')
                ->asPatch()
                ->apiUrl(apiUrl('admin.user.inactive-process.resend', ['id' => ':id']));
        });

        /**
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            // $menu->asButton();
            // $menu->withDelete();
        });

        /**
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('process')
                ->action('process')
                ->showWhen(['truthy', 'item.extra.can_process'])
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->showWhen(['includes', 'item.status', [InactiveProcess::NOT_STARTED_STATUS, InactiveProcess::STOPPED_STATUS]])
                ->label(__p('user::phrase.start_process'))
                ->reload();

            $menu->addItem('stop')
                ->action('stop')
                ->showWhen(['truthy', 'item.extra.can_stop'])
                ->showWhen(['includes', 'item.status', [InactiveProcess::SENDING_STATUS, InactiveProcess::PENDING_STATUS]])
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('user::phrase.stop_process'))
                ->reload();

            $menu->addItem('resend')
                ->action('resend')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->showWhen(['eq', 'item.status', InactiveProcess::COMPLETED_STATUS])
                ->label(__p('user::phrase.resend_process'))
                ->reload();
        });

    }
}
