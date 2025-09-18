<?php

namespace MetaFox\Group\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\GroupInviteCode;
use MetaFox\Group\Models\Invite;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\BlockRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\InviteRepositoryInterface;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Repositories\MuteRepositoryInterface;
use MetaFox\Group\Repositories\RequestRepositoryInterface;
use MetaFox\Group\Support\Browse\Scopes\Invite\StatusScope as InviteStatusScope;
use MetaFox\Group\Support\Browse\Scopes\Invite\ViewScope;
use MetaFox\Group\Support\Browse\Scopes\Request\StatusScope;
use MetaFox\Group\Support\InviteType;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\User\Models\UserEntity as UserEntityModel;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class InviteRepository.
 * @method Invite getModel()
 * @method Invite find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 *
 * @inore
 */
class InviteRepository extends AbstractRepository implements InviteRepositoryInterface
{
    use IsFriendTrait;
    use UserMorphTrait;

    public function model(): string
    {
        return Invite::class;
    }

    /**
     * @return GroupRepositoryInterface
     */
    private function groupRepository(): GroupRepositoryInterface
    {
        return resolve(GroupRepositoryInterface::class);
    }

    /**
     * @return MemberRepositoryInterface
     */
    private function groupMemberRepository(): MemberRepositoryInterface
    {
        return resolve(MemberRepositoryInterface::class);
    }

    /**
     * @return RequestRepositoryInterface
     */
    private function memberRequestRepository(): RequestRepositoryInterface
    {
        return resolve(RequestRepositoryInterface::class);
    }

    /**
     * @return BlockRepositoryInterface
     */
    private function memberBlockRepository(): BlockRepositoryInterface
    {
        return resolve(BlockRepositoryInterface::class);
    }

    public function inviteFriends(User $context, int $groupId, array $userIds): void
    {
        $group = $this->groupRepository()->find($groupId);
        /** @var UserEntityModel[] $users */
        $users = UserEntity::getByIds($userIds);

        foreach ($users as $user) {
            $this->inviteFriend($context, $group, $user->detail, null);
        }
    }

    public function handelInviteLeaveGroup(int $groupId, User $user, bool $notInviteAgain): bool
    {
        $data = [
            'group_id'   => $groupId,
            'owner_id'   => $user->entityId(),
            'owner_type' => $user->entityType(),
        ];

        /** @var Invite $invite */
        $invite = $this->getPendingInvite($groupId, $user);
        if ($invite instanceof Invite) {
            $invite->update(['status_id' => $notInviteAgain ? Invite::STATUS_NOT_INVITE_AGAIN : Invite::STATUS_NOT_USE]);
        }

        if ($notInviteAgain && null == $invite) {
            $invite = (new Invite(array_merge($data, [
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
                'status_id' => Invite::STATUS_NOT_INVITE_AGAIN,
            ])));
            $invite->save();
        }

        if (!empty($invite)) {
            app('events')->dispatch(
                'notification.delete_notification_by_type_and_item',
                ['group_invite', $invite->entityId(), $invite->entityType()],
                true
            );
        }

        return true;
    }

    public function handelInviteJoinGroup(int $groupId, User $user): void
    {
        $data = [
            'group_id'   => $groupId,
            'owner_id'   => $user->entityId(),
            'owner_type' => $user->entityType(),
        ];

        /** @var Invite $invite */
        $invite = $this->getModel()->newQuery()->where($data)->first();
        $invite?->update(['status_id' => Invite::STATUS_APPROVED]);
    }

    /**
     * @param User $context
     * @param int  $groupId
     * @param int  $userId
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteGroupInvite(User $context, int $groupId, int $userId): bool
    {
        /** @var Invite $invite */
        $invite = $this->getModel()->newQuery()
            ->with(['group'])
            ->where('group_id', $groupId)
            ->where('owner_id', $userId)
            ->where('status_id', Invite::STATUS_PENDING)
            ->firstOrFail();

        $canDelete = policy_check(GroupPolicy::class, 'viewInvitedOrBlocked', $context, $invite->group)
            || $context->entityId() == $invite->ownerId();

        if (!$canDelete) {
            throw new AuthorizationException(__p('core::validation.this_action_is_unauthorized'), 403);
        }

