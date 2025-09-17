<?php

namespace MetaFox\ActivityPoint\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use MetaFox\ActivityPoint\Jobs\MonthExchangedAggregateJob;
use MetaFox\ActivityPoint\Models\ActionType;
use MetaFox\ActivityPoint\Models\ConversionRequest;
use MetaFox\ActivityPoint\Notifications\ApprovedConversionRequestNotification;
use MetaFox\ActivityPoint\Notifications\DeniedConversionRequestNotification;
use MetaFox\ActivityPoint\Notifications\PendingConversionRequestNotification;
use MetaFox\ActivityPoint\Repositories\ConversionRequestRepositoryInterface;
use MetaFox\ActivityPoint\Support\Facade\PointConversion;
use MetaFox\ActivityPoint\Support\PointConversion as Support;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class ConversionRequestRepository.
 */
class ConversionRequestRepository extends AbstractRepository implements ConversionRequestRepositoryInterface
{
    public function model()
    {
        return ConversionRequest::class;
    }

    public function createConversionRequest(User $user, int $points, string $currency = Support::DEFAULT_CONVERSION_RATE_CURRENCY_TO_MONEY): ConversionRequest
    {
        $total      = PointConversion::getConversionAmount($points, $currency);
        $commission = PointConversion::getCommissionFee($total);
        $actual     = round($total - $commission, 2);

        $attributes = [
            'user_id'    => $user->entityId(),
            'user_type'  => $user->entityType(),
            'points'     => $points,
            'currency'   => $currency,
            'total'      => $total,
            'commission' => $commission,
            'actual'     => $actual,
            'status'     => Support::TRANSACTION_STATUS_PENDING,
        ];

        /**
         * @var ConversionRequest $request
         */
        $request = $this->getModel()->newInstance($attributes);

        $request->save();

        $request->refresh();

        match ($user->hasPermissionTo('activitypoint_conversion_request.auto_approved')) {
            true    => $this->approveConversionRequest($user, $request),
            default => $this->sendPendingNotification($request)
        };

        return $request;
    }

    public function cancelConversionRequest(User $user, ConversionRequest $request): ConversionRequest
    {
        $request->update(['status' => Support::TRANSACTION_STATUS_CANCELLED]);

        return $request->refresh();
    }

    public function approveConversionRequest(User $user, ConversionRequest $request): ConversionRequest
    {
        $request->update(['status' => Support::TRANSACTION_STATUS_APPROVED]);

        if ($user->entityId() != $request->userId()) {
            $this->sendApprovedNotification($user, $request);
        }

        if ($request->user instanceof User) {
            app('events')->dispatch('activitypoint.decrease_user_point.custom', [$request->user, $request->points, 'activitypoint::phrase.convert_points_to_emoney', ActionType::ACTIVITYPOINT_CONVERT_POINTS_TO_EMONEY_TYPE], true);
        }

        app('events')->dispatch('ewallet.transaction.create', [$user, $request->user, $request, $request->currency, $request->actual, 0, 0]);

        MonthExchangedAggregateJob::dispatch($request->user, Carbon::parse($request->created_at)->startOfMonth());

        return $request->refresh();
    }

    public function denyConversionRequest(User $user, ConversionRequest $request, ?string $reason = null): ConversionRequest
    {
        $request->update([
            'status'        => Support::TRANSACTION_STATUS_DENIED,
            'denied_reason' => $reason,
        ]);

        if ($user->entityId() != $request->userId()) {
            $this->sendDeniedNotification($user, $request);
        }

        return $request->refresh();
    }

    private function sendDeniedNotification(User $user, ConversionRequest $request): void
    {
        if (!$request->user instanceof User || $request->user->isDeleted()) {
            return;
        }

        $notification = (new DeniedConversionRequestNotification($request))
            ->setContext($user);

        $params = [$request->user, $notification];

        Notification::send(...$params);
    }

    private function sendPendingNotification(ConversionRequest $request): void
    {
        if (!$request->user instanceof User || $request->user->isDeleted()) {
            return;
        }

        $superAdmins = resolve(UserRepositoryInterface::class)->getAllSuperAdmin();

        if ($superAdmins->isEmpty()) {
            return;
        }

        $notifiables = [];
        foreach ($superAdmins as $superAdmin) {
            if (!$superAdmin instanceof IsNotifiable) {
                continue;
            }

            $notifiables[] = $superAdmin;
        }

        if (empty($notifiables)) {
            return;
        }

        $notification = (new PendingConversionRequestNotification($request))
            ->setContext($request->user);

        $params = [$notifiables, $notification];

        Notification::send(...$params);
    }

    private function sendApprovedNotification(User $context, ConversionRequest $request): void
    {
        if (!$request->user instanceof User || $request->user->isDeleted()) {
            return;
        }

        $notification = (new ApprovedConversionRequestNotification($request))
            ->setContext($context);

        $params = [$request->user, $notification];

        Notification::send(...$params);
    }

    private function buildQuery(Builder $query, array $attributes): void
    {
        $status   = Arr::get($attributes, 'status');
        $fromDate = Arr::get($attributes, 'from_date');
        $toDate   = Arr::get($attributes, 'to_date');
        $creator  = Arr::get($attributes, 'creator');
        $id       = Arr::get($attributes, 'id');

        if (is_string($status) && MetaFoxConstant::EMPTY_STRING != $status) {
            $query->where('status', $status);
        }

        if (is_string($fromDate) && MetaFoxConstant::EMPTY_STRING != $fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }

        if (is_string($toDate) && MetaFoxConstant::EMPTY_STRING != $toDate) {
            $query->where('created_at', '<=', $toDate);
        }

        if (is_string($creator) && MetaFoxConstant::EMPTY_STRING != $creator) {
            $query->join('user_entities', function (JoinClause $joinClause) use ($creator) {
                $joinClause->on('user_entities.id', '=', 'apt_conversion_requests.user_id')
                    ->where('user_entities.name', $this->likeOperator(), '%' . $creator . '%');
            });
        }

        if (is_numeric($id)) {
            $query->where('apt_conversion_requests.id', $id);
        }

        $orderStatement = sprintf("
            CASE
                WHEN apt_conversion_requests.status = '%s' THEN 1
                ELSE 2
            END", Support::TRANSACTION_STATUS_PENDING);

        $query->orderByRaw(DB::raw($orderStatement)->getValue(DB::getQueryGrammar()))
            ->orderByDesc('apt_conversion_requests.id');
    }

    public function viewConversionRequests(User $user, array $attributes = []): Paginator
    {
        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query = $this->getModel()->newQuery()
            ->where([
                'user_id' => $user->entityId(),
            ]);

        $this->buildQuery($query, $attributes);

        return $query->paginate($limit, ['apt_conversion_requests.*']);
    }

    public function viewConversionRequestAdminCP(array $attributes = []): Paginator
    {
        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query = $this->getModel()->newQuery();

        $this->buildQuery($query, $attributes);

        return $query
            ->with(['userEntity'])
            ->paginate($limit, ['apt_conversion_requests.*']);
    }
}
