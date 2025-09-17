<?php

namespace MetaFox\ChatPlus\Listeners;

use MetaFox\ChatPlus\Repositories\ChatServerInterface;
use MetaFox\Platform\Contracts\Entity;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class ModelDeletedListener
{
    public function handle($model)
    {
        if (!$model instanceof Entity) {
            return;
        }

        if ($model->entityType() == 'friend') {
            resolve(ChatServerInterface::class)->unFriend($model->userId());
        } elseif ($model->entityType() === 'user') {
            resolve(ChatServerInterface::class)->deleteUser($model->userId());
        } elseif ($model->entityType() === 'user_ban_rule') {
            if ($model?->type_id === 'word') {
                resolve(ChatServerInterface::class)->deleteBanWord($model->entityId());
            }
        }
    }
}
