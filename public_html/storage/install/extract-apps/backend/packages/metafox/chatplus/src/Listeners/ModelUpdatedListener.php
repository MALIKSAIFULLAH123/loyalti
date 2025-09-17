<?php

namespace MetaFox\ChatPlus\Listeners;

use MetaFox\Ban\Models\BanRule;
use MetaFox\ChatPlus\Repositories\ChatServerInterface;
use Metafox\User\Models\User;
use MetaFox\User\Models\UserProfile;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class ModelUpdatedListener
{
    public function handle($model)
    {
        if ($model instanceof User) {
            resolve(ChatServerInterface::class)->updateUser($model->userId());
        }
        if ($model instanceof UserProfile) {
            resolve(ChatServerInterface::class)->updateUser($model->entityId());
        }
        if ($model instanceof BanRule && $model->type_id === 'word') {
            $chatServer = resolve(ChatServerInterface::class);
            if ($model->is_active) {
                $chatServer->addBanWord($model->entityId(), $model->find_value, $model->replacement);
            } else {
                $chatServer->deleteBanWord($model->entityId());
            }
        }
    }
}
