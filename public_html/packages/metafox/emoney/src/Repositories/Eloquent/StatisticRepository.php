<?php

namespace MetaFox\EMoney\Repositories\Eloquent;

use MetaFox\EMoney\Models\Statistic;
use MetaFox\EMoney\Models\Transaction;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\EMoney\Repositories\StatisticRepositoryInterface;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class StatisticRepository.
 */
class StatisticRepository extends AbstractRepository implements StatisticRepositoryInterface
{
    public function model()
    {
        return Statistic::class;
    }

    private function updateIncomingTransaction(User $user, Transaction $transaction): bool
    {
        $statistic = $this->getStatistic($user, $transaction->balance_currency);

        $field = $transaction->is_approved ? 'total_balance' : 'total_pending_transaction';

        $amount = $transaction->balance_price;

        $update = [$field => $amount + $statistic->{$field}];

        if ($transaction->is_approved) {
            $update['total_earned']              = $statistic->total_earned + $amount;
            $update['total_pending_transaction'] = $statistic->total_pending_transaction > $amount ? $statistic->total_pending_transaction - $amount : 0;
        }

        $statistic->update($update);

        return true;
    }

    private function updateOutgoingTransaction(User $user, Transaction $transaction): bool
    {
        if (!$transaction->is_approved) {
            return true;
        }

        $statistic = $this->getStatistic($user, $transaction->balance_currency);

        $balance = $statistic->total_balance - $transaction->balance_price;

        $totalPurchased = $statistic->total_purchased + $transaction->balance_price;

        if ($balance < 0) {
            $balance = 0;
        }

        $statistic->update(['total_balance' => $balance, 'total_purchased' => $totalPurchased]);

        return true;
    }

    protected function updateForSendingAdjustment(User $user, Transaction $transaction): bool
    {
        $statistic = $this->getStatistic($user, $transaction->balance_currency);

        $update = [
            'total_balance' => round($transaction->balance_price + $statistic->total_balance, 2),
            'total_sent'    => round($transaction->balance_price + $statistic->total_sent, 2),
        ];

        $statistic->update($update);

        return true;
    }

    protected function updateForReducingAdjustment(User $user, Transaction $transaction): bool
    {
        $statistic = $this->getStatistic($user, $transaction->balance_currency);

        $update = [
            'total_balance' => max(0, round($statistic->total_balance - $transaction->balance_price, 2)),
            'total_reduced'    => $transaction->balance_price + $statistic->total_reduced,
        ];

        $statistic->update($update);

        return true;
    }

    public function updateStatisticForBalanceAdjustment(User $user, Transaction $transaction): bool
    {
        if ($transaction->source === Support::TRANSACTION_SOURCE_INCOMING) {
            return $this->updateForSendingAdjustment($user, $transaction);
        }

        return $this->updateForReducingAdjustment($user, $transaction);
    }

    public function updateTransactionStatistic(User $user, Transaction $transaction): bool
    {
        if ($transaction->source == Support::TRANSACTION_SOURCE_INCOMING) {
            return $this->updateIncomingTransaction($user, $transaction);
        }

        return $this->updateOutgoingTransaction($user, $transaction);
    }

    public function updateWithdrawStatistic(WithdrawRequest $request): bool
    {
        $user = $request->user;

        if (null === $user) {
            return false;
        }

        $statistic      = $this->getStatistic($user, $request->currency);
        $balanceAmount  = $statistic->total_balance - $request->total;
        $withdrawAmount = $statistic->total_withdrawn + $request->total;

        $statistic->update([
            'total_balance'   => max($balanceAmount, 0),
            'total_withdrawn' => $withdrawAmount,
        ]);

        return true;
    }

    public function getStatistic(User $user, string $currency): Statistic
    {
        return $this->getModel()->newQuery()
            ->firstOrCreate([
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
                'currency'  => $currency,
            ], [
                'total_pending_transaction' => 0,
                'total_balance'             => 0,
                'total_pending'             => 0,
                'total_earned'              => 0,
                'total_withdrawn'           => 0,
                'total_purchased'           => 0,
            ]);
    }

