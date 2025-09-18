<?php

namespace MetaFox\InAppPurchase\Repositories;

use MetaFox\InAppPurchase\Models\Order;
use MetaFox\Platform\Contracts\User as ContractUser;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Order.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface OrderRepositoryInterface
{
    /**
     * @param  ContractUser $context
     * @param  array        $attributes
     * @return Order
     */
    public function createIapOrder(ContractUser $context, array $attributes): Order;

    /**
     * @param  string      $platform
     * @param  string      $orgTransactionId
     * @param  string|null $transactionId
     * @return Order|null
     */
    public function getOrderByPlatform(string $platform, string $orgTransactionId, ?string $transactionId = null): ?Order;
}
