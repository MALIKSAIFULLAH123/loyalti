<?php

namespace MetaFox\Group\Repositories\Eloquent;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Models\Mute;
use MetaFox\Group\Policies\MemberPolicy;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Repositories\MuteRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class MuteRepository.
 */
class MuteRepository extends AbstractRepository implements MuteRepositoryInterface
{
    use UserMorphTrait;

    public function model()
    {
        return Mute::class;
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function muteInGroup(User $context, int $groupId, array $attributes): bool
    {
        $userId = $attributes['user_id'];
        $user   = UserEntity::getById($userId);

        $attributes['user_type'] = $user->entityType();

        $expiredAt = Arr::get($attributes, 'expired_at');

        $now    = CarbonImmutable::now();
        $member = $this->getMemberRepository()->getGroupMember($groupId, $userId);
        policy_authorize(MemberPolicy::class, 'muteInGroup', $context, $member);

        $isMuted = $this->isMuted($groupId, $userId);

        if ($isMuted) {
            abort(403, __p('group::phrase.mute_in_group_message', [
                'value' => 1,
            ]));
        }

        if (null !== $expiredAt) {
            $attributes['expired_at'] = $now->add($expiredAt);
        }

        //eg. now = 22-08-10 11:11:53, expiredAt = 1day12hours, $member->mute_expired_at = 22-08-11 23:13:06
        $this->getModel()->newQuery()->create($attributes);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function syncUserMuted(): void
    {
        $members = Member::query()->get();
        foreach ($members as $member) {
            /** @var Member $member */
            $isMuted = $member?->is_muted ?? false;
            if (!$isMuted) {
                continue;
            }
            $data = [
                'user_id'    => $member->userId(),
                'user_type'  => $member->userType(),
                'group_id'   => $member->group->entityId(),
                'status'     => $member->is_muted,
                'expired_at' => $member->mute_expired_at,
            ];
            $this->getModel()->newQuery()->create($data);
        }
    }

    /**
     * @inheritDoc
     */
    public function isMuted(int $groupId, int $userId): bool
    {
        return $this->getModel()->newQuery()
            ->where([
                'group_id' => $groupId,
                'user_id'  => $userId,
                'status'   => Mute::STATUS_MUTED,
            ])
            ->where(function (Builder $builder) {
                $builder->where('expired_at', '>=', Carbon::now())
                    ->orWhereNull('expired_at');
            })
            ->exists();
    }

    /**
     * @param int $groupId
     * @param int $userId
     * @return Builder|Model|object|null
     */
    public function getUserMuted(int $groupId, int $userId)
    {
        return $this->getModel()->newQuery()
            ->with(['user', 'group'])
            ->where([
                'group_id' => $groupId,
                'user_id'  => $userId,
                'status'   => Mute::STATUS_MUTED,
            ])
            ->where(function (Builder $builder) {
                $builder->where('expired_at', '>=', Carbon::now())
                    ->orWhereNull('expired_at');
            })
            ->first();
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function unmuteInGroup(User $context, int $groupId, int $userId): bool
    {
        $isMember = $this->getMemberRepository()->isGroupMember($groupId, $userId);

        if ($isMember) {
            $member = $this->getMemberRepository()->getGroupMember($groupId, $userId);
            policy_authorize(MemberPolicy::class, 'unmuteInGroup', $context, $member);
        }

        return $this->deleteMute($groupId, $userId);
    }

    public function deleteMute(int $groupId, int $userId): bool
    {
        return $this->getModel()->newQuery()->where([
            'group_id' => $groupId,
            'user_id'  => $userId,
            'status'   => Mute::STATUS_MUTED,
        ])->delete();
    }

    /**
     * @param User  $context
     * @param int   $groupId
     * @param array $attributes
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewMutedUsersInGroup(User $context, int $groupId, array $attributes): Paginator
    {
        $group  = $this->getGroupRepository()->find($groupId);
        $search = Arr::get($attributes, 'q', '');

        policy_authorize(MemberPolicy::class, 'viewAny', $context, $group);

        $limit = $attributes['limit'];
        $query = $this->getModel()->newQuery();

        if ($search != '') {
            $query = $query->join('users', 'users.id', '=', 'group_muted.user_id')
                ->where(function (Builder $builder) use ($search) {
                    $builder->where('users.full_name', $this->likeOperator(), '%' . $search . '%')
                        ->orWhere('users.user_name', $this->likeOperator(), '%' . $search . '%');
                });
        }

        return $query->with(['user', 'group'])
            ->where('group_id', $groupId)
            ->where(function (Builder $builder) {
                $builder->where('expired_at', '>=', Carbon::now())
                    ->orWhereNull('expired_at');
            })
            ->simplePaginate($limit, ['group_muted.*']);
    }

    protected function getGroupRepository(): GroupRepositoryInterface
    {
        return resolve(GroupRepositoryInterface::class);
    }

    protected function getMemberRepository(): MemberRepositoryInterface
    {
        return resolve(MemberRepositoryInterface::class);
    }
}
