<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\ConversionRequest\Admin;

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
    protected function initialize(): void
    {
        $this->setDataSource('/admincp/activitypoint/conversion-request', [
            'creator' => ':creator',
            'status' => ':status',
            'from_date' => ':from_date',
            'to_date' => ':to_date',
        ], [
            'creator' => ['truthy', 'creator'],
            'status' => ['truthy', 'status'],
            'from_date' => ['truthy', 'from_date'],
            'to_date' => ['truthy', 'to_date'],
        ]);

        $this->addColumn('user.display_name')
            ->header(__p('activitypoint::web.point_conversion_creator'))
            ->flex()
            ->linkTo('user.url')
            ->truncateLines();

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
            $actions->add('approveItem')
                ->asPatch()
                ->apiUrl('admincp/activitypoint/conversion-request/:id/approve');

            $actions->add('getDeniedForm')
                ->apiUrl('admincp/core/form/activitypoint.conversion-request.deny/:id');
        });

        /**
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('approve')
                ->action('approveItem')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('core::phrase.approve'))
                ->reload()
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_approve'],
                ])
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('activitypoint::admin.are_you_sure_you_want_to_approve_this_request'),
                ]);

            $menu->addItem('deny')
                ->action('getDeniedForm')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('activitypoint::admin.deny'))
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_deny'],
                ])
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('activitypoint::admin.are_you_sure_you_want_to_deny_this_request'),
                ])
                ->reload();

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
