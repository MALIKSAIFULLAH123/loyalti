<?php

namespace MetaFox\Group\Observers;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Group\Models\Block as Model;
use MetaFox\Group\Repositories\MuteRepositoryInterface;

class BlockObserver
{
    /**
     * @throws AuthorizationException
     */
    public function created(Model $model): void
    {
        $groupId         = $model->group->entityId();
        $userId          = $model->userId();
        $mutedRepository = resolve(MuteRepositoryInterface::class);
        $isMuted         = $mutedRepository->isMuted($groupId, $userId);

        if ($isMuted) {
            $mutedRepository->getUserMuted($groupId, $userId)?->delete();
        }
    }
}
