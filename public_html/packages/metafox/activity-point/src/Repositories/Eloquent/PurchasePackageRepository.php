<?php

namespace MetaFox\ActivityPoint\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Models\PackagePurchase as Model;
use MetaFox\ActivityPoint\Repositories\PurchasePackageRepositoryInterface;
use MetaFox\ActivityPoint\Support\Browse\Scopes\PackagePurchase\SortScope;
use MetaFox\ActivityPoint\Support\Browse\Scopes\PackagePurchase\StatusScope;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * Class PurchasePackageRepository.
 *
 * @method Model find($id, $columns = ['*'])
 * @method Model getModel()
 */
class PurchasePackageRepository extends AbstractRepository implements PurchasePackageRepositoryInterface
{
    public function model(): string
    {
        return Model::class;
    }

    public function viewPurchasePackageAdminCP(User $context, array $attributes = []): Paginator
    {
        $limit         = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $userFullName  = Arr::get($attributes, 'full_name');
        $packageId     = Arr::get($attributes, 'package_id');
        $transactionId = Arr::get($attributes, 'id');
        $query         = $this->getModel()->newQuery();
        $table         = $this->getModel()->getTable();
        $builder       = $this->builderTransaction($query, $attributes);

        if (is_numeric($packageId)) {
            $builder->where("$table.package_id", '=', $packageId);
        }

        if (is_numeric($transactionId)) {
            $builder->where("$table.id", '=', $transactionId);
        }

        if (is_string($userFullName)) {
            $query->join('users', function (JoinClause $joinClause) use ($table) {
                $joinClause->on('users.id', '=', "$table.user_id");
            })
                ->where('users.full_name', $this->likeOperator(), '%' . $userFullName . '%');
        }

        return $builder->paginate($limit, ["$table.*"]);
    }

    public function viewTransactions(User $context, array $attributes): Builder
    {
        $table = $this->getModel()->getTable();
        $query = $this->getModel()->newQuery();

        $query = $this->builderTransaction($query, $attributes);

        $query->where("$table.user_id", '=', $context->id);

        return $query;
    }

    protected function builderTransaction(Builder $query, array $attributes): Builder
    {
        $status        = Arr::get($attributes, 'status', []);
        $search        = Arr::get($attributes, 'q');
        $dateFrom      = Arr::get($attributes, 'from');
        $dateTo        = Arr::get($attributes, 'to');
        $sort          = Arr::get($attributes, 'sort');
        $sortType      = Arr::get($attributes, 'sort_type');
        $transactionId = Arr::get($attributes, 'transaction_id');
        $gatewayId     = Arr::get($attributes, 'gateway_id');
        $table         = $this->getModel()->getTable();

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $statusScope = new StatusScope();
        $statusScope->setStatus($status);
        $hasJoinPackage = !empty($search) || in_array($sort, [SortScope::SORT_PACKAGE_NAME]);

        if ($hasJoinPackage) {
            $query->leftJoin('apt_packages', 'apt_packages.id', '=', "$table.package_id");
        }

        if ($search) {
            $query = $query->addScope(new SearchScope($search, ['apt_packages.title']));
        }

        if ($dateFrom) {
            $query->where("$table.created_at", '>=', $dateFrom);
        }

        if ($transactionId) {
            $query->where("$table.transaction_id", '=', $transactionId);
        }

        if ($gatewayId) {
            $query->where("$table.gateway_id", '=', $gatewayId);
        }

        if ($dateTo) {
            $query->where("$table.created_at", '<=', $dateTo);
        }

        return $query->addScope($sortScope)->addScope($statusScope);
    }

    /**
     * @inheritDoc
     */
    public function payInvoice(User $context, int $id, int $gatewayId, array $extra = []): array
    {
        $purchase  = $this->find($id);
        $returnUrl = url_utility()->makeApiFullUrl('activitypoint/transactions-package');

        $purchase->update([
            'gateway_id' => $gatewayId,
        ]);

        // Init order them place order
        $order  = Payment::initOrder($purchase);
        $params = [
            'return_url'  => $returnUrl,
            'cancel_url'  => $returnUrl,
            'description' => $purchase->payment_description,
        ];

        if (!empty($extra)) {
            $params = array_merge($params, $extra);
        }

        $data = Payment::placeOrder($order, $purchase->gateway_id, $params);

        return [
            'url'   => Arr::get($data, 'gateway_redirect_url'),
            'token' => Arr::get($data, 'gateway_token'),
        ];
    }
}
