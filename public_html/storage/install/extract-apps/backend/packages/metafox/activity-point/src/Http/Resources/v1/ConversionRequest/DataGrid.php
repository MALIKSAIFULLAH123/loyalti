<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\ConversionRequest;

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
 * @ignore
 */
class DataGrid extends Grid
{
    public bool     $isAdminCP = false;
    protected array $apiParams = [
        'creator'   => ':creator',
        'status'    => ':status',
        'from_date' => ':from_date',
        'to_date'   => ':to_date',
    ];

    protected array $apiRules = [
        'creator'   => ['truthy', 'creator'],
        'status'    => ['truthy', 'status'],
        'from_date' => ['truthy', 'from_date'],
        'to_date'   => ['truthy', 'to_date'],
    ];

    protected function initialize(): void
    {
        $this->setRowsPerPage(20, [20, 50, 100, 200, 500]);
        $this->setDataSource('activitypoint/conversion-request', $this->apiParams, $this->apiRules);

        $this->addColumn('points')
            ->header(__p('activitypoint::web.point_conversion_points'))
            ->width(150);

        $this->addColumn('total')
            ->header(__p('activitypoint::web.point_conversion_gross_amount'))
            ->width(180);

        $this->addColumn('fee')
            ->header(__p('activitypoint::web.point_conversion_fee'))
            ->width(180);

        $this->addColumn('actual')
            ->header(__p('activitypoint::web.point_conversion_net_amount'))
            ->width(180);

        $this->addColumn('status')
            ->header(__p('core::web.status'))
            ->width(180);

        $this->addColumn('creation_date')
            ->header(__p('activitypoint::web.point_conversion_creation_date'))
            ->asDateTime()
            ->width(180);

        /**
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('cancelItem')
                ->apiUrl('activitypoint/conversion-request/:id/cancel')
                ->asPatch();
        });

        /**
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('cancel')
                ->action('cancelItem')
                ->value(MetaFoxForm::ACTION_BATCH_ITEM)
                ->label(__p('core::phrase.cancel'))
                ->reload()
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('activitypoint::phrase.are_you_sure_you_want_to_cancel_this_request'),
                ])
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_cancel'],
                ]);

            $menu->addItem('viewDeniedReason')
                ->value(MetaFoxForm::ACTION_SHOW_INFO)
                ->label(__p('activitypoint::admin.view_reason'))
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_view_reason'],
                ])
                ->params([
                    'info' => [
                        'label' => __p('activitypoint::web.point_conversion_reason'),
                        'field' => 'reason',
                    ],
                ]);
        });
    }
}
