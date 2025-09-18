<?php

namespace MetaFox\Marketplace\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Marketplace\Repositories\ListingHistoryRepositoryInterface;
use MetaFox\Marketplace\Models\ListingHistory;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class ListingHistoryRepository.
 */
class ListingHistoryRepository extends AbstractRepository implements ListingHistoryRepositoryInterface
{
    public function model()
    {
        return ListingHistory::class;
    }

    public function createHistory(int $userId, string $userType, int $listingId, bool $updateVisitedAt = false): ?ListingHistory
    {
        $model = $this->getModel()->newModelQuery()
            ->firstOrCreate([
                'listing_id' => $listingId,
                'user_id'    => $userId,
                'user_type'  => $userType,
            ], [
                'visited_at' => $this->getModel()->freshTimestamp(),
            ]);

        if (null === $model) {
            return null;
        }

        if ($model->wasRecentlyCreated) {
            return $model->refresh();
        }

        if (false === $updateVisitedAt) {
            return $model;
        }

        $model->fill([
            'visited_at' => $this->getModel()->freshTimestamp(),
        ]);

        $model->save();

        return $model;
    }

    public function deleteHistoriesByListing(int $id): void
    {
        $this->getModel()->newModelQuery()
            ->where([
                'listing_id' => $id,
            ])
            ->delete();
    }

    public function deleteHistoriesByUser(int $userId, string $userType): void
    {
        $this->getModel()->newModelQuery()
            ->where([
                'user_id'   => $userId,
                'user_type' => $userType,
            ])
            ->delete();
    }
}
