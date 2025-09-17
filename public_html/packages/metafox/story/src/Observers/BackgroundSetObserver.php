<?php

namespace MetaFox\Story\Observers;

use MetaFox\Story\Models\BackgroundSet;

/**
 * Class BgsCollectionObserver.
 * @ignore
 * @codeCoverageIgnore
 */
class BackgroundSetObserver
{
    public function updated(BackgroundSet $backgroundSet): void
    {
        if ($backgroundSet->wasChanged(['is_deleted'])) {
            $backgroundSet->backgrounds()?->update(['is_deleted' => 1]);
        }

        if ($backgroundSet->wasChanged('is_default')) {
            if ($backgroundSet->is_default) {
                BackgroundSet::query()->newQuery()
                    ->where('id', '<>', $backgroundSet->entityId())
                    ->update(['is_default' => 0]);
            }
        }
    }
}
