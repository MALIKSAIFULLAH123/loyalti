<?php

namespace MetaFox\Advertise\Http\Resources\v1\Advertise\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'advertise';
    protected string $resourceName = 'advertise';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->truncateLines()
            ->flex();

        $this->addColumn('placement.title')
            ->header(__p('advertise::phrase.placement'))
            ->truncateLines()
            ->flex();

        $this->addColumn('start_date')
            ->header(__p('advertise::phrase.start_date'))
            ->asDateTime()
            ->width(200);

        $this->addColumn('status')
            ->header(__p('core::web.status'))
            ->width(120);

        $this->addColumn('user.display_name')
            ->header(__p('advertise::phrase.creator'))
            ->width(150)
            ->truncateLines()
            ->linkTo('user.url')
            ->target('_blank');

        $this->addColumn('statistic.current_impressions')
            ->width(100)
            ->alignCenter()
            ->header(__p('advertise::web.impressions'))
            ->asNumber();

        $this->addColumn('statistic.current_clicks')
            ->header(__p('advertise::web.clicks'))
            ->alignCenter()
            ->width(100)
            ->asNumber();

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'delete', 'toggleActive']);

            $actions->add('deleteItem')
                ->apiUrl('admincp/advertise/advertise/:id')
                ->asDelete();

            $actions->add('approveItem')
                ->apiUrl('admincp/advertise/advertise/approve/:id')
                ->asPatch();

            $actions->add('denyItem')
                ->apiUrl('admincp/advertise/advertise/deny/:id')
                ->asPatch();

            $actions->add('paidItem')
                ->apiUrl('admincp/advertise/advertise/paid/:id')
                ->asPatch();
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit()
                ->showWhen([
                    'and',
                    ['falsy', 'item.is_completed'],
                ])
                ->reload();

            $menu->addItem('approve')
                ->action('approveItem')
                ->icon('ico-check-circle-o')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('core::phrase.approve'))
                ->showWhen([
                    'or',
                    ['truthy', 'item.is_pending'],
                    ['truthy', 'item.is_denied'],
                ])
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('advertise::phrase.are_you_sure_you_want_to_approve_this_ad'),
                ])
                ->reload();

            $menu->addItem('deny')
                ->action('denyItem')
                ->icon('ico-trash-o')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('advertise::phrase.deny'))
                ->showWhen(['truthy', 'item.is_pending'])
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('advertise::phrase.are_you_sure_you_want_to_deny_this_ad'),
                ])
                ->reload();

            $menu->addItem('paid')
                ->action('paidItem')
                ->icon('ico-credit-card-o')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('advertise::phrase.mark_as_paid'))
                ->showWhen(['truthy', 'item.extra.can_mark_as_paid'])
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('advertise::phrase.are_you_sure_you_want_to_mark_this_ad_as_paid'),
                ])
                ->reload();

            $menu->withDelete()
                ->action('deleteItem')
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('advertise::phrase.are_you_sure_you_want_to_delete_this_advertise_permanently'),
                ]);
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('advertise::phrase.create_new_advertise'))
                ->removeAttribute('value')
                ->to('advertise/advertise/create');
        });
    }
}
