<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\Admin;

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Subscription\Support\Helper;

class DataGrid extends Grid
{
    protected string $appName      = 'subscription';
    protected string $resourceName = 'package';

    protected function initialize(): void
    {
        $this->setDataSource('/admincp/subscription/package', [
            'q'                      => ':q',
            'status'                 => ':status',
            'type'                   => ':type',
            'payment_statistic'      => ':payment_statistic',
            'payment_statistic_from' => ':payment_statistic_from',
            'payment_statistic_to'   => ':payment_statistic_to',
        ], [
            'view' => [
                'includes',
                'view',
                [Helper::VIEW_ADMINCP, Browse::VIEW_SEARCH],
            ],
            'q' => [
                'truthy',
                'q',
            ],
            'status' => [
                'includes',
                'status',
                Helper::getItemStatus(),
            ],
        ]);

        $this->addColumn('id')
            ->header(__p('core::phrase.id'))
            ->width(80);

        $this->addColumn('title')
            ->header(__p('subscription::admin.package_title'))
            ->truncateLines()
            ->flex();

        $this->addColumn('price')
            ->header(__p('core::phrase.price'))
            ->width(150);

        $this->addColumn('type')
            ->header(__p('subscription::admin.package_type'))
            ->width(150);

        $this->addColumn('is_active')
            ->header(__p('subscription::admin.package_status'))
            ->asToggleActive()
            ->width(150);

        $this->addColumn('created_at')
            ->header(__p('subscription::admin.created_time'))
            ->asDateTime()
            ->width(200);

        $this->addColumn('statistic.total_success')
            ->header(__p('subscription::admin.active_users'))
            ->width(120)
            ->linkTo('link_to_active');

        $this->addColumn('statistic.total_expired')
            ->header(__p('subscription::admin.expired'))
            ->width(120)
            ->linkTo('link_to_expired');

        $this->addColumn('statistic.total_canceled')
            ->header(__p('subscription::admin.cancelled'))
            ->width(100)
            ->linkTo('link_to_cancelled');

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'delete', 'destroy', 'toggleActive']);

            $actions->add('popular')
                ->apiUrl('admincp/subscription/package/popular/:id')
                ->asFormDialog(false)
                ->apiParams([
                    'is_popular' => 1,
                ])
                ->asPatch();

            $actions->add('unpopular')
                ->apiUrl('admincp/subscription/package/popular/:id')
                ->asFormDialog(false)
                ->apiParams([
                    'is_popular' => 0,
                ])
                ->asPatch();
        });

        /*
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            $menu->asButton();
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();

            $menu->addItem('popular')
                ->icon('ico-star-o')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('subscription::admin.mark_as_most_popular'))
                ->showWhen(['falsy', 'item.is_popular'])
                ->action('popular')
                ->reload();

            $menu->addItem('unpopular')
                ->icon('ico-star-o')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('subscription::admin.un_mark_as_most_popular'))
                ->showWhen(['truthy', 'item.is_popular'])
                ->action('unpopular')
                ->reload();

            $menu->addItem('viewSubscription')
                ->label(__p('subscription::admin.view_subscription'))
                ->value(MetaFoxForm::FORM_ACTION_REDIRECT_TO)
                ->params(['url' => '/admincp/subscription/invoice/browse?package_id=:id']);

            $menu->withDelete()
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('subscription::admin.are_you_sure_you_want_to_delete_this_package_permanently'),
                ]);
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('subscription::admin.create_new_package'))
                ->removeAttribute('value')
                ->to('subscription/package/create');
        });
    }
}
