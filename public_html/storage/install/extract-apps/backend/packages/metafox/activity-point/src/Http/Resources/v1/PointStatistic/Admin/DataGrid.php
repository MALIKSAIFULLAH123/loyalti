<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointStatistic\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\ActivityPoint\Support\ActivityPoint;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 * @driverName  activitypoint.statistic
 * @driverType  data-grid
 */
class DataGrid extends Grid
{
    protected string $appName      = 'activitypoint';
    protected string $resourceName = 'statistic';

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchPointStatisticForm());

        $this->dynamicRowHeight();

        $this->enableCheckboxSelection();

        $this->addColumn('name')
            ->header(__p('core::phrase.username'))
            ->sortable()
            ->truncateLines()
            ->sortableField('full_name')
            ->linkTo('link')
            ->target('_blank')
            ->minWidth(250)
            ->flex();

        $this->addColumn('current_points')
            ->asNumber()
            ->header(__p('activitypoint::phrase.type_all_label'))
            ->sortable()
            ->sortableField('current_points')
            ->width(200);

        $this->addColumn('total_earned')
            ->asNumber()
            ->header(__p('activitypoint::phrase.type_earned_label'))
            ->width(150);

        $this->addColumn('total_received')
            ->asNumber()
            ->header(__p('activitypoint::phrase.type_received_label'))
            ->width(150);

        $this->addColumn('total_bought')
            ->asNumber()
            ->header(__p('activitypoint::phrase.type_bought_label'))
            ->width(150);

        $this->addColumn('total_spent')
            ->asNumber()
            ->header(__p('activitypoint::phrase.type_spent_label'))
            ->width(150);

        $this->addColumn('total_sent')
            ->asNumber()
            ->header(__p('activitypoint::phrase.type_sent_label'))
            ->width(150);

        $this->addColumn('total_retrieved')
            ->asNumber()
            ->header(__p('activitypoint::phrase.type_retrieved_label'))
            ->width(150);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('adjustPoints')
                ->asGet()
                ->apiUrl('admincp/core/form/activitypoint_statistic.adjust/:id?type=' . ActivityPoint::TYPE_RECEIVED);

            $actions->add('massAdjustPoints')
                ->asGet()
                ->apiUrl('admincp/core/form/activitypoint_statistic.mass_adjust?user_ids=[:id]&type=' . ActivityPoint::TYPE_RECEIVED);
        });

        /*
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            $menu->asButton();

            $menu->addItem('massAdjustPoints')
                ->action('massAdjustPoints')
                ->reload()
                ->icon('ico-plus-circle-o')
                ->label('Adjust Points')
                ->asBatchEdit();
        });
        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('adjustPoints')
                ->icon('ico-plus-circle-o')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('activitypoint::phrase.adjust_point'))
                ->params(['action' => 'adjustPoints']);

            $menu->addItem('viewTransaction')
                ->icon('ico-plus-circle-o')
                ->value(MetaFoxForm::FORM_ACTION_REDIRECT_TO)
                ->label(__p('activitypoint::phrase.view_transaction'))
                ->params([
                    'url' => '/admincp/activitypoint/transaction/browse?user_id=:id',
                ]);
        });
    }
}
