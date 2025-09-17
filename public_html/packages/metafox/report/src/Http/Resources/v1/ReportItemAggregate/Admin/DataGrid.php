<?php

namespace MetaFox\Report\Http\Resources\v1\ReportItemAggregate\Admin;

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'report';
    protected string $resourceName = 'items';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->setSearchForm(new SearchReportItemAggregateForm());

        $this->addColumn('item_title')
            ->header(__p('core::phrase.content_label'))
            ->linkTo('item_url')
            ->target('_blank')
            ->flex(3);

        $this->addColumn('item_type_label')
            ->header(__p('core::phrase.item_type'))
            ->flex();

        $this->addColumn('last_user_name')
            ->header(__p('report::phrase.last_report_by'))
            ->linkTo('last_user_url')
            ->target('_blank')
            ->flex();

        $this->addColumn('total_reports')
            ->header(__p('report::phrase.total_reports'))
            ->linkTo('report_detail_url')
            ->alignCenter()
            ->flex();

        $this->addColumn('created_at')
            ->header(__p('core::phrase.date'))
            ->asDateTime()
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['process', 'ignore', 'edit']);
        });

        /*
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            // $menu->asButton();
            // $menu->withDelete();
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('process')
                ->icon('ico-check-circle-alt')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('report::phrase.mark_as_processed'))
                ->action('process')
                ->reload()
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('report::phrase.are_you_sure_process_this'),
                ]);
            $menu->addItem('ignore')
                ->icon('ico-trash-o')
                ->value(MetaFoxForm::ACTION_ROW_DELETE)
                ->label(__p('core::phrase.ignore'))
                ->action('ignore')
                ->confirm(true);
        });
    }
}
