<?php

namespace MetaFox\EMoney\Http\Resources\v1\WithdrawRequest\Admin;

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

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'emoney';
    protected string $resourceName = 'request';

    protected function initialize(): void
    {
        $this->addColumn('user.display_name')
            ->header(__p('ewallet::admin.creator'))
            ->linkTo('user.url')
            ->flex()
            ->truncateLines();

        $this->addColumn('total')
            ->header(__p('ewallet::web.gross'))
            ->width(200);

        $this->addColumn('fee')
            ->header(__p('ewallet::web.fee'))
            ->width(200);

        $this->addColumn('amount')
            ->header(__p('ewallet::web.net'))
            ->width(200);

        $this->addColumn('withdraw_method')
            ->header(__p('ewallet::phrase.method'))
            ->width(200);

        $this->addColumn('status')
            ->header(__p('core::phrase.status'))
            ->width(200);

        $this->addColumn('creation_date')
            ->asDateTime()
            ->header(__p('ewallet::phrase.creation_date'))
            ->width(200);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('approveItem')
                ->asPatch()
                ->apiUrl('admincp/emoney/request/approve/:id');

            $actions->add('paymentItem')
                ->asPatch()
                ->apiUrl('admincp/emoney/request/payment/:id');

            $actions->add('getDeniedForm')
                ->apiUrl('admincp/core/form/ewallet.request.deny/:id');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('approve')
                ->action('approveItem')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('core::phrase.approve'))
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_approve'],
                ])
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('ewallet::admin.are_you_sure_you_want_to_approve_this_request'),
                ]);

            $menu->addItem('deny')
                ->action('getDeniedForm')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('ewallet::admin.deny'))
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_deny'],
                ])
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('ewallet::admin.are_you_sure_you_want_to_deny_this_request'),
                ])
                ->reload();

            $menu->addItem('viewDeniedReason')
                ->value(MetaFoxForm::ACTION_SHOW_INFO)
                ->label(__p('ewallet::admin.view_reason'))
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_view_reason'],
                ])
                ->params([
                    'info' => [
                        'label' => __p('ewallet::phrase.reason'),
                        'field' => 'reason',
                    ],
                ]);

            $menu->addItem('payment')
                ->action('paymentItem')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('core::web.pay'))
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_payment'],
                ]);
        });
    }
}
