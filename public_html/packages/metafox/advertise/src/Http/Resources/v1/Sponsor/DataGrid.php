<?php

namespace MetaFox\Advertise\Http\Resources\v1\Sponsor;

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
        'start_date' => ':start_date',
        'end_date'   => ':end_date',
        'status'     => ':status',
    ];

    /**
     * @var array|array[]
     */
    protected array $apiRules = [
        'status'     => ['truthy', 'status'],
        'start_date' => ['truthy', 'start_date'],
        'end_date'   => ['truthy', 'end_date'],
    ];

    protected function initialize(): void
    {
        $this->setRowsPerPage(20, [10, 20, 50]);
        $this->setDataSource(apiUrl('sponsor.index'), $this->apiParams, $this->apiRules);
        $this->dynamicRowHeight();

        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->truncateLines()
            ->linkTo('url')
            ->target('_blank')
            ->flex();

        $this->addColumn('start_date')
            ->header(__p('advertise::phrase.start_date'))
            ->asDateTime()
            ->width(200);

        $this->addColumn('end_date')
            ->header(__p('advertise::phrase.end_date'))
            ->asDateTime()
            ->width(200);

        $this->addColumn('status')
            ->header(__p('core::web.status'))
            ->width(120);

        $this->addColumn('statistic.current_impressions')
            ->asNumber()
            ->width(150)
            ->alignCenter()
            ->header(__p('advertise::web.views'))
            ->asNumber();

        $this->addColumn('statistic.current_clicks')
            ->header(__p('advertise::web.clicks'))
            ->asNumber()
            ->alignCenter()
            ->width(150)
            ->asNumber();

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->width(120);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('editItem')
                ->apiUrl('core/form/advertise.advertise_sponsor.update/:id')
                ->asFormDialog(false)
                ->pageUrl('advertise/sponsor/edit/:id');

            $actions->add('paymentItem')
                ->apiUrl('core/form/advertise.advertise_sponsor.payment/:id');

            $actions->add('toggleActive')
                ->apiUrl('advertise/sponsor/active/:id')
                ->asPatch();

            $actions->add('deleteItem')
                ->apiUrl('advertise/sponsor/:id')
                ->asDelete();
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
                    ['falsy', 'item.extra.can_edit'],
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
                    'message' => __p('advertise::phrase.are_you_sure_you_want_to_delete_this_sponsor_permanently'),
                ]);
        });
    }
}
