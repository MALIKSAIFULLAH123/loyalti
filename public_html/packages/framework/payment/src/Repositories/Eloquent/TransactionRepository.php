<?php

namespace MetaFox\Payment\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;
use MetaFox\Payment\Repositories\TransactionRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Helper\Pagination;
use RuntimeException;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class TransactionRepository.
 */
class TransactionRepository extends AbstractRepository implements TransactionRepositoryInterface
{
    public function model()
    {
        return Transaction::class;
    }

    public function createTransaction(Order $order, array $params = []): Transaction
    {
        $transaction = new Transaction();
        $transaction->fill([
            'gateway_id'             => $order->gateway_id,
            'order_id'               => $order->entityId(),
            'user_id'                => $order->userId(),
            'user_type'              => $order->userType(),
            'amount'                 => Arr::get($params, 'amount'),
            'currency'               => Arr::get($params, 'currency'),
            'status'                 => Arr::get($params, 'status', Transaction::STATUS_PENDING),
            'gateway_order_id'       => $order->gateway_order_id,
            'gateway_transaction_id' => Arr::get($params, 'id'),
            'raw_data'               => Arr::get($params, 'raw_data', []),
        ]);
        $transaction->save();

        return $transaction;
    }

    public function getByGatewayTransactionId(string $gatewayTransactionId, int $gatewayId): ?Transaction
    {
        return $this->findWhere([
            'gateway_transaction_id' => $gatewayTransactionId,
            'gateway_id'             => $gatewayId,
        ])->first();
    }

    public function handleTransactionData(Order $order, array $transactionData = []): Transaction
    {
        $gatewayTransactionId = Arr::get($transactionData, 'id');
        if (!$gatewayTransactionId) {
            throw new RuntimeException('Missing transaction data.');
        }

        $transaction = $this->getByGatewayTransactionId($gatewayTransactionId, $order->gateway_id);
        if (!$transaction instanceof Transaction) {
            $transaction = $this->createTransaction($order, $transactionData);
        }

        $transaction->status = Arr::get($transactionData, 'status', Transaction::STATUS_PENDING);
        $transaction->save();

        return $transaction;
    }

    public function viewTransactionsAdminCP(array $attributes = []): Paginator
    {
        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query = $this->getModel()->newQuery();

        $attributes = Arr::only($attributes, ['gateway_id', 'status', 'gateway_transaction_id']);

        if (count($attributes)) {
            $query->where($attributes);
        }

        return $query->with(['gateway', 'userEntity'])
            ->paginate($limit, ['payment_transactions.*']);
    }
}