        return (bool) $invite->delete();
    }

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function updateInvite(User $context, array $attributes): bool
    {
        $groupId = Arr::get($attributes, 'group_id');
        $userId  = Arr::get($attributes, 'user_id');
        $status  = Arr::get($attributes, 'status_id');

        if (!$groupId || !$userId || $status == null) {
            return false;
        }

        /** @var Invite $invite */
        $invite = $this->getModel()->newQuery()
            ->with(['group'])
            ->where('group_id', $groupId)
            ->where('owner_id', $userId)
            ->where('status_id', Invite::STATUS_PENDING)
            ->firstOrFail();

        $canCancel = policy_check(GroupPolicy::class, 'viewInvitedOrBlocked', $context, $invite->group)
            || $context->entityId() == $invite->ownerId();

        if (!$canCancel) {
            throw new AuthorizationException(__p('core::validation.this_action_is_unauthorized'), 403);
        }

        return (bool) $invite->update(['status_id' => $status]);
    }

    public function viewInvites(User $context, array $attributes): Paginator
    {
        $limit   = Arr::get($attributes, 'limit');
        $groupId = Arr::get($attributes, 'group_id');

        $query = $this->getModel()->newQuery();
        $group = $this->groupRepository()->find($groupId);

        policy_authorize(GroupPolicy::class, 'viewInvitedOrBlocked', $context, $group);
        $query = $this->buildViewInviteQuery($context, $query, $attributes);

        $query->with(['userEntity', 'ownerEntity'])
            ->where('group_id', $groupId);

        $query->join('users as invitee', 'invitee.id', '=', 'group_invites.owner_id');

        return $query->select('group_invites.*')->paginate($limit);
    }

    /**
     * @param User    $context
     * @param Builder $query
     * @param array   $attributes
     * @return Builder
     */
    protected function buildViewInviteQuery(User $context, Builder $query, array $attributes): Builder
    {
        $view        = Arr::get($attributes, 'view', ViewScope::VIEW_ALL);
        $status      = Arr::get($attributes, 'status');
        $search      = Arr::get($attributes, 'q', '');
        $sort        = Arr::get($attributes, 'sort', SortScope::SORT_DEFAULT);
        $sortType    = Arr::get($attributes, 'sort_type', SortScope::SORT_TYPE_DEFAULT);
        $createdFrom = Arr::get($attributes, 'created_from');
        $createdTo   = Arr::get($attributes, 'created_to');

        if ($search != '') {
            $query->join('users as inviter', 'inviter.id', '=', 'group_invites.user_id');

            $query->where(function (Builder $builder) use ($search) {
                $builder->where('invitee.full_name', $this->likeOperator(), '%' . $search . '%')
                    ->orWhere('invitee.user_name', $this->likeOperator(), '%' . $search . '%');

                $builder->orWhere('inviter.full_name', $this->likeOperator(), '%' . $search . '%')
                    ->orWhere('inviter.user_name', $this->likeOperator(), '%' . $search . '%');
            });
        }

        if ($createdFrom) {
            $query->where('group_invites.created_at', '>=', $createdFrom);
        }

        if ($createdTo) {
            $query->where('group_invites.created_at', '<=', $createdTo);
        }

        $viewScope = new ViewScope();
        $viewScope->setView($view);

        if ($status) {
            $statusScope = new InviteStatusScope();
            $statusScope->setStatus($status);
            $query->addScope($statusScope);
        }

        if ($sort == SortScope::SORT_DEFAULT) {
            $query->orderBy('group_invites.created_at', $sortType);
        }

        return $query->addScope($viewScope);
    }

    public function getInvite(int $groupId, User $user, string $inviteType = InviteType::INVITED_MEMBER): ?Invite
    {
        /** @var Invite $invite */
        $invite = $this->getModel()->newQuery()
            ->where([
                'group_id'    => $groupId,
                'invite_type' => $inviteType,
                'owner_id'    => $user->entityId(),
                'owner_type'  => $user->entityType(),
            ])->first();

        return $invite;
    }

    /**
     * @param int         $groupId
     * @param User        $user
     * @param string|null $inviteType
     *
     * @return Invite|null
     */
    public function getPendingInvite(int $groupId, User $user, string $inviteType = null): ?Invite
    {
        $data  = [
            'group_id'   => $groupId,
            'owner_id'   => $user->entityId(),
            'owner_type' => $user->entityType(),
            'status_id'  => Invite::STATUS_PENDING,
        ];
        $query = $this->getModel()->newModelQuery()->with(['userEntity', 'ownerEntity']);

        if ($inviteType != null) {
            $data['invite_type'] = $inviteType;
            if ($inviteType != InviteType::INVITED_GENERATE_LINK) {
                $query->whereNull('code');
            }
        }

        /** @var Invite $invite */
        $invite = $query->where($data)
            ->where(function (Builder $q) {
                $q->where('expired_at', '>=', Carbon::now()->toDateTimeString())
                    ->orWhere('expired_at', '=', null);
            })->first();

        return $invite;
    }

    /**
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function acceptInvite(Group $group, User $user): bool
    {
        $invite = $this->getPendingInvite($group->entityId(), $user);
        if (null == $invite) {
            return false;
        }
        $invite->update([
            'status_id' => Invite::STATUS_APPROVED,
        ]);

        $result = match ($invite->getInviteType()) {
            InviteType::INVITED_ADMIN_GROUP     => $this->groupMemberRepository()->updateGroupRole(
                $group,
                $user->entityId(),
                Member::ADMIN
            ),
            InviteType::INVITED_MODERATOR_GROUP => $this->groupMemberRepository()->updateGroupRole(
                $group,
                $user->entityId(),
                Member::MODERATOR
            ),
            default                             => $this->handleAcceptInviteByGroupPrivacy($group, $user),
        };

        if ($result) {
            $this->handleAfterAcceptInvite($group, $user, $invite);
        }

        return $result;
    }

    /**
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    private function handleAcceptInviteByGroupPrivacy(Group $group, User $user): bool
    {
        $group->decrementAmount('total_invite');
        if (!$group->isSecretPrivacy()) {
            return $this->groupMemberRepository()->addGroupMember($group, $user->entityId());
        }

        if ($this->groupRepository()->hasGroupQuestionsConfirmation($group)) {
            return true;
        }

        $this->groupMemberRepository()->createRequest($user, $group->entityId());

        return true;
    }

    /**
     * @throws AuthorizationException
     */
    public function declineInvite(Group $group, User $user): bool
    {
        $invite = $this->getPendingInvite($group->entityId(), $user);

        if (null == $invite) {
            return false;
        }

        $notificationType = $this->getNotificationType($invite);

        match (null != $notificationType) {
            true    => $this->handleDeleteNotification($invite, $notificationType),
            default => $group->decrementAmount('total_invite'),
        };

        return $invite->update(['status_id' => Invite::STATUS_NOT_USE]);
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function inviteAdminOrModerator(User $context, int $groupId, array $userIds, string $inviteType): void
    {
        $group = $this->groupRepository()->find($groupId);
        policy_authorize(GroupPolicy::class, 'update', $context, $group);

        /** @var UserEntityModel[] $users */
        $users = UserEntity::getByIds($userIds);
        foreach ($users as $user) {
            if (!$group->isMember($user->detail)) {
                if (count($userIds) == 1) {
                    $message = json_encode([
                        'title'   => __p('group::phrase.add_role_failed', ['role' => $inviteType]),
                        'message' => __p(
                            'group::phrase.there_was_an_error_adding_the_group_role',
                            ['role' => $inviteType]
                        ),
                    ]);
                    abort(403, $message);
                }

                continue;
            }
            $this->handleCreateInvite($context, $user->detail, $groupId, $inviteType);
        }
    }

    public function createInvite(User $context, array $attributes): void
    {
        $model = new Invite();

        $model->fill($attributes);
        $model->save();
    }

    private function handleCreateInvite(
        User    $context,
        mixed   $user,
        int     $groupId,
        string  $inviteType,
        ?string $code = null,
        ?string $expired = null
    ): void
    {
        if (!$user instanceof User) {
            return;
        }

        $expired = $this->handleExpiredInvite($inviteType, $expired);
        $invite  = $this->getModel()->newQuery()
            ->where([
                'group_id' => $groupId,
                'owner_id' => $user->entityId(),
            ])
            ->latest('created_at')
            ->first();

        $newData = [
            'user_id'     => $context->entityId(),
            'user_type'   => $context->entityType(),
            'expired_at'  => $expired,
            'status_id'   => Invite::STATUS_PENDING,
            'group_id'    => $groupId,
            'invite_type' => $inviteType,
            'owner_id'    => $user->entityId(),
            'owner_type'  => $user->entityType(),
            'code'        => $code,
        ];

        if ($invite instanceof Invite && $invite->isPending()) {
            $invite->update([
                'status_id' => Invite::STATUS_NOT_USE,
            ]);
        }

        $this->createInvite($context, $newData);
    }

    /**
     * @inheritDoc
     */
    public function getMessageAcceptInvite(Group $group, User $user, ?string $inviteType = null): string
    {
        return match ($inviteType) {
            InviteType::INVITED_ADMIN_GROUP     => __p('group::phrase.you_are_now_a_admin_for_the_group'),
            InviteType::INVITED_MODERATOR_GROUP => __p('group::phrase.you_are_now_a_moderate_for_the_group'),
            InviteType::INVITED_GENERATE_LINK   => $this->groupMemberRepository()->handleMessageCreatedRequest($group, $user),
            default                             => match ($group->isSecretPrivacy()) {
                true  => $this->groupMemberRepository()->handleMessageCreatedRequest($group, $user),
                false => __p('group::phrase.you_joined', ['group' => $group->toTitle()]),
            }
        };
    }

    /**
     * @inheritDoc
     */
    public function getPendingInvites(Group $group, string $inviteType = InviteType::INVITED_MEMBER)
    {
        return $this->getBuilderPendingInvites($group, $inviteType)->get();
    }

    /**
     * @inheritDoc
     */
    public function getBuilderPendingInvites(Group $group, string $inviteType = InviteType::INVITED_MEMBER): Builder
    {
        $query = $this->getModel()->newQuery()
            ->where('group_id', $group->entityId())
            ->where('status_id', Invite::STATUS_PENDING)
            ->where(function ($q) {
                $q->whereDate('expired_at', '>=', Carbon::now()->toDateTimeString())
                    ->orWhere('expired_at', '=', null);
            });

        $inviteMemberTypes = [InviteType::INVITED_MEMBER, InviteType::INVITED_GENERATE_LINK];

        if (!in_array($inviteType, $inviteMemberTypes)) {
            return $query->whereNotIn('invite_type', $inviteMemberTypes);
        }

        return $query->whereIn('invite_type', $inviteMemberTypes);
    }

    /**
     * @inheritDoc
     *
     * @param User       $context
     * @param Group      $group
     * @param User       $user
     * @param array|null $inviteLink
     *
     * @throws ValidatorException
     * @throws AuthorizationException
     */
    public function inviteFriend(User $context, Group $group, User $user, ?GroupInviteCode $inviteLink): void
    {
        $code       = $expired = null;
        $inviteType = InviteType::INVITED_MEMBER;
        if ($group->isMember($user)) {
            return;
        }

        if ($this->memberBlockRepository()->isBlocked($group->entityId(), $user->entityId())) {
            return;
        }

        if ($this->hasRequestedInvite($group, $user, $inviteType)) {
            $this->groupMemberRepository()->addGroupMember($group, $user->entityId());

            return;
        }

        if (!$this->isFriend($context, $user) && empty($inviteLink) === null) {
            return;
        }

        if ($inviteLink !== null) {
            $code       = $inviteLink->code;
            $expired    = $inviteLink->expired_at;
            $inviteType = InviteType::INVITED_GENERATE_LINK;
            $this->declineInvite($group, $user);
        }

        $this->handleCreateInvite($context, $user, $group->entityId(), $inviteType, $code, $expired);
    }

    /**
     * @param Group  $group
     * @param User   $user
     * @param string $inviteType
     *
     * @return bool
     */
    protected function hasRequestedInvite(Group $group, User $user, string $inviteType): bool
    {
        //auto join when request exist
        $requested = $this->memberRequestRepository()
            ->getRequestByUserGroupId($user->entityId(), $group->entityId(), StatusScope::STATUS_PENDING);
        if (null == $requested) {
            return false;
        }

        if ($inviteType == InviteType::INVITED_GENERATE_LINK) {
            return false;
        }

        $requested->update(['status_id' => StatusScope::STATUS_APPROVED]);

        return true;
    }

    public function handleExpiredInvite(string $inviteType, ?string $expired)
    {
        $numberHours = match ($inviteType) {
            InviteType::INVITED_MEMBER => Settings::get('group.invite_expiration_interval', 0),
            default                    => Settings::get('group.invite_expiration_role', 0)
        };

        return match ($inviteType) {
            InviteType::INVITED_GENERATE_LINK => $expired,
            default                           => $numberHours == 0 ? null : Carbon::now()->addHours($numberHours)
        };
    }

    private function handleAfterAcceptInvite(Group $group, User $user, Invite $invite): void
    {
        $notificationType = $this->getNotificationType($invite);
        if ($notificationType) {
            $this->handleDeleteNotification($invite, $notificationType);
        }

        if ($invite->getInviteType() === InviteType::INVITED_ADMIN_GROUP) {
            $this->handleDeleteMute($group, $user);
        }

        $requested = $this->memberRequestRepository()->getRequestByUserGroupId($user->entityId(), $group->entityId(), StatusScope::STATUS_PENDING);

        if (!$group->isSecretPrivacy()) {
            $requested?->update(['status_id' => StatusScope::STATUS_APPROVED]);
        }
    }

    private function handleDeleteMute(Group $group, User $user): void
    {
        $muteRepository = resolve(MuteRepositoryInterface::class);
        $muteRepository->deleteMute($group->entityId(), $user->entityId());
    }

    private function handleDeleteNotification(Invite $invite, string $notificationType): void
    {
        $this->memberRequestRepository()
            ->removeNotificationForPendingRequest($notificationType, $invite->entityId(), $invite->entityType());
    }

    private function getNotificationType(Invite $invite): ?string
    {
        return match ($invite->getInviteType()) {
            InviteType::INVITED_ADMIN_GROUP     => 'add_group_admin',
            InviteType::INVITED_MODERATOR_GROUP => 'add_group_moderator',
            InviteType::INVITED_MEMBER          => 'group_invite',
            default                             => null,
        };
    }

    public function getInviteByCode(User $user, int $groupId, string $code): ?Invite
    {
        $query = $this->getModel()->newQuery();
        $query->where('code', $code)
            ->where('group_id', $groupId)
            ->where('owner_id', $user->entityId());

        return $query?->first();
    }
}
