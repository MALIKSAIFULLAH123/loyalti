<?php

namespace MetaFox\EMoney\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\EMoney\Contracts\WithdrawMethodInterface;
use MetaFox\EMoney\Models\WithdrawRequestReason;
use MetaFox\EMoney\Notifications\DeniedWithdrawRequestNotification;
use MetaFox\EMoney\Notifications\SuccessPaymentRequestNotification;
use MetaFox\EMoney\Repositories\StatisticRepositoryInterface;
use MetaFox\EMoney\Repositories\TransactionRepositoryInterface;
use MetaFox\EMoney\Repositories\WithdrawRequestReasonRepositoryInterface;
use MetaFox\EMoney\Services\Contracts\WithdrawServiceInterface;
use MetaFox\EMoney\Support\Browse\Scopes\GeneralScope;
use MetaFox\EMoney\Support\Support;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\EMoney\Repositories\WithdrawRequestRepositoryInterface;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class WithdrawRequestRepository.
 */
class WithdrawRequestRepository extends AbstractRepository implements WithdrawRequestRepositoryInterface
{
    use CollectTotalItemStatTrait;

    public function model()
    {
        return WithdrawRequest::class;
    }

    public function viewRequests(User $user, array $attributes = []): Paginator
    {
        $limit    = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $fromDate = Arr::get($attributes, 'from_date');
        $toDate   = Arr::get($attributes, 'to_date');
        $status   = Arr::get($attributes, 'status');
        $scope    = new GeneralScope($fromDate, $toDate, $status);
        $id       = Arr::get($attributes, 'id');

        /**
         * @var Builder $query
         */
        $query = $this->getModel()->newQuery()
            ->addScope($scope)
            ->where('emoney_withdraw_requests.user_id', $user->entityId());

        if (is_numeric($id)) {
            $query->where('emoney_withdraw_requests.id', $id);
        }

        $orderStatement = sprintf("
            CASE
                WHEN emoney_withdraw_requests.status = '%s' THEN 1
                WHEN emoney_withdraw_requests.status = '%s' THEN 2
                ELSE 3
            END", Support::WITHDRAW_STATUS_WAITING_CONFIRMATION, Support::WITHDRAW_STATUS_PENDING);

        return $query
            ->with(['withdrawMethod', 'reason'])
            ->orderBy(DB::raw($orderStatement))
            ->orderByDesc('emoney_withdraw_requests.id')
            ->simplePaginate($limit, ['emoney_withdraw_requests.*']);
    }

    public function manageRequests(User $user, array $attributes = []): Paginator
    {
        $limit    = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $creator  = Arr::get($attributes, 'creator');
        $fromDate = Arr::get($attributes, 'from_date');
        $toDate   = Arr::get($attributes, 'to_date');
        $status   = Arr::get($attributes, 'status');
        $scope    = new GeneralScope($fromDate, $toDate, $status);
        $id       = Arr::get($attributes, 'id');

        $query = $this->getModel()->newQuery()
            ->addScope($scope);

        if (is_numeric($id)) {
            $query->where('emoney_withdraw_requests.id', $id);
        }

        if (is_string($creator) && MetaFoxConstant::EMPTY_STRING != $creator) {
            $query->join('user_entities', function (JoinClause $joinClause) use ($creator) {
                $joinClause->on('user_entities.id', '=', 'emoney_withdraw_requests.user_id')
                    ->where('user_entities.name', $this->likeOperator(), '%' . $creator . '%');
            });
        }

        $orderStatement = sprintf("
            CASE
                WHEN emoney_withdraw_requests.status = '%s' THEN 1
                WHEN emoney_withdraw_requests.status = '%s' THEN 2
                ELSE 3
            END", Support::WITHDRAW_STATUS_PENDING, Support::WITHDRAW_STATUS_WAITING_CONFIRMATION);

        return $query
            ->with(['userEntity', 'withdrawMethod', 'reason'])
            ->orderBy(DB::raw($orderStatement))
            ->orderByDesc('emoney_withdraw_requests.id')
            ->paginate($limit, ['emoney_withdraw_requests.*']);
    }

    public function createRequest(User $user, string $currency, float $amount, string $withdrawService): WithdrawRequest
    {
        $attributes = [
            'user_id'          => $user->entityId(),
            'user_type'        => $user->entityType(),
            'currency'         => $currency,
            'amount'           => $amount,
            'total'            => $amount,
            'fee'              => 0,
            'withdraw_service' => $withdrawService,
            'status'           => Support::WITHDRAW_STATUS_PENDING,
        ];

        $attributes = $this->applyWithdrawFee($attributes);

        $request = $this->getModel()->newInstance($attributes);

        $request->save();

        $request->refresh();

        $this->statisticRepository()->updatePendingWithdrawStatistic($request);

        $this->transactionRepository()->createPendingTrackingWithdrawnTransaction($request);

        return $request;
    }

    protected function applyWithdrawFee(array $attributes): array
    {
        $feePercentage = (float) Settings::get('ewallet.withdraw_fee', 0);

        if ($feePercentage <= 0) {
            return $attributes;
        }

        $total = Arr::get($attributes, 'total');

        $fee = round($total * ($feePercentage / 100), 2);

        return array_merge($attributes, [
            'amount' => round($total - $fee, 2),
            'fee'    => $fee,
        ]);
    }

    public function cancelRequest(User $user, WithdrawRequest $request, ?string $reason = null)
    {
        $request->update([
            'status' => Support::WITHDRAW_STATUS_CANCELLED,
        ]);

        if (is_string($reason) && MetaFoxConstant::EMPTY_STRING != $reason) {
            $this->addReason($request, $reason, Support::WITHDRAW_REQUEST_REASON_TYPE_CANCEL);
        }

        $this->statisticRepository()->updateCancelledWithdrawStatistic($request);

        $this->transactionRepository()->processWithdrawRequestCancelled($request);

        return true;
    }

    public function denyRequest(User $user, WithdrawRequest $request, ?string $reason = null)
    {
        $request->update([
            'status' => Support::WITHDRAW_STATUS_DENIED,
        ]);

        if (is_string($reason) && MetaFoxConstant::EMPTY_STRING != $reason) {
            $this->addReason($request, $reason, Support::WITHDRAW_REQUEST_REASON_TYPE_DENY);
        }

        if ($user->entityId() != $request->userId()) {
            $this->sendDeniedNotification($user, $request);
        }

        $this->statisticRepository()->updateDeniedWithdrawStatistic($request);

        $this->transactionRepository()->processWithdrawRequestDenied($request);

        return true;
    }

    public function approveRequest(User $user, WithdrawRequest $request): ?array
    {
        try {
            $status = Support::WITHDRAW_STATUS_PROCESSING;

            /**
             * @var WithdrawMethodInterface $provider
             */
            $provider = resolve(WithdrawServiceInterface::class)->getServiceProvider($request->withdraw_service);

            if ($provider->waitForConfirmation($request)) {
                $status = Support::WITHDRAW_STATUS_WAITING_CONFIRMATION;
            }

            $result = $this->processRequest($request, $provider, $status);

            if (is_array($result) && count($result)) {
                $request->update(['status' => $status]);
            }

            return $result;
        } catch (\Throwable $throwable) {
        }

        return null;
    }

    private function processRequest(WithdrawRequest $request, WithdrawMethodInterface $provider, string $status): ?array
    {
        $localeId = null;

        if ($request->user instanceof \MetaFox\User\Models\User) {
            $localeId = $request->user->profile?->language_id;
        }

        return match ($status) {
            Support::WITHDRAW_STATUS_PROCESSING => $provider->placeOrder($request->user, $request, [
                'cancel_url'  => url_utility()->makeApiFullUrl('admincp/ewallet/request/browse'),
                'return_url'  => url_utility()->makeApiFullUrl('admincp/ewallet/request/browse?id=' . $request->entityId()),
                'description' => __p('ewallet::phrase.payment_withdraw_request_description', [
                    'site_title' => Settings::get('core.general.site_name', 'MetaFox'),
                ], $localeId),
            ]),
            Support::WITHDRAW_STATUS_WAITING_CONFIRMATION => ['withdraw_status' => $status],
            default                                       => null,
        };
    }

    private function sendDeniedNotification(User $context, WithdrawRequest $request): void
    {
        $notifiable = $request->user;

        if (null === $notifiable) {
            return;
        }

        $notification = new DeniedWithdrawRequestNotification($request);

        $notification->setContext($context);

        $params = [$notifiable, $notification];

        Notification::send(...$params);
    }

    private function addReason(WithdrawRequest $request, string $reason, string $type): WithdrawRequestReason
    {
        return resolve(WithdrawRequestReasonRepositoryInterface::class)->createReason($request, $reason, $type);
    }

    public function paymentRequest(User $user, WithdrawRequest $request): ?array
    {
        try {
            /**
             * @var WithdrawMethodInterface $provider
             */
            $provider = resolve(WithdrawServiceInterface::class)->getServiceProvider($request->withdraw_service);

            return $this->processRequest($request, $provider, Support::WITHDRAW_STATUS_PROCESSING);
        } catch (\Throwable $throwable) {
        }

        return null;
    }

    public function updateSuccessPayment(Order $order, Transaction $transaction): bool
    {
        /**
         * @var null|WithdrawRequest $request
         */
        $request = $order->item;

        if (null === $request) {
            return false;
        }

        if ($request->is_processed) {
            return true;
        }

        $request->update([
            'status'         => Support::WITHDRAW_STATUS_PROCESSED,
            'transaction_id' => $transaction->gateway_transaction_id,
            'processed_at'   => Carbon::now(),
        ]);

        $this->statisticRepository()->updatePaidWithdrawStatistic($request);

        $this->sendSuccessPaymentNotification($request);

        $this->transactionRepository()->processWithdrawRequestProcessed($request);

        return true;
    }

    protected function sendSuccessPaymentNotification(WithdrawRequest $request): void
    {
        if (!$request->user instanceof User) {
            return;
        }

        $notification = new SuccessPaymentRequestNotification($request);

        $params = [$request->user, $notification];

        Notification::send(...$params);
    }

    protected function transactionRepository(): TransactionRepositoryInterface
    {
        return resolve(TransactionRepositoryInterface::class);
    }

    protected function statisticRepository(): StatisticRepositoryInterface
    {
        return resolve(StatisticRepositoryInterface::class);
    }
}
