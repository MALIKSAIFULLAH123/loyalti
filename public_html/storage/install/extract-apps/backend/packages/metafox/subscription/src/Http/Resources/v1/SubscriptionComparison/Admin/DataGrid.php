<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionComparison\Admin;

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage;
use MetaFox\Subscription\Support\Helper;

class DataGrid extends Grid
{
    protected string $appName      = 'subscription';
    protected string $resourceName = 'comparison';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();
        $this->sortable();

        $context = user();

        $packages = SubscriptionPackage::getPackages($context, [
            'view' => Helper::VIEW_ADMINCP,
        ]);

        if (null !== $packages) {
            $this->addColumn('title')
                ->header(__p('core::phrase.feature'))
                ->flex();

            foreach ($packages as $package) {
                $this->addColumn('packages.' . $package->entityId() . '.value')
                    ->header($package->toTitle())
                    ->asIconStatus([
                        'yes' => [
                            'icon'    => 'ico-check-circle',
                            'color'   => 'success.main',
                            'spinner' => false,
                            'hidden'  => false,
                            'label'   => __p('core::phrase.yes'),
                        ],
                        'no'  => [
                            'icon'    => 'ico-minus',
                            'color'   => 'text.hint',
                            'spinner' => false,
                            'hidden'  => false,
                            'label'   => __p('core::phrase.no'),
                        ],
                    ])
                    ->flex();
            }
        }

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'destroy', 'delete']);
            $actions->add('orderItem')
                ->asPost()
                ->apiUrl(apiUrl('admin.subscription.comparison.order'));

        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->withDelete()
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('subscription::admin.are_you_sure_you_want_to_delete_this_comparison_feature_permanently'),
                ]);
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('subscription::admin.create_comparison_feature'))
                ->removeAttribute('value')
                ->to('subscription/comparison/create');
        });
    }
}
