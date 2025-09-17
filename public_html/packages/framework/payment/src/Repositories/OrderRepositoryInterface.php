<?php

namespace MetaFox\Payment\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Payment\Contracts\IsBillable;
use MetaFox\Payment\Models\Order;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Order.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface OrderRepositoryInterface
{
    /**
     * createOrder.
     *
     * @param  IsBillable $billable
     * @return Order
     */
    public function createOrder(IsBillable $billable): Order;

    /**
     * getByGatewaySubscriptionId.
     *
     * @param  string $gatewaySubscriptionId
     * @param  int    $gatewayId
     * @return ?Order
     */
    public function getByGatewaySubscriptionId(string $gatewaySubscriptionId, int $gatewayId): ?Order;

    /**
     * @param  User                $context
     * @param  array<string,mixed> $attributes
     * @return Paginator
     */
    public function getTransactions(User $context, array $attributes): Paginator;

    /**
     * getByGatewayOrderId.
     *
     * @param  string $gatewayOrderId
     * @param  int    $gatewayId
     * @return ?Order
     */
    public function getByGatewayOrderId(string $gatewayOrderId, int $gatewayId): ?Order;
}
