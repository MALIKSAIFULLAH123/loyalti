<?php

namespace MetaFox\Group\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use MetaFox\Group\Models\Block;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Policies\MemberPolicy;
use MetaFox\Group\Repositories\BlockRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Support\Browse\Scopes\GroupMember\ViewScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class BlockRepository.
 * @method Block getModel()
 */
class BlockRepository extends AbstractRepository implements BlockRepositoryInterface
{
    use UserMorphTrait;

    public function model()
    {
        return Block::class;
    }

    /**
     * @return UserRepositoryInterface
     */
    private function userRepository(): UserRepositoryInterface
    {
        return resolve(UserRepositoryInterface::class);
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
    private function memberRepository(): MemberRepositoryInterface
    {
        return resolve(MemberRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function viewGroupBlocks(User $context, array $attributes): Paginator
    {
        $groupId = $attributes['group_id'];
        $limit   = $attributes['limit'];
        $search  = Arr::get($attributes, 'q', '');
        $view    = Arr::get($attributes, 'view', 'all');

        $group = $this->groupRepository()->find($groupId);

        policy_authorize(GroupPolicy::class, 'viewInvitedOrBlocked', $context, $group);

        $query     = $this->getModel()->newQuery();
        $viewScope = new ViewScope();
        $viewScope->setView($view)->setGroupId($group->entityId());

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['full_name', 'user_name'], 'users'));
        }

        return $query->addScope($viewScope)->with(['userEntity', 'ownerEntity'])->simplePaginate($limit);
    }

    public function isBlocked(int $groupId, int $userId): bool
    {
        return LoadReduce::get(
            sprintf('user::blocked(user:%s,owner:%s)', $userId, $groupId),
            fn() => $this->getModel()->newQuery()
                ->where('group_id', $groupId)
                ->where('user_id', $userId)
                ->exists()
        );
    }

    /**
     * @inheritDoc
     *
     * @param User  $context
     * @param int   $groupId
     * @param array $attributes
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function addGroupBlock(User $context, int $groupId, array $attributes): bool
    {
        $deleteAllActivities = (bool) Arr::get($attributes, 'delete_activities', 0);
        $userId              = (int) Arr::get($attributes, 'user_id', 0);

        if ($userId <= 0) {
            return false;
        }

        /** @var User $user */
        $user = $this->userRepository()->find($userId);

        if (!$this->memberRepository()->isGroupMember($groupId, $user->entityId())) {
            return false;
        }

        $member = $this->memberRepository()
            ->getModel()
            ->newQuery()
            ->where('group_id', $groupId)
            ->where('user_id', $userId)->first();

        policy_authorize(MemberPolicy::class, 'blockFromGroup', $context, $member);

        $group = $this->groupRepository()->find($groupId);

        /* @var Block $block */
        Block::query()->create([
            'group_id'   => $groupId,
            'user_id'    => $user->entityId(),
            'user_type'  => $user->entityType(),
            'owner_id'   => $context->entityId(),
            'owner_type' => $context->entityType(),
        ]);
        $this->memberRepository()->deleteGroupMember($context, $groupId, $userId, $deleteAllActivities);
        app('events')->dispatch('user.user_blocked', [$group, $user]);

        return true;
    }

    /**
     * @param User  $context
     * @param int   $groupId
     * @param array $attributes
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteGroupBlock(User $context, int $groupId, array $attributes): bool
    {
        $userId = $attributes['user_id'];
        /** @var User $user */
        $user  = $this->userRepository()->find($userId);
        $group = $this->groupRepository()->find($groupId);
        policy_authorize(GroupPolicy::class, 'viewInvitedOrBlocked', $context, $group);

        app('events')->dispatch('user.user_unblocked', [$group, $user]);

        return $this->getModel()->newQuery()
            ->where('group_id', $groupId)
            ->where('user_id', $userId)
            ->delete();
    }
}
