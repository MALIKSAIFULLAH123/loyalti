<?php

namespace MetaFox\Activity\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use MetaFox\Activity\Contracts\SnoozeContract;
use MetaFox\Activity\Models\Snooze as Model;
use MetaFox\Activity\Repositories\SnoozeRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class Snooze implements SnoozeContract
{
    /** @var array<int, array<string, mixed>> */
    private array $snoozedList = [];

    public function __construct(protected SnoozeRepositoryInterface $snoozeRepository)
    {
    }

    public function getAllowedSnoozeTypes(): array
    {
        return Arr::pluck($this->getSnoozeOptions(), 'value');
    }

    public function getSnoozeOptions(): array
    {
        return collect($this->getTabConfigs())
            ->filter(fn (array $tab) => app_active($tab['module']))
            ->map(fn (array $tab) => Arr::only($tab, ['label', 'value']))
            ->values()
            ->toArray();
    }

    protected function getTabConfigs(): array
    {
        return [
            'user' => [
                'module' => 'metafox/user',
                'label'  => __p('activity::phrase.snooze_user_tab'),
                'value'  => Constants::SNOOZE_TYPE_USER,
            ],
            'page' => [
                'module' => 'metafox/page',
                'label'  => __p('activity::phrase.snooze_page_tab'),
                'value'  => Constants::SNOOZE_TYPE_PAGE,
            ],
            'group' => [
                'module' => 'metafox/group',
                'label'  => __('activity::phrase.snooze_group_tab'),
                'value'  => Constants::SNOOZE_TYPE_GROUP,
            ],
        ];
    }

    public function getSearchSnoozeDesc(): string
    {
        return __p(
            'activity::phrase.search_users_pages_groups',
            [
                'hasPage'  => app_active('metafox/page'),
                'hasGroup' => app_active('metafox/group'),
            ]
        );
    }

    public function clearCache(int $userId): void
    {
        Cache::forget($this->getCacheName($userId));

        Arr::forget($this->snoozedList, $userId);
    }

    public function isSnooze(User $user, ?User $owner): bool
    {
        if (!$owner instanceof User) {
            return false;
        }

        if ($user->entityId() == $owner->entityId()) {
            return false;
        }

        if (!$this->getSnoozeRecord($user, $owner)) {
            return false;
        }

        return true;
    }

    public function isSnoozeForever(User $user, ?User $owner): bool
    {
        if (!$owner instanceof User) {
            return false;
        }

        if ($user->entityId() == $owner->entityId()) {
            return false;
        }

        $snoozeRecord = $this->getSnoozeRecord($user, $owner);

        return Arr::get($snoozeRecord, 'is_snooze_forever') === 1;
    }

    protected function getSnoozeRecord(User $user, User $owner): ?array
    {
        $snoozedUsers = $this->getSnoozedUsers($user);

        return Arr::get($snoozedUsers, $owner->entityId());
    }

    protected function getSnoozedUsers(User $user): array
    {
        if (Arr::has($this->snoozedList, $user->entityId())) {
            return $this->snoozedList[$user->entityId()];
        }

        $this->snoozedList[$user->entityId()] = $this->fetchSnoozedUsers($user->entityId());

        return $this->snoozedList[$user->entityId()];
    }

    public function fetchSnoozedUsers(int $userId): array
    {
        return Cache::remember(
            $this->getCacheName($userId),
            CacheManager::ACTIVITY_SNOOZE_CACHE_TIME,
            function () use ($userId) {
                return Model::query()
                    ->where('user_id', $userId)
                    ->where(function (Builder $q) {
                        $q->where('is_snooze_forever', '=', 1);
                        $q->orWhereDate('snooze_until', '>', Carbon::now()->format('Y-m-d H:i:s'));
                    })
                    ->get(['id', 'owner_id', 'is_snooze_forever', 'snooze_until'])
                    ->keyBy('owner_id')
                    ->toArray();
            }
        );
    }

    public function snooze(User $user, User $owner, int $snoozeDay = Constants::DEFAULT_SNOOZE_DAYS): Model
    {
        return $this->snoozeRepository->snooze($user, $owner, $snoozeDay);
    }

    public function snoozeForever(User $user, User $owner): Model
    {
        return $this->snoozeRepository->snoozeForever($user, $owner);
    }

    public function unSnooze(User $user, User $owner): Model
    {
        return $this->snoozeRepository->unSnooze($user, $owner);
    }

    protected function getCacheName(int $userId): string
    {
        return sprintf(CacheManager::ACTIVITY_SNOOZE_CACHE, $userId);
    }
}
