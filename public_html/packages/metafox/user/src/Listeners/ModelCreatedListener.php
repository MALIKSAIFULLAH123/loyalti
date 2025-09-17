<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Listeners;

use MetaFox\Platform\Contracts\HasShortcutItem;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;
use MetaFox\User\Repositories\UserShortcutRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;

class ModelCreatedListener
{
    public function userPrivacyRepository()
    {
        return resolve(UserPrivacyRepositoryInterface::class);
    }

    public function handle($model)
    {
        if ($model instanceof User) {
            UserEntity::createEntity($model->entityId(), $model->toUserResource());
        }
        $this->handleShortcut($model);
    }

    protected function handleShortcut($model)
    {
        if ($model instanceof HasShortcutItem) {
            resolve(UserShortcutRepositoryInterface::class)->createdBy($model);
        }
    }
}
