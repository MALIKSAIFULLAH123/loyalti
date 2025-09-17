<?php

namespace MetaFox\Photo\Listeners;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Notifications\DoneProcessingGroupItemsNotification;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;

class DoneProcessingGroupItemsListener
{
    /**
     * @param  int       $groupId
     * @return bool|null
     */
    public function handle(int $groupId): ?bool
    {
        $repository = resolve(PhotoGroupRepositoryInterface::class);
        $group = null;
        try {
            $group = $repository->find($groupId);
        } catch (Exception $e) {
            //Silent the exception
            Log::error($e->getMessage());
        }

        if (!$group instanceof PhotoGroup) {
            return null;
        }

        Notification::send($group->user, new DoneProcessingGroupItemsNotification($group));

        return true;
    }
}
