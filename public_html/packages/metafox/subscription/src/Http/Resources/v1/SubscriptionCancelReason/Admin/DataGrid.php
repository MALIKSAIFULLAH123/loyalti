<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\Admin;

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * @driverType data-grid
 * @driverName subscription.cancel_reason
 */
class DataGrid extends Grid
{
    protected string $appName      = 'subscription';
    protected string $resourceName = 'cancel-reason';

    protected function initialize(): void
    {
        $this->sortable();

        $this->dynamicRowHeight();

        $this->addColumn('title')
            ->header(__p('subscription::admin.reason'))
            ->width(500)
            ->flex(2);

        $this->addColumn('statistic.total_canceled')
            ->header(__p('subscription::admin.number_of_subscriptions'))
            ->width(200)
            ->asNumber()
            ->flex(1);

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'order', 'toggleActive']);

            $actions->add('getDeleteForm')
                ->apiUrl(apiUrl('admin.subscription.cancel-reason.delete', ['cancel_reason' => ':id']));

            $actions->add('orderItem')
                ->apiUrl('/admincp/subscription/cancel-reason/order')
                ->apiMethod(MetaFoxForm::METHOD_POST);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->addItem('deleteItem')
                ->icon('ico-trash-o')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('core::phrase.delete'))
                ->params(['action' => 'getDeleteForm', 'reload' => true])
                ->showWhen([
                    'falsy',
                    'item.is_default',
                ]);
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('subscription::admin.create_new_reason'))
                ->removeAttribute('value')
                ->to('subscription/cancel-reason/create');
        });
    }
}
