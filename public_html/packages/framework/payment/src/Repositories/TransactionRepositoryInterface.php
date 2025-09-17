<?php

namespace MetaFox\Payment\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Transaction.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface TransactionRepositoryInterface
{
    /**
     * createTransaction.
     *
     * @param  Order        $order
     * @param  array<mixed> $params
     * @return Transaction
     */
    public function createTransaction(Order $order, array $params = []): Transaction;

    /**
     * getByGatewayTransactionId.
     *
     * @param  string      $gatewayTransactionId
     * @param  int         $gatewayId
     * @return Transaction
     */
    public function getByGatewayTransactionId(string $gatewayTransactionId, int $gatewayId): ?Transaction;

    /**
     * handleTransactionData.
     *
     * @param  Order        $order
     * @param  array<mixed> $transactionData
     * @return Transaction
     */
    public function handleTransactionData(Order $order, array $transactionData = []): Transaction;

    /**
     * @param array $attributes
     * @return Paginator
     */
    public function viewTransactionsAdminCP(array $attributes = []): Paginator;
}
