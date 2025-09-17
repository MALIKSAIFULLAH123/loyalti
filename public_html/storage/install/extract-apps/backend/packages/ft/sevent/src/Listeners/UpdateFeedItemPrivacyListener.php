<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace Foxexpert\Sevent\Listeners;

use Foxexpert\Sevent\Models\Sevent;
use Foxexpert\Sevent\Repositories\SeventRepositoryInterface;

/**
 * Class UpdateFeedItemPrivacyListener.
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateFeedItemPrivacyListener
{
    /**
     * @param int    $itemId
     * @param string $itemType
     * @param int    $privacy
     * @param int[]  $list
     */
    public function handle(int $itemId, string $itemType, int $privacy, array $list = []): void
    {
        if ($itemType != Sevent::ENTITY_TYPE) {
            return;
        }

        $item = resolve(SeventRepositoryInterface::class)->find($itemId);
        $item->privacy = $privacy;
        $item->setPrivacyListAttribute($list);
        $item->save();
    }
}
