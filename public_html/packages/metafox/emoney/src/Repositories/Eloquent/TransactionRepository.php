<?php

namespace MetaFox\EMoney\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Facades\Payment;
use MetaFox\EMoney\Models\BalanceAdjustment;
use MetaFox\EMoney\Models\Transaction;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\EMoney\Notifications\ApprovedTransactionNotification;
use MetaFox\EMoney\Repositories\StatisticRepositoryInterface;
use MetaFox\EMoney\Repositories\TransactionRepositoryInterface;
use MetaFox\EMoney\Services\Contracts\ConversionRateServiceInterface;
use MetaFox\EMoney\Support\Browse\Scopes\GeneralScope;
use MetaFox\EMoney\Support\Support;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction as PaymentTransaction;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Helper\Pagination;

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

    public function createTransactionForBalanceAdjustment(User $user, User $owner, BalanceAdjustment $model, string $currency, float $total, string $source, string $type, ?array $extra = null): ?Transaction
    {
        $attributes = array_merge($this->buildTrackingTransactionData($user, $owner, $model, $currency, $total),
            [
                'source' => $source,
                'type'   => $type,
                'extra'  => $extra,
            ]
        );

        /**
         * @var Transaction $model
         */
        $model = $this->getModel()->newInstance($attributes);

        $model->save();

        $this->getStatisticRepository()->updateStatisticForBalanceAdjustment($owner, $model);

        return $model->refresh();
    }

    public function createTransactionForIntegration(User $user, User $owner, Entity $entity, string $currency, float $total, ?float $commissionPercentage = null, ?int $holdingDays = null, ?string $target = null): ?Transaction
    {
        return $this->createModel($user, $owner, $entity, $currency, $total, $commissionPercentage, $holdingDays, Support::TRANSACTION_SOURCE_INCOMING, Support::INCOMING_TRANSACTION_TYPE_RECEIVED, null, null, $target);
    }

    public function createTrackingTransaction(User $user, User $owner, Entity $entity, string $currency, float $total, string $source = Support::TRANSACTION_SOURCE_OUTGOING, string $type = Support::OUTGOING_TRANSACTION_TYPE_WITHDRAWN): Transaction
    {
        $attributes = array_merge(
            $this->buildTrackingTransactionData($user, $owner, $entity, $currency, $total),
            [
                'source' => $source,
                'type'   => $type,
            ]
        );

        /**
         * @var Transaction $model
         */
        $model = $this->getModel()->newInstance($attributes);

        $model->save();

        return $model->refresh();
    }

    public function createPendingTrackingWithdrawnTransaction(WithdrawRequest $withdrawRequest): Transaction
    {
        $attributes = array_merge(
            $this->buildTrackingTransactionData($withdrawRequest->user, $withdrawRequest->user, $withdrawRequest, $withdrawRequest->currency, $withdrawRequest->total),
            [
                'status' => Support::TRANSACTION_STATUS_PENDING,
                'source' => Support::TRANSACTION_SOURCE_OUTGOING,
                'type'   => Support::OUTGOING_TRANSACTION_TYPE_WITHDRAWN,
            ]
        );

        /**
         * @var Transaction $model
         */
        $model = $this->getModel()->newInstance($attributes);

        $model->save();

        return $model->refresh();
    }

    public function processWithdrawRequestCancelled(WithdrawRequest $withdrawRequest): void
    {
        $transaction = $this->getTransactionByWithdrawRequest($withdrawRequest);

        if ($transaction instanceof Transaction) {
            $transaction->update(['status' => Support::TRANSACTION_STATUS_APPROVED]);
        }

        $this->createCashbackWithdrawRequestTransaction($withdrawRequest);
    }

    public function processWithdrawRequestDenied(WithdrawRequest $withdrawRequest): void
    {
        $this->processWithdrawRequestCancelled($withdrawRequest);
    }

    public function processWithdrawRequestProcessed(WithdrawRequest $withdrawRequest): void
    {
        $transaction = $this->getTransactionByWithdrawRequest($withdrawRequest);

        if ($transaction instanceof Transaction) {
            $transaction->update(['status' => Support::TRANSACTION_STATUS_APPROVED]);

            return;
        }

        $this->createTrackingTransaction($withdrawRequest->user, $withdrawRequest->user, $withdrawRequest, $withdrawRequest->currency, $withdrawRequest->total);
    }

    protected function createCashbackWithdrawRequestTransaction(WithdrawRequest $withdrawRequest): ?Transaction
    {
        $attributes = $this->buildTrackingTransactionData(
            $withdrawRequest->user,
            $withdrawRequest->user,
            $withdrawRequest,
            $withdrawRequest->currency,
            $withdrawRequest->total,
        );

        $attributes = array_merge($attributes, [
            'source'     => Support::TRANSACTION_SOURCE_INCOMING,
            'type'       => Support::OUTGOING_TRANSACTION_TYPE_WITHDRAWN,
            'status'     => Support::TRANSACTION_STATUS_APPROVED,
            'actor_type' => $withdrawRequest->is_denied ? Support::TRANSACTION_ACTOR_TYPE_SYSTEM : Support::TRANSACTION_ACTOR_TYPE_USER,
        ]);

        /** @var Transaction $model */
        $model = $this->getModel()->newInstance($attributes);

        $model->save();

        return $model->refresh();
    }

    protected function getTransactionByWithdrawRequest(WithdrawRequest $withdrawRequest): ?Transaction
    {
        return $this->getModel()->newQuery()
            ->where([
                'owner_id'   => $withdrawRequest->user->entityId(),
                'owner_type' => $withdrawRequest->user->entityType(),
                'item_id'    => $withdrawRequest->entityId(),
                'item_type'  => $withdrawRequest->entityType(),
                'source'     => Support::TRANSACTION_SOURCE_OUTGOING,
                'type'       => Support::OUTGOING_TRANSACTION_TYPE_WITHDRAWN,
                'status'     => Support::TRANSACTION_STATUS_PENDING,
            ])->first();
    }

    protected function buildTrackingTransactionData(User $user, User $owner, Entity $entity, string $currency, float $total): array
    {
        $currentBalance = app('ewallet.statistic')->getUserBalance($owner, $currency);

        return [
            'user_id'               => $user->entityId(),
            'user_type'             => $user->entityType(),
            'owner_id'              => $owner->entityId(),
            'owner_type'            => $owner->entityType(),
            'item_id'               => $entity->entityId(),
            'item_type'             => $entity->entityType(),
            'module_id'             => PackageManager::getAliasForEntityType($entity->entityType()),
            'total_currency'        => $currency,
            'total_price'           => $total,
            'commission_currency'   => $currency,
            'commission_price'      => 0,
            'actual_currency'       => $currency,
            'actual_price'          => $total,
            'balance_price'         => $total,
            'balance_currency'      => $currency,
            'current_balance_price' => $currentBalance, /* @deprecated  Remove in 5.1.15 */
            'exchange_rate'         => 0,
            'available_at'          => Carbon::now(),
            'actor_type'            => Support::TRANSACTION_ACTOR_TYPE_USER,
            'status'                => Support::TRANSACTION_STATUS_APPROVED,
        ];
    }

    private function createModel(
        User $user,
        User $owner,
        Entity $entity,
        string $currency,
        float $total,
        ?float $commissionPercentage = null,
        ?int $holdingDays = null,
        string $source = Support::TRANSACTION_SOURCE_INCOMING,
        string $type = Support::INCOMING_TRANSACTION_TYPE_RECEIVED,
        ?string $outgoingOrderId = null,
        ?array $extra = null,
        ?string $target = null,
    ): ?Transaction {
        $commission = $this->getRateService()->getCommissionFee($total, $commissionPercentage);

        $actual = $total - $commission;

        if (null === $holdingDays) {
            $holdingDays = (int) Settings::get('ewallet.balance_holding_duration', 0);
        }

        $attributes = [
            'user_id'             => $user->entityId(),
            'user_type'           => $user->entityType(),
            'owner_id'            => $owner->entityId(),
            'owner_type'          => $owner->entityType(),
            'item_id'             => $entity->entityId(),
            'item_type'           => $entity->entityType(),
            'module_id'           => PackageManager::getAliasForEntityType($entity->entityType()),
            'total_currency'      => $currency,
            'total_price'         => $total,
            'commission_currency' => $currency,
            'commission_price'    => $commission,
            'actual_currency'     => $currency,
            'actual_price'        => $actual,
            'balance_price'       => $actual,
            'balance_currency'    => $currency,
            'exchange_rate'       => 0,
            'available_at'        => Carbon::now(),
            'actor_type'          => Support::TRANSACTION_ACTOR_TYPE_USER,
            'status'              => Support::TRANSACTION_STATUS_PENDING,
            'source'              => $source,
            'type'                => $type,
            'outgoing_order_id'   => $outgoingOrderId,
            'extra'               => $extra,
        ];

        if ($holdingDays > 0) {
            $attributes = array_merge($attributes, [
                'available_at' => $attributes['available_at']->addDays($holdingDays),
            ]);
        }

        if (null === $target) {
            $target = $this->getDefaultTargetCurrency();
        }

        if ($balanceParams = $this->getRateService()->getBalancePrice(price: $actual, base: $currency, target: $target)) {
            $attributes = array_merge($attributes, $balanceParams);
        }

        /**
         * @var Transaction $model
         */
        $model = $this->getModel()->newInstance($attributes);

        $model->save();

        $model->refresh();

        /*
         * We assume transaction is pending, so we update statistic for pending case
         */
        $this->getStatisticRepository()->updateTransactionStatistic($owner, $model);

        /*
         * if no pending days, then we update to be approved
         */
        if (!$holdingDays) {
            $this->approveTransaction($model);
        }

        return $model;
    }

    private function createTransactionModel(User $owner, array $attributes, ?int $holdingDays = null): ?Transaction
    {
        /**
         * @var Transaction $model
         */
        $model = $this->getModel()->newInstance($attributes);

        $model->save();

        $model->refresh();

        /*
         * We assume transaction is pending, so we update statistic for pending case
         */
        $this->getStatisticRepository()->updateTransactionStatistic($owner, $model);

        if (null === $holdingDays) {
            $holdingDays = (int) Settings::get('ewallet.balance_holding_duration', 0);
        }

        /*
         * if no pending days, then we update to be approved
         */
        if (!$holdingDays) {
            $this->approveTransaction($model);
        }

        return $model;
    }

    private function buildDataTransaction(
        User $user,
        User $owner,
        Entity $entity,
        string $currency,
        float $total,
        ?float $commissionPercentage = null,
        string $type = Support::INCOMING_TRANSACTION_TYPE_RECEIVED,
        ?string $outgoingOrderId = null,
        ?array $extra = null
    ): array {
        $commission = $this->getRateService()->getCommissionFee($total, $commissionPercentage);

        $actual = $total - $commission;

        return [
            'user_id'             => $user->entityId(),
            'user_type'           => $user->entityType(),
            'owner_id'            => $owner->entityId(),
            'owner_type'          => $owner->entityType(),
            'item_id'             => $entity->entityId(),
            'item_type'           => $entity->entityType(),
            'module_id'           => PackageManager::getAliasForEntityType($entity->entityType()),
            'total_currency'      => $currency,
            'total_price'         => $total,
            'commission_currency' => $currency,
            'commission_price'    => $commission,
            'actual_currency'     => $currency,
            'actual_price'        => $actual,
            'balance_price'       => $actual,
            'balance_currency'    => $currency,
            'exchange_rate'       => 0,
            'available_at'        => Carbon::now(),
            'actor_type'          => Support::TRANSACTION_ACTOR_TYPE_USER,
            'status'              => Support::TRANSACTION_STATUS_PENDING,
            'source'              => Support::TRANSACTION_SOURCE_OUTGOING,
            'type'                => $type,
            'outgoing_order_id'   => $outgoingOrderId,
            'extra'               => $extra,
        ];
    }

    private function buildDataIncomingTransaction(
        User $user,
        User $owner,
        Entity $entity,
        string $currency,
        float $total,
        ?array $extra = null
    ): array {
        $holdingDays = (int) Settings::get('ewallet.balance_holding_duration', 0);

        $attributes  = $this->buildDataTransaction($user, $owner, $entity, $currency, $total, $entity->commission_percentage, Support::INCOMING_TRANSACTION_TYPE_RECEIVED, null, $extra);

        $attributes  = array_merge($attributes, [
            'status'           => Support::TRANSACTION_STATUS_PENDING,
            'source'           => Support::TRANSACTION_SOURCE_INCOMING,
        ]);

        if ($holdingDays > 0) {
            $attributes = array_merge($attributes, [
                'available_at' => $attributes['available_at']->addDays($holdingDays),
            ]);
        }

        $target = $this->getDefaultTargetCurrency();

        if ($balanceParams = $this->getRateService()->getBalancePrice(price: Arr::get($attributes, 'actual_price'), base: $currency, target: $target)) {
            $attributes = array_merge($attributes, $balanceParams);
        }

        return $attributes;
    }

    protected function getDefaultTargetCurrency(): string
    {
        return app('currency')->getDefaultCurrencyId();
    }

    public function createOutgoingTransaction(Order $order, ?string $gatewayOrderId = null, array $extra = []): ?Transaction
    {
        if (!$order->user instanceof User) {
            return null;
        }

        if ($order->total < 0) {
            return null;
        }

        /**
         * TODO: Implement setting if need.
         */
        $commissionPercentage = 0;

        if (null === $gatewayOrderId) {
            $gatewayOrderId = Payment::generateOrderId($order);
        }

        $target = Emoney::getPaymentBalanceCurrency($order, $extra);

        $price = Arr::get($extra, 'price_payment', $order->total);

        $data = $this->buildDataTransaction($order->user, $order->user, $order->item, $target, $price, $commissionPercentage, Support::OUTGOING_TRANSACTION_TYPE_PURCHASED, $gatewayOrderId, $extra);

        return $this->createTransactionModel($order->user, $data, 0);
    }

    public function createIncomingTransaction(PaymentTransaction $transaction, array $extra = []): ?Transaction
    {
        if ($transaction->status != PaymentTransaction::STATUS_COMPLETED) {
            return null;
        }

        /**
         * @var Order $order
         */
        $order = $transaction->order;

        if (null === $order) {
            return null;
        }

        if (!$order->payee instanceof User) {
            return null;
        }

        $total = $transaction->amount;

        if ($total <= 0) {
            return null;
        }

        $data = $this->buildDataIncomingTransaction($transaction->user, $order->payee, $order->item, $transaction->currency, $total, $extra);

        return $this->createTransactionModel($order->payee, $data);
    }

    private function getStatisticRepository(): StatisticRepositoryInterface
    {
        return resolve(StatisticRepositoryInterface::class);
    }

    public function approveTransaction(Transaction $transaction): bool
    {
        $update = ['status' => Support::TRANSACTION_STATUS_APPROVED];

        $owner = $transaction->owner;

        if ($owner) {
            $userBalance = $this->getStatisticRepository()->getUserBalance($owner, $transaction->balance_currency);

            if (!is_numeric($userBalance)) {
                $userBalance = 0;
            }

            /*
             * @deprecated  Remove in 5.1.15
             */
            if ($transaction->source == Support::TRANSACTION_SOURCE_INCOMING) {
                $update['current_balance_price'] = $userBalance + $transaction->balance_price;
            } else {
                $update['current_balance_price'] = $userBalance > $transaction->balance_price ? $userBalance - $transaction->balance_price : 0;
            }
        }

        $transaction->update($update);

        $transaction->refresh();

        if ($owner) {
            $this->getStatisticRepository()->updateTransactionStatistic($owner, $transaction);

            /*
             * Only send approved notification when transaction is incoming from others who bought your items
             */
            if ($transaction->source == Support::TRANSACTION_SOURCE_INCOMING) {
                $this->sendApprovedNotification($owner, $transaction);
            }
        }

        return true;
    }

    protected function sendApprovedNotification(User $user, Transaction $transaction): void
    {
        $params = [$user, new ApprovedTransactionNotification($transaction)];

        Notification::send(...$params);
    }

    protected function getRateService(): ConversionRateServiceInterface
    {
        return resolve(ConversionRateServiceInterface::class);
    }

    public function viewTransactions(User $user, array $attributes = []): Paginator
    {
        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query = $this->createBuilder($attributes);

        return $query
            ->where([
                'emoney_transactions.owner_id' => $user->entityId(),
            ])
            ->with(['userEntity', 'package'])
            ->orderByDesc('emoney_transactions.id')
            ->paginate($limit, ['emoney_transactions.*']);
    }

    public function viewTransactionsAdminCP(array $attributes): Paginator
    {
        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query = $this->createBuilder($attributes);

        return $query
            ->with(['userEntity', 'ownerEntity'])
            ->orderByDesc('emoney_transactions.id')
            ->paginate($limit, ['emoney_transactions.*']);
    }

    private function createBuilder(array $attributes): Builder
    {
        $baseCurrency = Arr::get($attributes, 'base_currency');
        $status       = Arr::get($attributes, 'status');
        $fromDate     = Arr::get($attributes, 'from_date');
        $toDate       = Arr::get($attributes, 'to_date');
        $buyer        = Arr::get($attributes, 'buyer');
        $seller       = Arr::get($attributes, 'seller');
        $id           = Arr::get($attributes, 'id');
        $source       = Arr::get($attributes, 'source');
        $type         = Arr::get($attributes, 'type');

        $query = $this->getModel()->newQuery();

        /*
         * For mobile old version that is not improving layout
         */
        if (!Emoney::isUsingNewAlias()) {
            $source = Support::TRANSACTION_SOURCE_INCOMING;
        }

        if ($source) {
            $query->where(['emoney_transactions.source' => $source]);
        }

        if ($type) {
            $query->where(['emoney_transactions.type' => $type]);
        }

        if (is_numeric($id)) {
            $query->where('emoney_transactions.id', $id);
        }

        $this->applyBuyerFilter($query, $buyer);

        $this->applySellerFilter($query, $seller);

        if ($baseCurrency) {
            $query->where('emoney_transactions.total_currency', $baseCurrency);
        }

        $scope = new GeneralScope($fromDate, $toDate, $status);

        $query->addScope($scope);

        return $query;
    }

    protected function applyBuyerFilter(Builder $query, ?string $buyer): void
    {
        if (!is_string($buyer) || MetaFoxConstant::EMPTY_STRING == $buyer) {
            return;
        }

        $query->join('user_entities', function (JoinClause $joinClause) use ($buyer) {
            $joinClause->on('user_entities.id', '=', 'emoney_transactions.user_id')
                ->where('user_entities.name', $this->likeOperator(), '%' . $buyer . '%');
        });

        $query->where(function ($query) {
            $query->where(function ($subQuery) {
                $subQuery->where('emoney_transactions.source', Support::TRANSACTION_SOURCE_INCOMING)
                    ->whereColumn('emoney_transactions.user_id', '<>', 'emoney_transactions.owner_id');
            })->orWhere('emoney_transactions.source', '<>', Support::TRANSACTION_SOURCE_INCOMING);
        });
    }

    protected function applySellerFilter(Builder $query, ?string $seller): void
    {
        if (!is_string($seller) || MetaFoxConstant::EMPTY_STRING == $seller) {
            return;
        }

        $query->join('user_entities as owner_entities', function (JoinClause $joinClause) use ($seller) {
            $joinClause->on('owner_entities.id', '=', 'emoney_transactions.owner_id')
                ->where('owner_entities.name', $this->likeOperator(), '%' . $seller . '%');
        });

        $query->where(function ($query) {
            $query->where(function ($subQuery) {
                $subQuery->where('emoney_transactions.source', Support::TRANSACTION_SOURCE_OUTGOING)
                    ->whereColumn('emoney_transactions.user_id', '<>', 'emoney_transactions.owner_id');
            })->orWhere('emoney_transactions.source', '<>', Support::TRANSACTION_SOURCE_OUTGOING);
        });
    }
}
