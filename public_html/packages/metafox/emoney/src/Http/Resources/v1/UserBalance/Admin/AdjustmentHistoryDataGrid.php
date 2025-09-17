<?php
namespace MetaFox\EMoney\Http\Resources\v1\UserBalance\Admin;

use MetaFox\EMoney\Policies\UserBalancePolicy;
use MetaFox\Platform\Resource\GridConfig;

class AdjustmentHistoryDataGrid extends GridConfig
{
    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->setRowsPerPage(20, [10, 20, 50]);

        $this->addColumn('sender.display_name')
            ->header(__p('ewallet::admin.sender'))
            ->linkTo('sender.url')
            ->flex()
            ->truncateLines();

        $this->addColumn('type')
            ->header(__p('core::phrase.type'))
            ->width(150);

        $this->addColumn('amount')
            ->header(__p('payment::phrase.amount'))
            ->width(200);

        $this->addColumn('creation_date')
            ->header(__p('core::phrase.creation_date'))
            ->asDateTime()
            ->width(200);
    }

    /**
     * @param int $id
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function boot(int $parent): void
    {
        $context = user();

        policy_authorize(UserBalancePolicy::class, 'viewHistories', $context);

        $this->setDataSource(apiUrl('admin.emoney.user-balance.viewAdjustmentHistories', ['id' => $parent]), [
            'user_full_name'     => ':user_full_name',
            'owner_full_name'     => ':owner_full_name',
            'currency'     => ':currency',
            'type'         => ':type',
            'date_from'         => ':date_from',
            'date_to'           => ':date_to',
            'price_from'         => ':price_from',
            'price_to'           => ':price_to',
        ], [
            'user_full_name'     => ['truthy', 'user_full_name'],
            'owner_full_name'      => ['truthy', 'owner_full_name'],
            'currency'     => ['truthy', 'currency'],
            'type'      => ['truthy', 'type'],
            'date_from'     => ['truthy', 'date_from'],
            'date_to'      => ['truthy', 'date_to'],
            'price_from'     => ['truthy', 'price_from'],
            'price_to'      => ['truthy', 'price_to'],
        ]);
    }
}
