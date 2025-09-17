<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Event\Listeners;

use MetaFox\Event\Models\Event;
use MetaFox\Event\Repositories\EventRepositoryInterface;

/**
 * Class UpdateFeedItemPrivacyListener.
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateFeedItemPrivacyListener
{
    public function __construct(protected EventRepositoryInterface $repository)
    {
    }
    /**
     * @param int    $itemId
     * @param string $itemType
     * @param int    $privacy
     * @param int[]  $list
     */
    public function handle(int $itemId, string $itemType, int $privacy, array $list = []): void
    {
        if ($itemType != Event::ENTITY_TYPE) {
            return;
        }

        $item          = $this->repository->find($itemId);
        $item->privacy = $privacy;
        $item->setPrivacyListAttribute($list);
        $item->save();
    }
}
