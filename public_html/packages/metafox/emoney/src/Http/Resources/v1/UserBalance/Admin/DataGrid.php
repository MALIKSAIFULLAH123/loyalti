<?php
namespace MetaFox\EMoney\Http\Resources\v1\UserBalance\Admin;

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig;
use MetaFox\Platform\Resource\ItemActionMenu;
use MetaFox\Platform\UserRole;

class DataGrid extends GridConfig
{
    /**
     * @var array|string[]
     */
    protected array $apiParams = [
        'full_name'     => ':full_name',
        'currency'     => ':currency',
    ];

    /**
     * @var array|array[]
     */
    protected array $apiRules = [
        'full_name'     => ['truthy', 'full_name'],
        'currency'      => ['truthy', 'currency'],
    ];

    protected string $appName      = 'emoney';

    protected string $resourceName = 'user-balance';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->setRowsPerPage(20, [10, 20, 50]);

        $this->addColumn('user')
            ->header(__p('core::web.photo'))
            ->renderAs('AvatarCell')
            ->width(120);

        $this->addColumn('display_name')
            ->header(__p('user::phrase.display_name'))
            ->linkTo('user_link')
            ->target('_blank')
            ->asFeaturedUser()
            ->sortable()
            ->sortableField('users.full_name')
            ->flex()
            ->truncateLines();

        $this->setCurrencyColumns();

        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('sendBalance')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('ewallet::admin.send_amount_to_balance'))
                ->params(['action' => 'sendBalance'])
                ->showWhen([
                    'and',
                    ['eq', 'session.user.role.id', 1],
                ]);

            $menu->addItem('reduceBalance')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('ewallet::admin.reduce_amount_from_balance'))
                ->params(['action' => 'reduceBalance'])
                ->showWhen([
                    'and',
                    ['eq', 'session.user.role.id', 1],
                ]);

            $menu->addItem('viewBalanceAdjustmentHistories')
                ->value(MetaFoxForm::ACTION_ROW_LINK)
                ->label(__p('ewallet::admin.view_adjustment_histories'))
                ->params([
                    'to' => '/admincp/ewallet/user-balance/:id/adjustment-history/browse',
                    'target' => '_blank',
                ])
                ->showWhen([
                    'and',
                    ['eq', 'session.user.role.id', 1],
                ]);
        });

        $this->withActions(function (Actions $actions) {
            $actions->add('sendBalance')
                ->asGet()
                ->apiUrl('admincp/core/form/ewallet.user_balance.send/:id');

            $actions->add('reduceBalance')
                ->asGet()
                ->apiUrl('admincp/core/form/ewallet.user_balance.reduce/:id');
        });
    }

    protected function setCurrencyColumns(): void
    {
        $codes = array_keys(app('currency')->getCurrencies());

        foreach ($codes as $code) {
            $this->addColumn(sprintf('ewallet_balance.%s', $code))
                ->header(__p('ewallet::admin.currency_balance', ['currency' => $code]))
                ->width(200)
                ->sortable()
                ->sortableField(sprintf('emoney_statistics.%s', $code));
        }
    }
}
