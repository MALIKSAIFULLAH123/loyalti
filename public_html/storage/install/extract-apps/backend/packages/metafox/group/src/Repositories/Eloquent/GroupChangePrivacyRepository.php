<?php

namespace MetaFox\Group\Repositories\Eloquent;

use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\GroupChangePrivacy;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Notifications\PendingPrivacyNotification;
use MetaFox\Group\Notifications\SuccessPrivacyNotification;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\GroupChangePrivacyRepositoryInterface;
use MetaFox\Group\Repositories\GroupHistoryRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Group\Support\Support;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class GroupChangePrivacyRepository.
 *
 * @method GroupChangePrivacy getModel()
 * @method GroupChangePrivacy find($id, $columns = ['*'])()
 */
class GroupChangePrivacyRepository extends AbstractRepository implements GroupChangePrivacyRepositoryInterface
{
    public function model()
    {
        return GroupChangePrivacy::class;
    }

    /**
     * @return GroupRepositoryInterface
     */
    private function groupRepository(): GroupRepositoryInterface
    {
        return resolve(GroupRepositoryInterface::class);
    }

    /**
     * @return GroupHistoryRepositoryInterface
     */
    private function historyRepository(): GroupHistoryRepositoryInterface
    {
        return resolve(GroupHistoryRepositoryInterface::class);
    }

    /**
     * @return MemberRepositoryInterface
     */
    private function groupMemberRepository(): MemberRepositoryInterface
    {
        return resolve(MemberRepositoryInterface::class);
    }

    /**
     * @return PrivacyTypeHandler
     */
    private function getPrivacyTypeHandler(): PrivacyTypeHandler
    {
        return resolve(PrivacyTypeHandler::class);
    }

    /**
     * @inheritDoc
     * @throws ValidatorException
     */
    public function createRequest(Group $group, User $user, array $attributes): bool
    {
        if ($this->isPendingChangePrivacy($group)) {
            return false;
        }
        $numberDays = Settings::get('group.number_days_expiration_change_privacy');

        $data = [
            'expired_at'   => Carbon::now(),
            'group_id'     => $group->entityId(),
            'user_id'      => $user->entityId(),
            'user_type'    => $user->entityType(),
            'is_active'    => GroupChangePrivacy::IS_NOT_ACTIVE,
            'privacy_type' => $attributes['privacy_type'],
            'privacy'      => $this->getPrivacyTypeHandler()->getPrivacy($attributes['privacy_type']),
            'privacy_item' => $this->getPrivacyTypeHandler()->getPrivacyItem($attributes['privacy_type']),
        ];

        /* @var GroupChangePrivacy $groupChangePrivacy */
        if ($numberDays > 0) {
            $data['expired_at'] = Carbon::now()->addDays($numberDays);
            $data['is_active']  = GroupChangePrivacy::IS_ACTIVE;
            $groupChangePrivacy = parent::create($data);
            $this->sentNotificationWhenPending($groupChangePrivacy->entityId());

            return true;
        }

        $groupChangePrivacy = parent::create($data);

        $this->sentNotificationWhenSuccess($groupChangePrivacy->entityId());

        $this->updatePrivacyGroup($user, $group, $attributes['privacy_type']);

        return true;
    }

    /**
     * @param User $user
     * @param int  $groupId
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function cancelRequest(User $user, int $groupId): bool
    {
        $now   = Carbon::now();
        $group = $this->groupRepository()->find($groupId);

        policy_authorize(GroupPolicy::class, 'update', $user, $group);

        /** @var $model GroupChangePrivacy */
        $model = $this->getModel()->newQuery()
            ->where([
                'group_id'  => $group->entityId(),
                'is_active' => GroupChangePrivacy::IS_ACTIVE,
            ])
            ->whereDate('expired_at', '>=', $now)->first();

        if (!$model instanceof GroupChangePrivacy) {
            return false;
        }

        $model->update(['is_active' => GroupChangePrivacy::IS_NOT_ACTIVE]);

        app('events')->dispatch(
            'notification.delete_notification_by_type_and_item',
            ['pending_privacy', $model->entityId(), $model->entityType()]
        );

        return true;
    }

    /**
     * @inheritDoc
     */
    public function sentNotificationWhenPending(int $id): void
    {
        $model   = $this->find($id);
        $members = $this->groupMemberRepository()->getModel()
            ->newQuery()->with('userEntity')
            ->where('group_id', $model->group_id)
            ->where('member_type', Member::ADMIN)->get();

        $notification = new PendingPrivacyNotification($model);

        $this->sendNotification($members, $notification);
    }

    public function isPendingChangePrivacy(Group $group): bool
    {
        return $this->getModel()->newQuery()
            ->where([
                'group_id'  => $group->entityId(),
                'is_active' => GroupChangePrivacy::IS_ACTIVE,
            ])->exists();
    }

    /**
     * @inheritDoc
     */
    public function sentNotificationWhenSuccess(int $id): void
    {
        $model        = $this->find($id);
        $members      = $this->groupMemberRepository()->getGroupMembers($model->group_id);
        $notification = new SuccessPrivacyNotification($model);

        if ($members->isEmpty()) {
            return;
        }
        $this->sendNotification($members, $notification);

    }

    protected function sendNotification(Collection $members, mixed $notification): void
    {
        $users = [];
        foreach ($members as $member) {
            if (!$member->user instanceof User) {
                continue;
            }

            $users[] = $member->user;

        }

        $notificationParams = [$users, $notification];
        Notification::send(...$notificationParams);
    }

    /**
     * @param User   $user
     * @param Group  $group
     * @param string $privacyType
     *
     * @return void
     */
    public function updatePrivacyGroup(User $user, Group $group, string $privacyType): void
    {
        $paramsHistory = [
            'type' => Support::UPDATE_GROUP_PRIVACY_TYPE,
            'new'  => __p(PrivacyTypeHandler::PRIVACY_PHRASE[$privacyType]),
            'old'  => __p(PrivacyTypeHandler::PRIVACY_PHRASE[$group->privacy_type]),
        ];

        $group->update([
            'privacy'      => $this->getPrivacyTypeHandler()->getPrivacy($privacyType),
            'privacy_item' => $this->getPrivacyTypeHandler()->getPrivacyItem($privacyType),
            'privacy_type' => $privacyType,
        ]);

        $this->historyRepository()->createHistory($user, $group, $paramsHistory);
    }
}
