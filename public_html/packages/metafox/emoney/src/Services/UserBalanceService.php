<?php
namespace MetaFox\EMoney\Services;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use MetaFox\EMoney\Facades\UserBalance;
use MetaFox\EMoney\Models\BalanceAdjustment;
use MetaFox\EMoney\Notifications\ReduceAmountFromBalanceNotification;
use MetaFox\EMoney\Notifications\SendAmountToBalanceNotification;
use MetaFox\EMoney\Repositories\StatisticRepositoryInterface;
use MetaFox\EMoney\Repositories\TransactionRepositoryInterface;
use MetaFox\EMoney\Services\Contracts\UserBalanceServiceInterface;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\User\Models\User as UserModel;

class UserBalanceService implements UserBalanceServiceInterface
{
    public function __construct(protected StatisticRepositoryInterface $repository)
    {
    }

    public function manageUserBalances(array $attributes = []): Paginator
    {
        $limit = (int) Arr::get($attributes, 'limit', 20);

        $sortType = Arr::get($attributes, 'sort_type', Browse::SORT_TYPE_ASC);

        $sort = Arr::get($attributes, 'sort', 'users.full_name');

        $builder = UserModel::query()
            ->with(['userEntity'])
            ->whereNotNull('users.verified_at')
            ->where('users.approve_status', MetaFoxConstant::STATUS_APPROVED);

        if (is_string($fullName = Arr::get($attributes, 'full_name')) && trim($fullName) !== '') {
            $builder->where('users.full_name', $this->getLikeOperator(), '%' . $fullName . '%');
        }

        if (preg_match('/^emoney_statistics\.(.*)$/', $sort, $matches)) {
            $currency = array_pop($matches);

            $builder->leftJoin('emoney_statistics', function (JoinClause $clause) use ($currency) {
                $clause->on('users.id', '=', 'emoney_statistics.user_id')
                    ->where('emoney_statistics.currency', '=', $currency);
            })
            ->orderByRaw("CASE WHEN emoney_statistics.id IS NOT NULL THEN 1 ELSE 0 END " . strtoupper($sortType))
            ->orderBy('emoney_statistics.total_balance', $sortType);
        } else {
            $builder->orderBy($sort, $sortType);
        }

        return $builder
            ->orderByDesc('users.id')
            ->paginate($limit, ['users.*']);
    }

    protected function renderUserBalance(array $currencies, array $balances): array
    {
        $data = array_combine($currencies, array_fill(0, count($currencies), 0));

        $data = array_merge($data, $balances);

        foreach ($data as $currency => $balance) {
            $data[$currency] = app('currency')->getPriceFormatByCurrencyId($currency, $balance);
        }

        return $data;
    }

    public function preloadUserBalances(Collection $collection): void
    {
        $ids = $collection->pluck('id')->toArray();

        $currencies = array_keys(app('currency')->getCurrencies());

        $balances = $this->repository->getModel()->newQuery()
            ->whereIn('user_id', $ids)
            ->get()
            ->groupBy('user_id')
            ->map(function (Collection $collection) {
                return $collection->pluck('total_balance', 'currency');
            })
            ->toArray();

        foreach ($ids as $id) {
            LoadReduce::remember(sprintf('emoney::user_balance::getUserBalances(%s)', $id), function () use ($balances, $id, $currencies) {
                $balance = Arr::get($balances, $id);

                if (!is_array($balance)) {
                    $balance = [];
                }

                return $this->renderUserBalance($currencies, $balance);
            });
        }
    }

    public function getUserBalances(User $user): array
    {
        return LoadReduce::get(sprintf('emoney::user_balance::getUserBalances(%s)', $user->entityId()), function () use ($user) {
            $currencies = array_keys(app('currency')->getCurrencies());

            $balances = $this->repository->getModel()->newQuery()
                ->where('user_id', $user->entityId())
                ->get()
                ->pluck('total_balance', 'currency')
                ->toArray();

            return $this->renderUserBalance($currencies, $balances);
        });
    }

    protected function getLikeOperator(): string
    {
        return database_driver() == 'pgsql' ? 'ilike' : 'like';
    }

