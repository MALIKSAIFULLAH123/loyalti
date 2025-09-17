<?php

namespace MetaFox\User\Support;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\User\Contracts\UserBlockedSupportContract;
use MetaFox\User\Models\UserBlocked as Model;
use MetaFox\User\Policies\UserPolicy;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

class UserBlockedSupport implements UserBlockedSupportContract
{
    /** @var array<int, array<int>> */
    private array $blockedList = [];

    /** @var array<string,boolean> */
    private array $blocked = [];

    /**
     * @var UserRepositoryInterface
     */
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository =  $userRepository;
    }

    public function getCacheName(int $userId): string
    {
        return sprintf('user_blocked_%s', $userId);
    }

    public function clearCache(int $userId): void
    {
        $this->blocked = [];

        if (isset($this->blockedList[$userId])) {
            unset($this->blockedList[$userId]);
        }

        $cacheName = $this->getCacheName($userId);
        Cache::forget($cacheName);
    }

    public function isBlocked(?ContractUser $user, ?ContractUser $owner): bool
    {
        if (!$user || !$owner) {
            return false;
        }

        if ($user->entityId() == $owner->entityId()) {
            return false;
        }

        if (!$user->entityId() || !$owner->entityId()) {
            return false;
        }

        return LoadReduce::remember(
            sprintf('user::blocked(user:%s,owner:%s)', $user->entityId(), $owner->entityId()),
            fn () => Model::query()
                ->where('user_id', $user->entityId())
                ->where('owner_id', $owner->entityId())
                ->exists()
        );
    }

    public function getBlockedUsers(ContractUser $user, string $search = null): array
    {
        if ($search != null) {
            $this->blockedList[$user->entityId()] = $this->userRepository->searchBlockUser($user, $search);

            return $this->blockedList[$user->entityId()];
        }

        if (!isset($this->blockedList[$user->entityId()])) {
            $this->blockedList[$user->entityId()] = Model::query()
                ->where('user_id', $user->entityId())
                ->get(['owner_id', 'user_id'])
                ->pluck('user_id', 'owner_id')
                ->toArray();
        }

        return $this->blockedList[$user->entityId()];
    }

    public function getBlockedUserIds(User $user): array
    {
        // should be cached in array.
        $blockedUser = $this->getBlockedUsers($user);

        return array_keys($blockedUser);
    }

    public function blockUser(ContractUser $user, ContractUser $owner): bool
    {
        policy_authorize(UserPolicy::class, 'blockUser', $user, $owner);
        $this->processBlockUser($user, $owner);

        return true;
    }

    private function processBlockUser(ContractUser $user, ContractUser $owner): void
    {
        $data = [
            'user_id'    => $user->entityId(),
            'user_type'  => $user->entityType(),
            'owner_id'   => $owner->entityId(),
            'owner_type' => $owner->entityType(),
        ];

        Model::query()->firstOrCreate($data, $data);

        $this->clearCache($user->entityId());
        $this->clearCache($owner->entityId());

        app('events')->dispatch('user.blocked', [$user, $owner]);
    }

    public function unBlockUser(ContractUser $user, ContractUser $owner): bool
    {
        policy_authorize(UserPolicy::class, 'unBlockUser', $user, $owner);

        $this->processUnBlockUser($user, $owner);

        return true;
    }

    private function processUnBlockUser(ContractUser $user, ContractUser $owner): void
    {
        Model::query()
            ->where('user_id', $user->entityId())
            ->where('owner_id', $owner->entityId())
            ->delete();

        $this->clearCache($user->entityId());
        $this->clearCache($owner->entityId());

        app('events')->dispatch('user.unblocked', [$user, $owner]);
    }

    public function getBlockedUsersCollection(ContractUser $user, ?string $search): Collection
    {
        $blockedUserIds = $this->getBlockedUsers($user, $search);

        $data = new Collection();

        if (!empty($blockedUserIds)) {
            $data = \MetaFox\User\Models\UserEntity::query()
                ->with('detail')
                ->whereIn('id', array_keys($blockedUserIds))
                ->orderBy('id', 'DESC')
                ->get();
        }

        return $data;
    }

    public function getBlockUserDetail(ContractUser $user, ContractUser $owner)
    {
        if (!$this->isBlocked($user, $owner)) {
            return null;
        }

        return \MetaFox\User\Models\UserEntity::query()
            ->with('detail')
            ->findOrFail($owner->entityId());
    }
}
