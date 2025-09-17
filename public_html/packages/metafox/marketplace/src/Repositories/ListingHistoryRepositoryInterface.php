<?php

namespace MetaFox\Marketplace\Repositories;

use MetaFox\Marketplace\Models\ListingHistory;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface ListingHistory.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface ListingHistoryRepositoryInterface
{
    /**
     * @param  int                 $userId
     * @param  string              $userType
     * @param  int                 $listingId
     * @param  bool                $updateVisitedAt
     * @return ListingHistory|null
     */
    public function createHistory(int $userId, string $userType, int $listingId, bool $updateVisitedAt = false): ?ListingHistory;

    /**
     * @param  int  $id
     * @return void
     */
    public function deleteHistoriesByListing(int $id): void;

    /**
     * @param  int    $userId
     * @param  string $userType
     * @return void
     */
    public function deleteHistoriesByUser(int $userId, string $userType): void;
}
