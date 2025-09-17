<?php

namespace MetaFox\Group\Repositories\Eloquent;

use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Request;
use MetaFox\Group\Notifications\AcceptRequestNotification;
use MetaFox\Group\Notifications\PendingRequestNotification;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Policies\MemberPolicy;
use MetaFox\Group\Policies\RequestPolicy;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Repositories\RequestRepositoryInterface;
use MetaFox\Group\Support\Browse\Scopes\GroupMember\ViewScope;
use MetaFox\Group\Support\Browse\Scopes\Request\StatusScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class RequestRepository.
 * @method Request getModel()
 * @method Request find($id, $columns = ['*'])
 *
 * @ignore
 */
class RequestRepository extends AbstractRepository implements RequestRepositoryInterface
{
    use UserMorphTrait;

    public function model(): string
    {
        return Request::class;
    }

    public function __construct(
        Application $app,
        protected GroupRepositoryInterface $groupRepository,
        protected MemberRepositoryInterface $memberRepository
    ) {
        parent::__construct($app);
    }

    public function viewRequests(User $context, array $attributes): Paginator
    {
        $group = $this->groupRepository->find($attributes['group_id']);

        policy_authorize(GroupPolicy::class, 'managePendingRequestTab', $context, $group);

        $query = $this->buildViewRequestsQuery($attributes);

        return $query->select('group_requests.*')
            ->with(['user', 'group'])
            ->reorder('group_requests.id', 'desc')
            ->paginate($attributes['limit']);
    }

    public function buildViewRequestsQuery(array $attributes): Builder
    {
        $groupId   = Arr::get($attributes, 'group_id');
        $search    = Arr::get($attributes, 'q', '');
        $view      = Arr::get($attributes, 'view', ViewScope::VIEW_ALL);
        $status    = Arr::get($attributes, 'status');
        $startDate = Arr::get($attributes, 'start_date');
        $endDate   = Arr::get($attributes, 'end_date');

        $query = $this->getModel()->newQuery();

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['full_name'], 'users'));
        }

        if ($startDate) {
            $query->whereNotNull('group_requests.created_at')
                ->where('group_requests.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereNotNull('group_requests.created_at')
                ->where('group_requests.created_at', '<=', $endDate);
        }

        $viewScope = new ViewScope();
        $viewScope->setView($view)->setGroupId($groupId);

        if ($status !== null) {
            $statusScope = new StatusScope();
            $statusScope->setStatus($status);

            $query->addScope($statusScope);
        }

        return $query
            ->addScope($viewScope);
    }

    public function acceptMemberRequest(User $context, int $groupId, int $userId): Request
    {
        $group = $this->groupRepository->find($groupId);

        /** @var Request $request */
        $request = $this->getRequestByUserGroupId($userId, $group->entityId(), StatusScope::STATUS_PENDING);

        if (null == $request) {
            throw ValidationException::withMessages([
                __p('group::validation.the_request_join_group_does_not_exist'),
            ]);
        }

        policy_authorize(RequestPolicy::class, 'approve', $context, $request);

        $notification = new AcceptRequestNotification($request);
        $notification->setUser($context->userEntity);

        Notification::send($request->user, $notification);

        $request->update([
            'status_id'     => StatusScope::STATUS_APPROVED,
            'reviewer_id'   => $context->entityId(),
            'reviewer_type' => $context->entityType(),
        ]);

        $request->refresh();

        $notification = new PendingRequestNotification($this);
        $this->removeNotificationForPendingRequest($notification->getType(), $request->entityId(), $request->entityType());

        return $request;
    }

    /**
     * @deprecated Need remove for some next version
     */
    public function denyMemberRequest(User $context, int $groupId, int $userId): Request
    {
        $group = $this->groupRepository->find($groupId);

        /** @var Request $request */
        $request = $this->getRequestByUserGroupId($userId, $group->entityId(), StatusScope::STATUS_PENDING);

        if (null == $request) {
            throw ValidationException::withMessages([
                __p('group::validation.the_request_join_group_does_not_exist'),
            ]);
        }

        policy_authorize(RequestPolicy::class, 'approve', $context, $request);

        $request->update([
            'reviewer_id'   => $context->entityId(),
            'reviewer_type' => $context->entityType(),
            'status_id'     => StatusScope::STATUS_DENIED,
        ]);

        $request->refresh();

        $notification = new PendingRequestNotification($this);
        $this->removeNotificationForPendingRequest($notification->getType(), $request->entityId(), $request->entityType());

        return $request;
    }

    public function cancelRequest(User $context, int $groupId): bool
    {
        $group = $this->groupRepository->find($groupId);

        policy_authorize(MemberPolicy::class, 'joinGroup', $context, $group);

        $request = $this->getRequestByUserGroupId($context->entityId(), $group->entityId(), StatusScope::STATUS_PENDING);

        if ($request instanceof Request) {
            $request?->update(['status_id' => StatusScope::STATUS_CANCEL]);

            $notification = new PendingRequestNotification($this);
            $this->removeNotificationForPendingRequest($notification->getType(), $request->entityId(), $request->entityType());
        }

        return true;
    }

    public function getRequestByUserGroupId(int $userId, int $groupId, int $statusId): ?Model
    {
        return LoadReduce::get(
            sprintf('group::getRequestByUserGroupId(user:%s,group:%s,status_id:%s)', $userId, $groupId, $statusId),
            fn () => $this->getModel()->newQuery()
                ->where('user_id', $userId)
                ->where('status_id', $statusId)
                ->where('group_id', $groupId)->first()
        );
    }

    public function handelRequestJoinGroup(int $groupId, User $user): void
    {
        $data = [
            'group_id'  => $groupId,
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
        ];

        /** @var Request $request */
        $request = $this->getModel()->newQuery()->where($data)->first();
        $request?->delete();
    }

    public function removeNotificationForPendingRequest(string $notificationType, int $itemId, string $itemType): void
    {
        app('events')->dispatch(
            'notification.delete_notification_by_type_and_item',
            [$notificationType, $itemId, $itemType],
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function getBuilderPendingRequests(Group $group): Builder
    {
        return $this->getModel()->newQuery()
            ->where('group_id', $group->entityId())
            ->where('status_id', StatusScope::STATUS_PENDING);
    }

    public function acceptRequest(User $context, Request $request): Request
    {
        $request->update([
            'status_id'     => StatusScope::STATUS_APPROVED,
            'reviewer_id'   => $context->entityId(),
            'reviewer_type' => $context->entityType(),
        ]);

        $request->refresh();

        Notification::send($request->user, new AcceptRequestNotification($request));

        $notification = new PendingRequestNotification($request);
        $this->removeNotificationForPendingRequest($notification->getType(), $request->entityId(), $request->entityType());

        return $request;
    }

    public function declineRequest(User $context, Request $request, array $params = []): Request
    {
        $request->update([
            'reviewer_id'   => $context->entityId(),
            'reviewer_type' => $context->entityType(),
            'status_id'     => StatusScope::STATUS_DENIED,
            'reason'        => Arr::get($params, 'reason'),
        ]);

        $request->refresh();

        $notification = new PendingRequestNotification($request);
        $this->removeNotificationForPendingRequest($notification->getType(), $request->entityId(), $request->entityType());

        if (Arr::get($params, 'has_send_notification')) {
            $this->sendDeniedNotification($context, $request);
        }

        return $request;
    }

    protected function sendDeniedNotification(User $context, Request $request): void
    {
        [$notifiable, $notification] = $request->toDeniedNotification();

        $notification->setContext($context);

        Notification::send($notifiable, $notification);
    }
}
