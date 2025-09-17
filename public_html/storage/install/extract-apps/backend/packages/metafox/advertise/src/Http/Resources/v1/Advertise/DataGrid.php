<?php

namespace MetaFox\Advertise\Http\Resources\v1\Advertise;

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
 *
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    public bool $isAdminCP = false;

    /**
     * @var array|string[]
     */
    protected array $apiParams = [
        'placement_id' => ':placement_id',
        'view'         => ':view',
        'start_date'   => ':start_date',
        'end_date'     => ':end_date',
        'status'       => ':status',
    ];

    /**
     * @var array|array[]
     */
    protected array $apiRules = [
        'package_id' => ['truthy', 'package_id'],
        'status'     => ['truthy', 'status'],
        'view'       => ['truthy', 'view'],
        'start_date' => ['truthy', 'start_date'],
        'end_date'   => ['truthy', 'end_date'],
    ];

    protected function initialize(): void
    {
        $this->setRowsPerPage(20, [10, 20, 50]);

        $this->setDataSource(apiUrl('advertise.index'), $this->apiParams, $this->apiRules);
        $this->dynamicRowHeight();

        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->linkTo('link')
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
            $actions->add('editItem')
                ->apiUrl('core/form/advertise.edit/:id')
                ->asFormDialog(false)
                ->pageUrl('advertise/edit/:id');

            $actions->add('paymentItem')
                ->apiUrl('core/form/advertise.payment/:id');

            $actions->add('deleteItem')
                ->apiUrl('advertise/advertise/:id')
                ->asDelete();

            $actions->add('toggleActive')
                ->apiUrl('advertise/advertise/active/:id')
                ->asPatch();
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('edit')
                ->icon('ico-pencilline-o')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('core::phrase.edit'))
                ->params([
                    'action'      => 'editItem',
                    'dialogProps' => [
                        'fullWidth' => true,
                    ],
                ])
                ->as('menuItem.dataGird.as.labelIcu')
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_edit'],
                ]);

            $menu->addItem('payment')
                ->icon('ico-credit-card-o')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('advertise::web.pay_now'))
                ->params([
                    'action'      => 'paymentItem',
                    'as'          => [
                        'price' => 'price',
                    ],
                    'dialogProps' => [
                        'fullWidth' => false,
                    ],
                ])
                ->as('menuItem.dataGird.as.labelIcu')
                ->showWhen([
                    'truthy', 'item.extra.can_payment',
                ]);

            $menu->withDelete()
                ->action('deleteItem')
                ->showWhen([
                    'truthy', 'item.extra.can_delete',
                ])
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('advertise::phrase.are_you_sure_you_want_to_delete_this_advertise_permanently'),
                ]);
        });
    }
}
