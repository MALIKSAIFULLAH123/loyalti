<?php

namespace MetaFox\BackgroundStatus\Observers;

use MetaFox\BackgroundStatus\Models\BgsCollection;

/**
 * Class BgsCollectionObserver.
 * @ignore
 * @codeCoverageIgnore
 */
class BgsCollectionObserver
{
    public function updated(BgsCollection $bgsCollection): void
    {
        if ($bgsCollection->wasChanged(['is_deleted'])) {
            $bgsCollection->backgrounds()->update(['is_deleted' => BgsCollection::IS_DELETED]);
        }

        if ($bgsCollection->wasChanged('is_default')) {
            if ($bgsCollection->is_default) {
                BgsCollection::query()->newQuery()
                    ->where('id', '<>', $bgsCollection->entityId())
                    ->update(['is_default' => 0]);
            }
        }
    }
}
