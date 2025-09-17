<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Blog\Listeners;

use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Repositories\BlogRepositoryInterface;

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
        if ($itemType != Blog::ENTITY_TYPE) {
            return;
        }

        $item = resolve(BlogRepositoryInterface::class)->find($itemId);
        $item->privacy = $privacy;
        $item->setPrivacyListAttribute($list);
        $item->save();
    }
}
