<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Share;
use MetaFox\Activity\Models\Stream;
use MetaFox\Activity\Models\Type;
use MetaFox\Platform\PackageManager;

/**
 * Class PackageDeletingListener.
 * @ignore
 */
class PackageDeletingListener
{
    /**
     * @param string $packageName
     */
    public function handle(string $packageName): void
    {
        // If this turn is deleted activity, return. No need to go further.
        if ($packageName === 'metafox/activity') {
            return;
        }

        $this->cleanUpDatabase($packageName);
    }

    /**
     * @param string $packageName
     *
     * @return string[]
     */
    private function getResourceNames(string $packageName): array
    {
        return PackageManager::getResourceNames($packageName);
    }

    /**
     * @param string $packageName
     */
    private function cleanUpDatabase(string $packageName): void
    {
        $resourceNames = $this->getResourceNames($packageName);

        if (empty($resourceNames) || !is_array($resourceNames)) {
            return;
        }

        Stream::query()->whereIn('item_type', $resourceNames)->delete();

        Feed::query()->whereIn('item_type', $resourceNames)->delete();

        // Delete activity types.
        Type::query()->whereIn('entity_type', $resourceNames)->delete();

        // Delete share.
        $shareIds = Share::query()->whereIn('item_type', $resourceNames)->get('id')->pluck('id')->toArray();
        if (!empty($shareIds)) {

            // Delete share's streams.
            Stream::query()
                ->where('item_type', '=', Share::ENTITY_TYPE)
                ->whereIn('item_id', $shareIds)
                ->delete();

            // Delete share's feeds.
            Feed::query()
                ->where('item_type', '=', Share::ENTITY_TYPE)
                ->whereIn('item_id', $shareIds)
                ->delete();

            // Delete share items.
            Share::query()->whereIn('id', $shareIds)->delete();
        }
    }
}
