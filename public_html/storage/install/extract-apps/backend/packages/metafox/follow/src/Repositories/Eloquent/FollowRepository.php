<?php

namespace MetaFox\Follow\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use MetaFox\Follow\Models\Follow;
use MetaFox\Follow\Policies\FollowPolicy;
use MetaFox\Follow\Repositories\FollowRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class FollowRepository.
 *
 * @property Follow $model
 * @method   Follow getModel()
 */
class FollowRepository extends AbstractRepository implements FollowRepositoryInterface
{
    public function model()
    {
        return Follow::class;
    }

    private function getUserRepository(): UserRepositoryInterface
    {
        return resolve(UserRepositoryInterface::class);
    }

    private function activitySub()
    {
        return resolve('Activity.Subscription');
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function follow(User $user, User $owner): void
    {
        policy_authorize(FollowPolicy::class, 'addFollow', $user, $owner);
        $this->activitySub()->addSubscription($user->entityId(), $owner->entityId());
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function viewFollow(User $context, array $attributes): Paginator
    {
        $view   = Arr::get($attributes, 'view');
        $userId = Arr::get($attributes, 'user_id');
        $user   = $context;

        if ($userId > 0) {
            $user = $this->getUserRepository()->find($userId);
        }

        policy_authorize(FollowPolicy::class, 'viewOnProfilePage', $context, $user);

        $subQuery = $this->getFollowingQuery($user);

        if ($view == MetaFoxConstant::VIEW_FOLLOWER) {
            $subQuery = $this->getFollowerQuery($user);
        }

        $query = $this->buildQueryByVersion($attributes)
            ->whereIn('id', $subQuery);


        return $query
            ->simplePaginate();
    }

    protected function buildQueryByVersion(array $attributes): Builder
    {
        $search = Arr::get($attributes, 'q');

        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.14', '<')) {
            $query = $this->getUserRepository()
                ->getModel()
                ->newQuery()
                ->with('profile');

            if ($search) {
                $query = $query->addScope(new SearchScope($search, ['full_name', 'user_name']));
            }

            return $query;
        }

        $query = UserEntity::query()->whereIn('entity_type', ['user', 'page']);

        if ($search) {
            $query = $query->addScope(new SearchScope($search, ['name', 'user_name']));
        }

        return $query;
    }

    protected function getFollowingIds(User $context): array
    {
        return $this->getFollowingQuery($context)
            ->pluck('owner_id')
            ->toArray();
    }

    protected function getFollowerIds(User $context): array
    {
        return $this->getFollowerQuery($context)
            ->pluck('user_id')
            ->toArray();
    }

    public function getUserFollowers(User $context): Collection
    {
        $query = $this->getUserRepository()
            ->getModel()
            ->newQuery()
            ->whereIn('id', $this->getFollowerQuery($context));

        return $query->get();
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function unfollow(User $context, User $user): bool
    {
        policy_authorize(FollowPolicy::class, 'unfollow', $context, $user);

        $result = $this->activitySub()->deleteSubscription($context->entityId(), $user->entityId());

        return $result;
    }

    public function isFollow(int $contextId, int $userId): bool
    {
        if ($contextId == $userId) {
            return false;
        }

        return $this->activitySub()->isExist($contextId, $userId);
    }

    public function totalFollowers(User $user): int
    {
        return $this->activitySub()
            ->buildSubscriptions(['owner_id' => $user->entityId()])
            ->whereNull('activity_subscriptions.special_type')
            ->where('activity_subscriptions.is_active', true)
            ->where('activity_subscriptions.user_id', '!=', $user->entityId())
            ->count();
    }

    public function totalFollowing(User $user): int
    {
        return $this->activitySub()
            ->buildSubscriptions(['user_id' => $user->entityId()])
            ->whereNull('activity_subscriptions.special_type')
            ->where('activity_subscriptions.is_active', true)
            ->where('activity_subscriptions.owner_id', '!=', $user->entityId())
            ->count();
    }

    public function getFollowerQuery(User $context): Builder
    {
        return $this->activitySub()
            ->buildSubscriptions(['owner_id' => $context->entityId()])
            ->select('user_id')
            ->where('activity_subscriptions.user_id', '!=', $context->entityId())
            ->where('activity_subscriptions.is_active', true)
            ->whereNull('activity_subscriptions.special_type');
    }

    public function getFollowingQuery(User $context): Builder
    {
        return $this->activitySub()
            ->buildSubscriptions(['user_id' => $context->entityId()])
            ->where('activity_subscriptions.owner_id', '!=', $context->entityId())
            ->where('activity_subscriptions.is_active', true)
            ->select('owner_id')
            ->whereNull('activity_subscriptions.special_type');
    }
}
