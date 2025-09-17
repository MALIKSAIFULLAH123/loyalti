<?php

namespace MetaFox\Activity\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Activity\Contracts\TypeManager;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Type;

/**
 * Class DeleteFeedListener.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @ignore
 */
class DeleteFeedListener
{
    /**
     * @param Feed|null $feed
     *
     * @return bool
     */
    public function handle(?Feed $feed): bool
    {
        if (null == $feed) {
            return false;
        }

        $feed->delete();

        // If all actions is on resource, delete resource.
        if ($this->getTypeManager()->hasFeature($feed->type_id, Type::ACTION_ON_FEED_TYPE) == false) {
            if ($feed->item && $feed->item instanceof Model) {
                $feed->item->delete();
            }
        }

        return true;
    }

    /**
     * @return TypeManager
     */
    protected function getTypeManager(): TypeManager
    {
        return resolve(TypeManager::class);
    }
}
