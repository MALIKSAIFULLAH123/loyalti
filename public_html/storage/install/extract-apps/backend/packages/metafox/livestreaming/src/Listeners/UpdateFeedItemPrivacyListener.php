<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\LiveStreaming\Listeners;

use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;

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
        if ($itemType != LiveVideo::ENTITY_TYPE) {
            return;
        }

        $item          = resolve(LiveVideoRepositoryInterface::class)->find($itemId);

        if (!$item instanceof LiveVideo) {
            return;
        }
        $item->privacy = $privacy;
        $item->setPrivacyListAttribute($list);
        $item->save();
    }
}
