<?php

namespace MetaFox\Saved\Observers;

use MetaFox\Platform\Contracts\HasAmounts;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Models\SavedAgg;
use MetaFox\Saved\Models\SavedList;
use MetaFox\Saved\Repositories\SavedSearchRepositoryInterface;

/**
 * Class SavedObserver.
 */
class SavedObserver
{
    public function __construct(protected SavedSearchRepositoryInterface $savedSearchRepository)
    {
    }

    public function created(Saved $saved): void
    {
        $savedAggData = [
            'user_id'   => $saved->userId(),
            'user_type' => $saved->userType(),
            'item_type' => $saved->itemType(),
        ];

        /** @var SavedAgg $savedAgg */
        $savedAgg = SavedAgg::query()->where($savedAggData)->first();

        if (null == $savedAgg) {
            (new SavedAgg($savedAggData))->save();
        }

        if ($savedAgg instanceof HasAmounts) {
            $savedAgg->incrementAmount('total_saved');
        }

        $item = $saved->item;
        if (!$item instanceof HasSavedItem) {
            return;
        }

        if (!$this->savedSearchRepository->isSearchExist($saved)) {
            $this->savedSearchRepository->createdBy($item);
        }

        $nestedAttributes = $saved->getNestedAttributesFor();

        if (!empty($nestedAttributes['savedLists'])) {
            SavedList::query()
                ->whereIn('id', $nestedAttributes['savedLists'])
                ->each(function (SavedList $savedList) use ($saved) {
                    if ($savedList->saved_id == 0) {
                        $savedList->update(['saved_id' => $saved->entityId()]);
                    }
                });
        }
    }

    public function deleted(Saved $saved): void
    {
        $item = $saved->item;

        if ($item instanceof HasSavedItem) {
            $saved->savedLists->each(function (SavedList $savedList) use ($saved) {
                if ($savedList->saved_id == $saved->entityId()) {
                    /** @var Saved $savedFirst */
                    $savedFirst = $savedList->savedItems
                        ->where('saved_id', '<>', $saved->entityId())
                        ->first();

                    $savedId = 0;
                    if (null != $savedFirst) {
                        $savedId = $savedFirst->entityId();
                    }

                    $savedList->update(['saved_id' => $savedId]);
                }
            });
        }

        $saved->savedLists()->sync([]);

        $savedAggData = [
            'user_id'   => $saved->userId(),
            'user_type' => $saved->userType(),
            'item_type' => $saved->itemType(),
        ];

        /** @var SavedAgg $savedAgg */
        $savedAgg = SavedAgg::query()->where($savedAggData)->first();

        if ($savedAgg instanceof HasAmounts) {
            $savedAgg->decrementAmount('total_saved');
        }
    }
}
