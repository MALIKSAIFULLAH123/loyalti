<?php

namespace MetaFox\TourGuide\Policies;

use MetaFox\Platform\Contracts\User;
use MetaFox\TourGuide\Models\TourGuide;
use MetaFox\TourGuide\Repositories\HiddenRepositoryInterface;

/**
 * Class TourGuidePolicy.
 * @ignore
 */
class TourGuidePolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('tour_guide.create');
    }

    public function update(User $user, ?TourGuide $resource): bool
    {
        if (!$resource instanceof TourGuide) {
            return false;
        }

        return $user->hasAdminRole();
    }

    public function view(User $user, ?TourGuide $resource): bool
    {
        if (!$resource instanceof TourGuide) {
            return false;
        }

        if ($this->create($user)) {
            return true;
        }

        if (!$resource->is_active) {
            return false;
        }

        if ($user->isGuest()) {
            return !$resource->isMemberPrivacy();
        }

        if ($resource->isGuestPrivacy()) {
            return false;
        }

        return true;
    }

    public function active(User $user, ?TourGuide $resource): bool
    {
        if (!$resource instanceof TourGuide) {
            return false;
        }

        if ($user->hasAdminRole()) {
            return true;
        }

        return $user->entityId() === $resource->user_id;
    }

    public function delete(User $user, ?TourGuide $resource): bool
    {
        if (!$resource instanceof TourGuide) {
            return false;
        }

        return $user->hasAdminRole();
    }
}
