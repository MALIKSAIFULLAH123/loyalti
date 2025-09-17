<?php

namespace MetaFox\EMoney\Repositories;

use MetaFox\EMoney\Models\Statistic;
use MetaFox\EMoney\Models\Transaction;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Statistic.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface StatisticRepositoryInterface
{
    /**
     * @param User        $user
     * @param Transaction $transaction
     * @return bool
     */
    public function updateStatisticForBalanceAdjustment(User $user, Transaction $transaction): bool;

    /**
     * @param User        $user
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function updateTransactionStatistic(User $user, Transaction $transaction): bool;

    /**
     * @param WithdrawRequest $request
     *
     * @return bool
     */
    public function updateWithdrawStatistic(WithdrawRequest $request): bool;

    /**
     * @param WithdrawRequest $request
     *
     * @return bool
     */
    public function updateCancelledWithdrawStatistic(WithdrawRequest $request): bool;

    /**
     * @param WithdrawRequest $request
     *
     * @return bool
     */
    public function updateDeniedWithdrawStatistic(WithdrawRequest $request): bool;

    /**
     * @param WithdrawRequest $request
     *
     * @return bool
     */
    public function updatePendingWithdrawStatistic(WithdrawRequest $request): bool;

    /**
     * @param WithdrawRequest $request
     *
     * @return bool
     */
    public function updatePaidWithdrawStatistic(WithdrawRequest $request): bool;

    /**
     * @param User   $user
     * @param string $currency
     *
     * @return float
     */
    public function getUserBalance(User $user, string $currency): float;

    /**
     * @param User   $user
     * @param string $currency
     *
     * @return Statistic
     */
    public function getStatistic(User $user, string $currency): Statistic;

    /**
     * @param User $user
     *
     * @return array
     */
    public function getUserBalances(User $user): array;

    /**
     * @param User $user
     *
     * @return array
     */
    public function getUserBalancesOptions(User $user): array;

    /**
     * @param User $user
     *
     * @return array
     */
    public function getCurrencyOptions(User $user): array;

    /**
     * @param User $user
     *
     * @return array
     */
    public function getUserAmounts(User $user): array;

    /**
     * @param User   $user
     * @param string $column
     *
     * @return array
     */
    public function getUserBalancesByValue(User $user, string $column): array;
}
