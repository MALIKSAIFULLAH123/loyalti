<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Policies;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Models\User as ModelsUser;
use MetaFox\User\Models\User as Resource;
use MetaFox\User\Policies\Contracts\UserPolicyInterface;
use MetaFox\User\Policies\Traits\UserAvatarTrait;
use MetaFox\User\Policies\Traits\UserBannedTrait;
use MetaFox\User\Policies\Traits\UserBlockedTrait;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class UserPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserPolicy implements
    ResourcePolicyInterface,
    UserPolicyInterface
{
    use HasPolicyTrait;
    use UserBlockedTrait;
    use UserBannedTrait;
    use UserAvatarTrait;

    protected string $type = Resource::ENTITY_TYPE;

    public function getEntityType(): string
    {
        return Resource::ENTITY_TYPE;
    }

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('user.view')) {
            return false;
        }

        if ($owner instanceof User) {
            if (!$this->viewOwner($user, $owner)) {
                return false;
            }
        }

        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        if (!$user->hasPermissionTo('user.view')) {
            return false;
        }

        if ($resource instanceof User) {
            if ($this->viewOwner($user, $resource) == false) {
                return false;
            }
        }

        return true;
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        if ($owner == null) {
            return false;
        }

        // Check can view on owner.
        if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
            return false;
        }

        return true;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('user.moderate')) {
            return true;
        }

        if (!$this->updateSetting($user, $resource)) {
            return false;
        }

        return $user->hasPermissionTo('user.update');
    }

    public function updateSetting(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('user.moderate')) {
            return true;
        }

        if ($resource instanceof User) {
            if ($user->entityId() != $resource->userId()) {
                return false;
            }
        }

        return true;
    }

    public function manage(User $context, ?Content $resource = null): bool
    {
        if (!$this->update($context, $resource)) {
            return false;
        }

        if (!$resource instanceof User) {
            return false;
        }

        if ($context->entityId() == $resource->userId()) {
            return true;
        }

        //allows editing when the role id of the context is less than the role id of the person being edited
        return $context->roleId() < $resource->roleId();
    }

    public function feature(User $context, ?Content $resource = null): bool
    {
        if (!$resource instanceof ModelsUser) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if (!$resource->hasVerified()) {
            return false;
        }

        return $context->hasPermissionTo('user.feature');
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof User) {
            return false;
        }

        if ($resource->hasSuperAdminRole()) {
            return false;
        }

        if ($this->deleteOwn($user, $resource)) {
            return true;
        }

        if ($user->roleId() >= $resource?->roleId()) {
            return false;
        }

        if ($user->hasPermissionTo('user.moderate')) {
            return true;
        }

        return false;
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('user.delete')) {
            return false;
        }

        if (!$resource instanceof User) {
            return false;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function viewLocation(User $user, User $owner): bool
    {
        if (!UserPrivacy::hasAccess($user, $owner, 'profile.view_profile')) {
            return false;
        }

        if (UserPrivacy::hasAccess($user, $owner, 'profile.view_location') == false) {
            return false;
        }

        return true;
    }

    public function approve(User $user, ?Content $resource = null): bool
    {
        return $user->hasPermissionTo('user.moderate');
    }

    public function viewAdminCP(User $user): bool
    {
        if (!$user->hasPermissionTo('admincp.has_admin_access')) {
            return false;
        }

        if (!$this->viewAny($user)) {
            return false;
        }

        return true;
    }

    public function uploadCover(User $context, User $user)
    {
        if (!$context->hasPermissionTo('photo.create')) {
            return false;
        }

        return $this->update($context, $user);
    }

    public function editCover(User $context, User $user)
    {
        if (!$this->update($context, $user)) {
            return false;
        }

        if (!$user instanceof ModelsUser) {
            return false;
        }

        return (int) $user->profile?->getCoverId() > 0;
    }

    public function setProfileCover(User $context, User $user)
    {
        if (!$this->update($context, $user)) {
            return false;
        }

        if (!$user->hasPermissionTo('photo.set_profile_cover')) {
            return false;
        }

        return true;
    }
}