    public function sendAmountToSpecificUserBalanceByCurrency(User $context, User $user, string $currency, float $amount): bool
    {
        $log = new BalanceAdjustment([
            'user_id' => $context->entityId(),
            'user_type' => $context->entityType(),
            'owner_id' => $user->entityId(),
            'owner_type' => $user->entityType(),
            'currency' => $currency,
            'amount' => $amount,
            'type' => Support::USER_BALANCE_ACTION_SEND,
        ]);

        $log->save();

        resolve(TransactionRepositoryInterface::class)->createTransactionForBalanceAdjustment($context, $user, $log, $currency, $amount, Support::TRANSACTION_SOURCE_INCOMING, Support::INCOMING_TRANSACTION_TYPE_RECEIVED_FROM_ADMIN, [
            'type_description' => [
                'phrase' => 'ewallet::phrase.sent_to_your_wallet',
            ],
        ]);

        $this->sendReducingNotification($context, $user, $log);

        return true;
    }

    public function reduceAmountToSpecificUserBalanceByCurrency(User $context, User $user, string $currency, float $amount): bool
    {
        $log = new BalanceAdjustment([
            'user_id' => $context->entityId(),
            'user_type' => $context->entityType(),
            'owner_id' => $user->entityId(),
            'owner_type' => $user->entityType(),
            'currency' => $currency,
            'amount' => $amount,
            'type' => Support::USER_BALANCE_ACTION_REDUCE,
        ]);

        $log->save();

        resolve(TransactionRepositoryInterface::class)->createTransactionForBalanceAdjustment($context, $user, $log, $currency, $amount, Support::TRANSACTION_SOURCE_OUTGOING, Support::OUTGOING_TRANSACTION_TYPE_REDUCED_FROM_ADMIN, [
            'type_description' => [
                'phrase' => 'ewallet::phrase.reduced_from_your_wallet',
            ],
        ]);

        $this->sendSendingNotification($context, $user, $log);

        return true;
    }

    protected function sendSendingNotification(User $context, User $user, BalanceAdjustment $model): void
    {
        if ($context->entityId() === $user->entityId()) {
            return;
        }

        $params = [$user, new ReduceAmountFromBalanceNotification($model)];

        Notification::send(...$params);
    }

    protected function sendReducingNotification(User $context, User $user, BalanceAdjustment $model): void
    {
        if ($context->entityId() === $user->entityId()) {
            return;
        }

        $params = [$user, new SendAmountToBalanceNotification($model)];

        Notification::send(...$params);
    }

    public function manageAdjustmentHistories(User $context, User $user, array $attributes = []): Paginator
    {
        $userFullName = Arr::get($attributes, 'user_full_name');
        $ownerFullName = Arr::get($attributes, 'owner_full_name');
        $currency = Arr::get($attributes, 'currency');
        $type = Arr::get($attributes, 'type');
        $fromDate = Arr::get($attributes, 'from_date');
        $toDate = Arr::get($attributes, 'to_date');
        $fromAmount = Arr::get($attributes, 'from_amount');
        $toAmount = Arr::get($attributes, 'to_amount');
        $limit = (int) Arr::get($attributes, 'limit', 20);

        $builder = BalanceAdjustment::query()
            ->with(['userEntity', 'ownerEntity'])
            ->where([
                'emoney_balance_adjustments.owner_id' => $user->entityId(),
            ]);

        if (is_string($userFullName) && trim($userFullName) !== '') {
            $builder->join('users as user', function (JoinClause $clause) use ($userFullName) {
                $clause->where('user.full_name', $this->getLikeOperator(), '%' . $userFullName . '%');
            });
        }

        if (is_string($ownerFullName) && trim($ownerFullName) !== '') {
            $builder->join('users as owner', function (JoinClause $clause) use ($ownerFullName) {
                $clause->where('owner.full_name', $this->getLikeOperator(), '%' . $ownerFullName . '%');
            });
        }

        if (is_string($currency)) {
            $builder->where('emoney_balance_adjustments.currency', '=', $currency);
        }

        if (is_string($type)) {
            $builder->where('emoney_balance_adjustments.type', '=', $type);
        }

        if (is_string($fromDate)) {
            $builder->where('emoney_balance_adjustments.created_at', '>=', Carbon::parse($fromDate));
        }

        if (is_string($toDate)) {
            $builder->where('emoney_balance_adjustments.created_at', '<=', Carbon::parse($toDate));
        }

        if (is_numeric($fromAmount)) {
            $builder->where('emoney_balance_adjustments.amount', '>=', $fromAmount);
        }

        if (is_numeric($toAmount)) {
            $builder->where('emoney_balance_adjustments.amount', '<=', $toAmount);
        }

        return $builder->orderByDesc('emoney_balance_adjustments.id')
            ->paginate($limit, ['emoney_balance_adjustments.*']);
    }
}
