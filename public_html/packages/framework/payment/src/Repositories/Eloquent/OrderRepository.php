<?php

namespace MetaFox\Payment\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use MetaFox\Payment\Contracts\IsBillable;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Repositories\OrderRepositoryInterface;
use MetaFox\Payment\Support\Browse\Scopes\Order\StatusScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class OrderRepository.
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class OrderRepository extends AbstractRepository implements OrderRepositoryInterface
{
    public function model()
    {
        return Order::class;
    }

    public function createOrder(IsBillable $billable): Order
    {
        $data = $billable->toOrder();

        /** @var Order $order */
        $order = $this->getModel()->newModelInstance();
        $order->fill([
            'gateway_id'   => 0, // will be set later
            'user_id'      => $data['user_id'],
            'user_type'    => $data['user_type'],
            'item_id'      => $data['item_id'],
            'item_type'    => $data['item_type'],
            'title'        => $data['title'],
            'total'        => $data['total'],
            'currency'     => $data['currency'],
            'payment_type' => $data['payment_type'],
            'status'       => Order::STATUS_INIT,
        ]);

        $order->save();

        return $order;
    }

    public function getTransactions(User $context, array $attributes): Paginator
    {
        $status        = Arr::get($attributes, 'status', []);
        $excludeStatus = Arr::get($attributes, 'exclude_status', []);
        $search        = Arr::get($attributes, 'q');
        $dateFrom      = Arr::get($attributes, 'from');
        $dateTo        = Arr::get($attributes, 'to');
        $sort          = Arr::get($attributes, 'sort');
        $sortType      = Arr::get($attributes, 'sort_type');

        $query = $this->getModel()->newModelQuery();

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $statusScope = new StatusScope();
        $statusScope->setStatus($status)->exclude($excludeStatus);

        if ($search) {
            $query = $query->addScope(new SearchScope($search, ['title']));
        }

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        return $query
            ->addScope($sortScope)
            ->addScope($statusScope)
            ->where('item_type', '=', $attributes['item_type'])
            ->where('user_id', '=', $context->entityId())
            ->paginate($attributes['limit']);
    }

    public function getByGatewaySubscriptionId(string $gatewaySubscriptionId, int $gatewayId): ?Order
    {
        return $this->findWhere([
            'gateway_subscription_id' => $gatewaySubscriptionId,
            'gateway_id'              => $gatewayId,
        ])->first();
    }

    public function getByGatewayOrderId(string $gatewayOrderId, int $gatewayId): ?Order
    {
        return $this->findWhere([
            'gateway_order_id' => $gatewayOrderId,
            'gateway_id'       => $gatewayId,
        ])->first();
    }
}
