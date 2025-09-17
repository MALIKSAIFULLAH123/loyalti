<?php

namespace MetaFox\BackgroundStatus\Observers;

use MetaFox\BackgroundStatus\Models\BgsBackground;
use MetaFox\BackgroundStatus\Repositories\BgsCollectionRepositoryInterface;

/**
 * Class BgsBackgroundObserver.
 * @ignore
 * @codeCoverageIgnore
 */
class BgsBackgroundObserver
{
    public function created(BgsBackground $background): void
    {
        $bgsCollection = $background->bgsCollection;
        if (method_exists($bgsCollection, 'incrementAmount')) {
            $bgsCollection->incrementAmount('total_background');
        }

        if ($bgsCollection->main_background_id == 0) {
            resolve(BgsCollectionRepositoryInterface::class)->updateMainBackground(
                $bgsCollection,
                $background->entityId()
            );
        }
    }

    public function updated(BgsBackground $background): void
    {
        if ($background->wasChanged(['is_deleted'])) {
            $bgsCollection = $background->bgsCollection;
            if (method_exists($bgsCollection, 'decrementAmount')) {
                $bgsCollection->decrementAmount('total_background');
            }

            if ($bgsCollection->main_background_id == $background->entityId()) {
                resolve(BgsCollectionRepositoryInterface::class)->updateMainBackground($bgsCollection);
            }
        }
    }
}
