<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Marketplace\Listeners;

use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;

/**
 * Class UpdateFeedItemPrivacyListener.
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateFeedItemPrivacyListener
{
    public function __construct(protected ListingRepositoryInterface $repository)
    {
    }

    /**
     * @param  int        $itemId
     * @param  string     $itemType
     * @param  int        $privacy
     * @param  int[]      $list
     * @throws \Exception
     */
    public function handle(int $itemId, string $itemType, int $privacy, array $list = []): void
    {
        if ($itemType != Listing::ENTITY_TYPE) {
            return;
        }

        $item          = $this->repository->find($itemId);
        $item->privacy = $privacy;
        $item->setPrivacyListAttribute($list);
        $item->save();
    }
}
