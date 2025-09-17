<?php

namespace MetaFox\Payment\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Repositories\OrderAdminRepositoryInterface;
use MetaFox\Payment\Support\Browse\Scopes\Order\StatusScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class OrderAdminRepository.
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class OrderAdminRepository extends AbstractRepository implements OrderAdminRepositoryInterface
{
    public function model()
    {
        return Order::class;
    }

    public function getTransactions(User $context, array $attributes): Collection
    {
        return $this->getTransactionsBuilder($context, $attributes)
            ->get();
    }

    public function getTransactionsBuilder(User $context, array $attributes): Builder
    {
        $status        = Arr::get($attributes, 'status', []);
        $excludeStatus = Arr::get($attributes, 'exclude_status', []);
        $userFullName  = Arr::get($attributes, 'full_name');
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
            $query->where('payment_orders.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('payment_orders.created_at', '<=', $dateTo);
        }

        if (is_string($userFullName)) {
            $query->join('users', function (JoinClause $joinClause) {
                $joinClause->on('users.id', '=', 'payment_orders.user_id');
            })
            ->where('users.full_name', $this->likeOperator(), '%' . $userFullName . '%');
        }

        return $query
            ->addScope($sortScope)
            ->addScope($statusScope)
            ->where('payment_orders.item_type', '=', $attributes['item_type']);
    }

    public function viewOrders(array $attributes = []): Paginator
    {
        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query = $this->getModel()->newQuery();

        $attributes = Arr::only($attributes, [
            'gateway_id',
            'payment_type',
            'status',
            'recurring_status',
            'gateway_order_id',
            'gateway_subscription_id'
        ]);

        if (count($attributes)) {
            $query->where($attributes);
        }

        return $query
            ->with(['item', 'userEntity', 'payeeEntity', 'gateway'])
            ->orderByDesc('id')
            ->paginate($limit, ['payment_orders.*']);
    }
}