    public function getUserBalance(User $user, string $currency): float
    {
        $statistic = $this->getStatistic($user, $currency);

        return $statistic->total_balance ?? 0;
    }

    public function updateCancelledWithdrawStatistic(WithdrawRequest $request): bool
    {
        $user = $request->user;

        if (null === $user) {
            return false;
        }

        $statistic = $this->getStatistic($user, $request->currency);

        $amount = $request->total;

        $totalPending = $statistic->total_pending - $amount;

        $totalBalance = $statistic->total_balance + $amount;

        $statistic->update([
            'total_pending' => max($totalPending, 0),
            'total_balance' => $totalBalance,
        ]);

        return true;
    }

    public function updateDeniedWithdrawStatistic(WithdrawRequest $request): bool
    {
        return $this->updateCancelledWithdrawStatistic($request);
    }

    public function updatePendingWithdrawStatistic(WithdrawRequest $request): bool
    {
        $user = $request->user;

        if (null === $user) {
            return false;
        }

        $statistic = $this->getStatistic($user, $request->currency);

        $amount = $request->total;

        $totalPending = $statistic->total_pending + $amount;

        $totalBalance = $statistic->total_balance - $amount;

        $statistic->update([
            'total_pending' => $totalPending,
            'total_balance' => max($totalBalance, 0),
        ]);

        return true;
    }

    public function updatePaidWithdrawStatistic(WithdrawRequest $request): bool
    {
        $user = $request->user;

        if (null === $user) {
            return false;
        }

        $statistic = $this->getStatistic($user, $request->currency);

        $amount = $request->total;

        $totalPending = $statistic->total_pending - $amount;

        $totalWithdrawn = $statistic->total_withdrawn + $amount;

        $statistic->update([
            'total_pending'   => max($totalPending, 0),
            'total_withdrawn' => $totalWithdrawn,
        ]);

        return true;
    }

    public function getUserBalances(User $user): array
    {
        return Statistic::query()
            ->where('user_id', $user->entityId())
            ->get()
            ->map(function ($item) {
                return [
                    'value' => app('currency')->getPriceFormatByCurrencyId($item->currency, $item->total_balance),
                    'label' => $item->currency,
                ];
            })
            ->toArray();
    }

    public function getUserBalancesByValue(User $user, string $column): array
    {
        return Statistic::query()
            ->where('user_id', $user->entityId())
            ->get()
            ->map(function ($item) use ($column) {
                return [
                    'value' => app('currency')->getPriceFormatByCurrencyId($item->currency, $item->$column),
                    'label' => $item->currency,
                ];
            })
            ->toArray();
    }

    public function getUserAmounts(User $user): array
    {
        return Statistic::query()
            ->where('user_id', $user->entityId())
            ->get()
            ->map(function ($item) {
                return [
                    'currency'      => $item->currency,
                    'total_balance' => $item->total_balance,
                    'total_earned'  => $item->total_earned,
                ];
            })
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getCurrencyOptions(User $user): array
    {
        return Statistic::query()
            ->where('user_id', $user->entityId())
            ->get()
            ->map(function ($item) {
                return [
                    'value'   => $item->currency,
                    'balance' => app('currency')->getPriceFormatByCurrencyId($item->currency, $item->total_balance),
                    'label'   => $item->currency,
                ];
            })
            ->toArray();
    }

    public function getUserBalancesOptions(User $user): array
    {
        return Statistic::query()
            ->where('user_id', $user->entityId())
            ->get()
            ->map(function ($item) {
                return [
                    'label'         => app('currency')->getPriceFormatByCurrencyId($item->currency, $item->total_balance),
                    'value'         => $item->currency,
                    'total_balance' => $item->total_balance,
                ];
            })
            ->toArray();
    }
}
