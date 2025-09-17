<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Group;
use MetaFox\Platform\Contracts\User;

class CanMassInviteListener
{
    public function handle(?Model $model, ?User $user): bool
    {
        if (!$model instanceof Group) {
            return false;
        }

        if ($model->isPublicPrivacy()) {
            return false;
        }

        return true;
    }
}
