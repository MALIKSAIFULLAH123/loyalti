<?php

namespace MetaFox\Video\Http\Resources\v1\VerifyProcess\Admin;

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
use MetaFox\Video\Support\VideoSupport;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'video';
    protected string $resourceName = 'verify-process';

    protected function initialize(): void
    {
        $this->setRowsPerPage(20, [20, 50, 100]);

        $this->addColumn('process_text')
            ->header(__p('user::phrase.process'))
            ->flex();

        $this->addColumn('status_text')
            ->asColoredText()
            ->header(__p('core::phrase.status'))
            ->flex();

        $this->addColumn('created_at')
            ->header(__p('core::phrase.created_at'))
            ->asDateTime()
            ->flex();

        /**
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('process')
                ->asPatch()
                ->apiUrl('admincp/video/verify-process/process/:id');

            $actions->add('stop')
                ->asPatch()
                ->apiUrl('admincp/video/verify-process/stop/:id');
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
                ->showWhen(['includes', 'item.status', [VideoSupport::STOPPED_VERIFY_STATUS]])
                ->label(__p('user::phrase.start_process'))
                ->reload();

            $menu->addItem('stop')
                ->action('stop')
                ->showWhen(['truthy', 'item.extra.can_stop'])
                ->showWhen(['includes', 'item.status', [VideoSupport::PROCESSING_VERIFY_STATUS, VideoSupport::PENDING_VERIFY_STATUS]])
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('user::phrase.stop_process'))
                ->reload();
        });

    }
}
