<?php

namespace MetaFox\EMoney\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\EMoney\Models\BalanceAdjustment;
use MetaFox\EMoney\Models\Transaction;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction as PaymentTransaction;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Transaction.
 * @method static Transaction|null createTransaction(Order $order, ?PaymentTransaction $transaction = null)
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface TransactionRepositoryInterface
{
    /**
     * @param User              $user
     * @param User              $owner
     * @param BalanceAdjustment $model
     * @param string            $currency
     * @param float             $total
     * @param string            $source
     * @param string            $type
     * @param array|null        $extra
     * @return Transaction|null
     */
    public function createTransactionForBalanceAdjustment(User $user, User $owner, BalanceAdjustment $model, string $currency, float $total, string $source, string $type, ?array $extra = null): ?Transaction;

    /**
     * @param User   $user
     * @param User   $owner
     * @param Entity $entity
     * @param string $currency
     * @param float  $total
     *
     * @return Transaction
     */
    public function createTrackingTransaction(User $user, User $owner, Entity $entity, string $currency, float $total): Transaction;

    /**
     * @param  WithdrawRequest $request
     * @return Transaction
     */
    public function createPendingTrackingWithdrawnTransaction(WithdrawRequest $withdrawRequest): Transaction;

    /**
     * @param User       $user
     * @param User       $owner
     * @param Entity     $entity
     * @param string     $currency
     * @param float      $total
     * @param float|null $commissionPercentage
     * @param int|null   $holdingDays
     *
     * @return Transaction|null
     */
    public function createTransactionForIntegration(User $user, User $owner, Entity $entity, string $currency, float $total, ?float $commissionPercentage = null, ?int $holdingDays = null): ?Transaction;

    /**
     * @param Order       $order
     * @param string|null $gatewayOrderId
     * @param array       $extra
     *
     * @return Transaction|null
     */
    public function createOutgoingTransaction(Order $order, ?string $gatewayOrderId = null, array $extra = []): ?Transaction;

    /**
     * @param PaymentTransaction $transaction
     * @param array              $extra
     *
     * @return Transaction|null
     */
    public function createIncomingTransaction(PaymentTransaction $transaction, array $extra = []): ?Transaction;

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function approveTransaction(Transaction $transaction): bool;

    /**
     * @param User  $user
     * @param array $attributes
     *
     * @return Paginator
     */
    public function viewTransactions(User $user, array $attributes = []): Paginator;

    /**
     * @param array $attributes
     *
     * @return Paginator
     */
    public function viewTransactionsAdminCP(array $attributes): Paginator;

    /**
     * @param  WithdrawRequest $withdrawRequest
     * @return void
     */
    public function processWithdrawRequestCancelled(WithdrawRequest $withdrawRequest): void;

    /**
     * @param  WithdrawRequest $withdrawRequest
     * @return void
     */
    public function processWithdrawRequestDenied(WithdrawRequest $withdrawRequest): void;

    /**
     * @param  WithdrawRequest $withdrawRequest
     * @return void
     */
    public function processWithdrawRequestProcessed(WithdrawRequest $withdrawRequest): void;
}
