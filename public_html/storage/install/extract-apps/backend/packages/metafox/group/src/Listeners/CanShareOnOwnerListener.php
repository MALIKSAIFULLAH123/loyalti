<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Group\Models\Group;
use MetaFox\User\Models\User;

class CanShareOnOwnerListener
{
    public function handle(Model $resource, ?User $user, array $params): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($user->isGuest()) {
            return false;
        }

        $owner = $resource->owner;

        if (!$owner instanceof Group) {
            return false;
        }

        if (Arr::get($params, 'owner_id') != $owner->entityId()) {
            return false;
        }

        return true;
    }
}
