<?php

namespace MetaFox\Activity\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Cache;
use MetaFox\Activity\Models\Pin;
use MetaFox\Activity\Repositories\PinRepositoryInterface;
use MetaFox\Activity\Support\Browse\Scopes\TypeScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class PinRepository.
 */
class PinRepository extends AbstractRepository implements PinRepositoryInterface
{
    public const HOMEPAGE_PIN_CACHE_ID = 'activity_homepage_pins';

    public function model()
    {
        return Pin::class;
    }

    public function findPin(User $context, ?User $owner, int $feedId): ?Pin
    {
        $ownerId = $owner?->entityId();

        /** @var ?Pin $pin */
        $pin = $this->getModel()->newQuery()
            ->where([
                ['owner_id', '=', $ownerId],
                ['feed_id', '=', $feedId],
            ])->first();

        return $pin;
    }

    public function pin(User $context, User $owner, int $feedId): bool
    {
        $limit = Settings::get('activity.feed.total_pin_in_profile', 3);

        $pinned = $this->getBuilder($owner->id)
            ->pluck('feed_id')
            ->toArray();

        if (in_array($feedId, $pinned)) {
            return true;
        }

        $this->create([
            'user_id'    => $context->id,
            'user_type'  => $context->entityType(),
            'owner_id'   => $owner->id,
            'owner_type' => $owner->entityType(),
            'feed_id'    => $feedId,
        ]);

        if ($limit == 0) {
            return true;
        }

        if (count($pinned) < $limit) {
            return true;
        }

        $excludes = array_slice($pinned, $limit - 1);

        $this->getModel()->newQuery()
            ->where('owner_id', '=', $owner->id)
            ->whereIn('feed_id', $excludes)
            ->forceDelete();

        return true;
    }

    public function unpin(User $context, User $owner, int $feedId): bool
    {
        $data = $this->findPin($context, $owner, $feedId);

        if (!$data) {
            return true;
        }

        $data->delete();

        return true;
    }

    public function pinHome(User $context, int $feedId): bool
    {
        $limit = Settings::get('activity.feed.total_pin_in_homepage', 3);

        $pinned = $this->getBuilder()
            ->pluck('feed_id')
            ->toArray();

        if (in_array($feedId, $pinned)) {
            return true;
        }

        $this->create([
            'user_id'    => $context->id,
            'user_type'  => $context->entityType(),
            'owner_id'   => null,
            'owner_type' => null,
            'feed_id'    => $feedId,
        ]);

        $this->clearCache();

        if ($limit == 0) {
            return true;
        }

        if (count($pinned) < $limit) {
            return true;
        }

        $excludes = array_slice($pinned, $limit - 1);

        $this->getModel()->newQuery()
            ->whereNull('owner_id')
            ->whereIn('feed_id', $excludes)
            ->forceDelete();

        return true;
    }

    public function unpinHome(User $context, int $feedId): bool
    {
        $data = $this->findPin($context, null, $feedId);

        if (!$data) {
            return true;
        }

        $data->delete();

        $this->clearCache();

        return true;
    }

    public function getPinOwnerIds(User $context, int $feedId): array
    {
        /* @link \MetaFox\Activity\Support\LoadMissingPinOwnerIds::handle */
        return LoadReduce::get(
            sprintf('feed:pinedOwnerIds(feed:%s)', $feedId),
            fn () => $this->getModel()->newQuery()
                ->where('feed_id', '=', $feedId)
                ->orderBy('created_at', 'desc')
                ->pluck('owner_id')
                ->toArray()
        );
    }

    public function getPinsInProfilePage(int $ownerId): array
    {
        $limit = Settings::get('activity.feed.total_pin_in_profile', 3);

        $query = $this->getBuilder($ownerId);

        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query
            ->addScope(resolve(TypeScope::class))
            ->pluck('feed_id')
            ->toArray();
    }

    public function getPinsInHomePage(): array
    {
        return Cache::remember(self::HOMEPAGE_PIN_CACHE_ID, 3600, function () {
            $query = $this->getBuilder();

            $limit = Settings::get('activity.feed.total_pin_in_homepage', 3);

            if ($limit > 0) {
                $query->limit($limit);
            }

            return $query
                ->addScope(resolve(TypeScope::class))
                ->pluck('feed_id')
                ->toArray();
        });
    }

    protected function getBuilder(?int $ownerId = null): Builder
    {
        $query = $this->getModel()->newQuery()
            ->join('activity_feeds', function (JoinClause $joinClause) {
                $joinClause->on('activity_feeds.id', '=', 'activity_pins.feed_id');
            })
            ->orderBy('activity_pins.created_at', 'desc');

        if (null === $ownerId) {
            return $query->whereNull('activity_pins.owner_id');
        }

        return $query->where('activity_pins.owner_id', '=', $ownerId);
    }

    public function clearCache(?int $ownerId = null): void
    {
        if (null === $ownerId) {
            Cache::forget(self::HOMEPAGE_PIN_CACHE_ID);
        }
    }
}
