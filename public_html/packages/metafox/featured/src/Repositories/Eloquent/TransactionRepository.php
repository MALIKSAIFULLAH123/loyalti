<?php

namespace MetaFox\Featured\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Jobs\HandleTransactionForDeletedContentJob;
use MetaFox\Featured\Models\Item;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Featured\Repositories\TransactionRepositoryInterface;
use MetaFox\Featured\Models\Transaction;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class TransactionRepository
 *
 */
class TransactionRepository extends AbstractRepository implements TransactionRepositoryInterface
{
    public function model()
    {
        return Transaction::class;
    }

    public function handleContentDeleted(Content $content): bool
    {
        HandleTransactionForDeletedContentJob::dispatch($content->entityType(), $content->entityId(), Feature::getItemTitle($content));

        return true;
    }

    /*
     * TODO: Implement if need
     */
    public function handleItemDeleted(Item $item): bool
    {
        return true;
    }

    public function createTransaction(array $attributes): Transaction
    {
        /**
         * @var Transaction $transaction
         */
        $transaction = $this->getModel()->newInstance($attributes);

        $transaction->save();

        return $transaction->refresh();
    }

    /**
     * @warning Only use this method in AdminCP
     * @param array $attributes
     * @return Paginator
     */
    public function viewTransactions(array $attributes = []): Paginator
    {
        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $fullName = Arr::get($attributes, 'full_name');

        $fromDate = Arr::get($attributes, 'from_date');

        $toDate = Arr::get($attributes, 'to_date');

        $attributes = Arr::only($attributes, ['payment_gateway', 'item_type', 'status', 'transaction_id']);

        $builder = $this->getModel()->newQuery();

        if (count($attributes)) {
            foreach ($attributes as $key => $value) {
                $builder->where(sprintf('featured_transactions.%s', $key), '=', $value);
            }
        }

        if (is_string($fullName)) {
            $builder->join('user_entities', function (JoinClause $joinClause) {
                $joinClause->on('user_entities.id', '=', 'featured_transactions.user_id');
            })->where('user_entities.name', $this->likeOperator(), '%' . $fullName . '%');
        }

        if (is_string($fromDate)) {
            $builder->where('featured_transactions.created_at', '>=', $fromDate);
        }

        if (is_string($toDate)) {
            $builder->where('featured_transactions.created_at', '<=', $toDate);
        }

        return $builder->with(['user', 'item', 'paymentGateway'])
            ->orderByDesc('featured_transactions.id')
            ->paginate($limit, ['featured_transactions.*']);
    }
}
