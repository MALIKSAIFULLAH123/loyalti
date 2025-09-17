<?php
namespace MetaFox\Featured\Http\Resources\v1\Item;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig;
use MetaFox\Platform\Resource\ItemActionMenu;

class DataGrid extends GridConfig
{
    public bool $isAdminCP = false;

    /**
     * @var array|string[]
     */
    protected array $apiParams = [
        'item_type'  => ':item_type',
        'package_id' => ':package_id',
        'status'     => ':status',
        'package_duration_period'  => ':package_duration_period',
        'from_date'  => ':from_date',
        'to_date'  => ':to_date',
        'id' => ':id'
    ];

    /**
     * @var array|array[]
     */
    protected array $apiRules = [
        'item_type' => ['truthy', 'item_type'],
        'package_id' => ['truthy', 'package_id'],
        'status' => ['truthy', 'status'],
        'package_duration_period' => ['truthy', 'package_duration_period'],
        'from_date' => ['truthy', 'from_date'],
        'to_date' => ['truthy', 'to_date'],
        'id' => ['truthy', 'id']
    ];

    public function boot()
    {
        if (user()->isGuest()) {
            throw new AuthorizationException();
        }
    }

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->setRowsPerPage(20, [10, 20, 50]);

        $this->setDataSource(apiUrl('featured.item.index'), $this->apiParams, $this->apiRules);

        $this->addColumn('item_title')
            ->header(__p('core::web.item'))
            ->truncateLines()
            ->linkTo('item_link')
            ->target('_blank')
            ->flex();

        $this->addColumn('item_type_label')
            ->header(__p('core::phrase.item_type'))
            ->truncateLines()
            ->width(180);

        $this->addColumn('package.title')
            ->header(__p('featured::phrase.package'))
            ->truncateLines()
            ->flex();

        $this->addColumn('is_free')
            ->header(__p('core::web.free'))
            ->asYesNoIcon()
            ->width(150);

        $this->addColumn('status')
            ->header(__p('core::phrase.status'))
            ->truncateLines()
            ->width(150);

        $this->addColumn('duration')
            ->header(__p('featured::admin.duration'))
            ->truncateLines()
            ->width(150);

        $this->addColumn('expiration_date')
            ->header(__p('core::web.subscribe_expiration_date'))
            ->truncateLines()
            ->asDateTime()
            ->width(200);

        $this->addColumn('creation_date')
            ->header(__p('core::phrase.creation_date'))
            ->truncateLines()
            ->asDateTime()
            ->width(200);

        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('payment')
                ->icon('ico-credit-card-o')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('featured::phrase.pay_price'))
                ->params([
                    'action' => 'paymentItem',
                    'as'     => [
                        'price' => 'price'
                    ],
                    'dialogProps'=>[
                        'fullWidth'=> false
                    ]
                ])
                ->as('menuItem.dataGird.as.labelIcu')
                ->showWhen([
                    'truthy', 'item.extra.can_payment'
                ]);

            $menu->addItem('cancel')
                ->value(MetaFoxForm::ACTION_BATCH_ITEM)
                ->label(__p('core::phrase.cancel'))
                ->params(['action' => 'cancelItem'])
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_cancel'],
                ])
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('featured::phrase.are_you_sure_you_want_to_cancel_this_featured_item'),
                ])
                ->reload();

            $menu->withDelete()
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('featured::phrase.delete_featured_item_description'),
                ]);
        });

        $this->withActions(function (Actions $actions) {
            $actions->add('paymentItem')
                ->apiUrl('featured/item/:id/payment-form');

            $actions->add('cancelItem')
                ->asPatch()
                ->apiUrl('featured/item/:id/cancel');

            $actions->add('destroy')
                ->asDelete()
                ->apiUrl('featured/item/:id');
        });
    }
}
