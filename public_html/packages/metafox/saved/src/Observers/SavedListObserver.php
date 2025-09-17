<?php

namespace MetaFox\Saved\Observers;

use MetaFox\Saved\Models\SavedList;
use MetaFox\Saved\Models\SavedListData;
use MetaFox\Saved\Models\SavedListMember;

/**
 * Class SavedListObserver.
 */
class SavedListObserver
{
    public function created(SavedList $savedList): void
    {
        app('events')->dispatch('activitypoint.increase_user_point', [$savedList->user, $savedList, 'create']);
    }

    public function deleted(SavedList $savedList): void
    {
        SavedListData::query()->where(['list_id' => $savedList->entityId()])->delete();

        $savedList->savedListMembers()->each(function (SavedListMember $listMember) {
            $listMember->delete();
        });

        app('events')->dispatch('activitypoint.decrease_user_point', [$savedList->user, $savedList, 'create']);
    }
}
