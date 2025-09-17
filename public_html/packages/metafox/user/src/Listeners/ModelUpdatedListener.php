<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Models\User as UserModel;

class ModelUpdatedListener
{
    public function handle($model)
    {
        if ($model instanceof User) {
            UserEntity::updateEntity($model->entityId(), $model->toUserResource());
        }

        if ($model instanceof UserModel) {
            $this->updateSearchName($model);
        }
    }

    protected function updateSearchName(UserModel $user): void
    {
        if ($user->isDirty('full_name') || $user->isDirty('user_name')) {
            $user->updateQuietly(['search_name' => $user->display_name]);
        }
    }
}
