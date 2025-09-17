<?php

namespace MetaFox\Report\Http\Resources\v1\ReportReason\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 *
 * @codeCoverageIgnore
 * @ignore
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class DataGrid extends Grid
{
    protected string $appName      = 'report';
    protected string $resourceName = 'reason';

    protected function initialize(): void
    {
        //         $this->enableCheckboxSelection();
        $this->sortable();

        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->flex();

        $this->addColumn('is_default')
            ->header(__p('core::phrase.default'))
            ->asYesNoIcon()
            ->flex();

        $this->addColumn('creation_date')
            ->header(__p('core::phrase.created_at'))
            ->asDateTime()
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['destroy']);
            $actions->add('editItem')
                ->apiUrl('admincp/core/form/report.report_reason.update/:id');
            $actions->add('orderItem')
                ->asPost()
                ->apiUrl(apiUrl('admin.report.reason.order'));
            $actions->add('default')
                ->asPost()
                ->apiUrl(apiUrl('admin.report.reason.default', ['id' => ':id']));
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
            $menu->withEdit()
                ->params(['action' => 'editItem']);
            $menu->addItem('default')
                ->value(MetaFoxForm::ACTION_ROW_ACTIVE)
                ->params(['action' => 'default'])
                ->reload()
                ->showWhen([
                    'and',
                    ['falsy', 'item.is_default'],
                ])
                ->label(__p('core::phrase.default'));
            $menu->withDelete(
                null,
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('report::phrase.delete_confirm_report_reason'),
                ],
            )->showWhen([
                'and',
                ['falsy', 'item.is_default'],
            ]);
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('report::phrase.add_new_reasons'))
                ->removeAttribute('value')
                ->to('report/reason/create');
        });
    }
}
