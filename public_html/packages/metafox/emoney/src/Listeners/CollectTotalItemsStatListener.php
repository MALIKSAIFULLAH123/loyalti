<?php

namespace MetaFox\EMoney\Listeners;

use Carbon\Carbon;
use MetaFox\Core\Listeners\Abstracts\AbstractCollectTotalItemStatListener as AbstractClass;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\EMoney\Repositories\WithdrawRequestRepositoryInterface;
use MetaFox\EMoney\Support\Support;

class CollectTotalItemsStatListener extends AbstractClass
{
    /**
     * @param  Carbon|null            $after
     * @param  Carbon|null            $before
     * @return array<int, mixed>|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDefaultStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        return [
            [
                'name'  => WithdrawRequest::ENTITY_TYPE,
                'label' => 'ewallet::phrase.withdrawal_requests',
                'value' => resolve(WithdrawRequestRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
                'url'   => url_utility()->makeApiFullUrl('admincp/ewallet/request/browse'),
            ],
        ];
    }

    /**
     * @param  Carbon|null            $after
     * @param  Carbon|null            $before
     * @return array<int, mixed>|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getPendingStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        $conditions = [
            'status' => Support::WITHDRAW_STATUS_PENDING,
        ];

        return [
            [
                'name'  => 'pending_withdrawal_request',
                'label' => 'ewallet::phrase.withdrawal_requests',
                'value' => resolve(WithdrawRequestRepositoryInterface::class)->getTotalPendingItemByPeriod(null, null, $conditions),
                'group' => 'pending',
                'url'   => url_utility()->makeApiFullUrl('admincp/ewallet/request/browse?status=' . Support::WITHDRAW_STATUS_PENDING),
            ],
        ];
    }
}
