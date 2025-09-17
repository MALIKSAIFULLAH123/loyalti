<?php

namespace MetaFox\Activity\Support;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Activity\Models\Subscription;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\User\Models\UserEntity as UserEntityModels;

class ActivitySubscription
{
    /**
     * @param int         $userId
     * @param int         $ownerId
     * @param bool        $active
     * @param string|null $specialType
     *
     * @return Subscription
     */
    public function addSubscription(
        int     $userId,
        int     $ownerId,
        bool    $active = true,
        ?string $specialType = null
    ): Subscription
    {
        $data = [
            'user_id'      => $userId,
            'owner_id'     => $ownerId,
            'is_active'    => $active,
            'special_type' => $specialType,
        ];

        $subscription = Subscription::query()->where($data)->first();

        if ($subscription instanceof Subscription) {
            return $subscription;
        }

        $subscription = Subscription::query()->firstOrCreate($data);

        app('events')->dispatch('activity.subscription.created', [$subscription]);

        if ($specialType == null) {
            $this->incrementTotalFollow($userId, $ownerId);
        }

        return $subscription;
    }

    /**
     * @param int         $userId
     * @param int         $ownerId
     * @param string|null $specialType
     *
     * @return bool
     */
    public function deleteSubscription(int $userId, int $ownerId, ?string $specialType = null): bool
    {
        $subscription = $this->getSubscription($userId, $ownerId, $specialType);

        if (!$subscription instanceof Subscription) {
            return true;
        }

        if (!$subscription->delete()) {
            return false;
        }

        app('events')->dispatch('activity.subscription.deleted', [$subscription]);

        if ($specialType == null) {
            $this->decrementTotalFollow($userId, $ownerId);
        }

        return true;
    }

    /**
     * @param int         $userId
     * @param int         $ownerId
     * @param bool        $active
     * @param string|null $specialType
     *
     * @return false|Subscription
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function updateSubscription(int $userId, int $ownerId, bool $active = false, ?string $specialType = null)
    {
        $subscription = $this->getSubscription($userId, $ownerId, $specialType);

        if (!$subscription instanceof Subscription) {
            return $this->addSubscription($userId, $ownerId, $active, $specialType);
        }

        $subscription->is_active = $active;

        if (!$subscription->save()) {
            return false;
        }

        app('events')->dispatch('activity.subscription.updated', [$subscription]);

        return $subscription;
    }

    /**
     * @param int         $userId
     * @param int         $ownerId
     * @param string|null $specialType
     *
     * @return Subscription|null
     */
    public function getSubscription(int $userId, int $ownerId, ?string $specialType = null): ?Subscription
    {
        $subscription = Subscription::query()
            ->where([
                'user_id'      => $userId,
                'owner_id'     => $ownerId,
                'special_type' => $specialType,
            ])
            ->first();

        if (!$subscription instanceof Subscription) {
            return null;
        }

        return $subscription;
    }

    public function isExist(int $userId, int $ownerId): bool
    {
        return LoadReduce::get(
            sprintf('follow::exists(user:%s,owner:%s)', $userId, $ownerId),
            fn () => Subscription::query()
                ->where([
                    'user_id'      => $userId,
                    'owner_id'     => $ownerId,
                    'is_active'    => true,
                    'special_type' => null,
                ])
                ->exists()
        );
    }

    public function getSubscriptions(array $attributes): Collection
    {
        return Subscription::query()
            ->where($attributes)
            ->get();
    }

    public function buildSubscriptions(array $attributes)
    {
        return Subscription::query()
            ->where('is_active', true)
            ->where($attributes);
    }

    private function incrementTotalFollow(int $userId, int $ownerId): void
    {
        if ($userId == $ownerId) {
            return;
        }

        $user  = UserEntityModels::withTrashed()->where('id', $userId)->first()?->detail;
        $owner = UserEntityModels::withTrashed()->where('id', $ownerId)->first()?->detail;

        if ($user instanceof User && method_exists($user, 'incrementTotalFollowing')) {
            if ($owner instanceof User && method_exists($owner, 'incrementTotalFollowing')) {
                $user->incrementTotalFollowing();
            }
        }

        if ($owner instanceof User && method_exists($owner, 'incrementTotalFollower')) {
            $owner->incrementTotalFollower();
        }
    }

    private function decrementTotalFollow(int $userId, int $ownerId): void
    {
        if ($userId == $ownerId) {
            return;
        }

        $user  = UserEntityModels::withTrashed()->where('id', $userId)->first()?->detail;
        $owner = UserEntityModels::withTrashed()->where('id', $ownerId)->first()?->detail;

        if ($user instanceof User && method_exists($user, 'decrementTotalFollowing')) {
            if ($owner instanceof User && method_exists($owner, 'decrementTotalFollowing')) {
                $user->decrementTotalFollowing();
            }
        }

        if ($owner instanceof User && method_exists($owner, 'decrementTotalFollower')) {
            $owner->decrementTotalFollower();
        }
    }
}
