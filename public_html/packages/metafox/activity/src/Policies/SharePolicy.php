<?php

namespace MetaFox\Activity\Policies;

use Illuminate\Support\Arr;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Share;
use MetaFox\Activity\Policies\Traits\CheckPrivacyShareabilityTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\PostBy;
use MetaFox\Platform\Contracts\User as User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class SharePolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SharePolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;
    use CheckPrivacyShareabilityTrait;

    protected string $type = 'share';

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($owner instanceof User) {
            if (!$this->viewOwner($user, $owner)) {
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

        //        if (UserPrivacy::hasAccess($user, $owner, 'blog.view_browse_blogs') == false) {
        //            return false;
        //        }

        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        $owner = $resource->owner;

        if (!$owner instanceof User) {
            return false;
        }

        if ($user->entityId() == $owner->entityId()) {
            return true;
        }

        if (!$this->viewOwner($user, $owner)) {
            return false;
        }

        // Check can view on resource.
        if (!PrivacyPolicy::checkPermission($user, $resource)) {
            return false;
        }

        // Check setting view on resource.

        return true;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        if ($owner instanceof User) {
            if ($owner->entityId() != $user->entityId()) {
                if (!policy_check(FeedPolicy::class, 'viewOwner', $user, $owner)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function shareItem(User $user, ?User $owner = null, ?array $attributes = []): bool
    {
        if (!$this->create($user, $owner)) {
            return false;
        }

        if (!Arr::has($attributes, 'item_type')) {
            return false;
        }

        $itemType         = Arr::get($attributes, 'item_type', '');
        $entityPermission = "$itemType.share";

        if (!$user->hasPermissionTo($entityPermission)) {
            return false;
        }

        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Share) {
            return false;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Share) {
            return false;
        }

        if ($user->hasPermissionTo('feed.moderate')) {
            return true;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if ($resource instanceof Content) {
            if ($user->entityId() != $resource->userId()) {
                return false;
            }
        }

        return true;
    }

    public function saveItem(User $user, Content $resource = null): bool
    {
        if ($resource instanceof Share) {
            $item = $resource->item;

            if (!$item instanceof Content) {
                return false;
            }

            return PolicyGate::check($item->entityType(), 'saveItem', [$user, $item]);
        }

        return false;
    }

    public function isSavedItem(User $user, Content $resource): bool
    {
        if ($resource instanceof Share) {
            $item = $resource->item;

            if (!$item instanceof Content) {
                return false;
            }

            return PolicyGate::check($item->entityType(), 'isSavedItem', [$user, $item]);
        }

        return false;
    }

    public function share(string $entityType, User $user, $resource, mixed $newValue = null): bool
    {
        if (!$resource instanceof Content) {
            return false;
        }

        // improve: FOXSOCIAL5-905
        if (!$resource instanceof HasTotalShare) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if (!$resource instanceof HasPrivacy) {
            return false;
        }

        if (!$this->isPrivacyShareable($resource->privacy)) {
            return false;
        }

        // if resource has "publish" state, it can only be shared when it is published
        if ($resource->isDraft()) {
            return false;
        }

        if (!$user->hasPermissionTo("$entityType.share")) {
            return false;
        }

        if (!$this->checkSpecialSharePermission($resource)) {
            return false;
        }

        $owner = $resource->owner;

        if (!$owner instanceof User) {
            return true;
        }

        if (!$owner->isApproved()) {
            return false;
        }

        if ($owner->entityId() == $user->entityId()) {
            return true;
        }

        if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
            return false;
        }

        if (!$owner instanceof PostBy) {
            return false;
        }

        return $owner->checkContentShareable($user, $owner);
    }

    private function checkSpecialSharePermission(Content $content): bool
    {
        $entityType = $content->entityType();

        if ($entityType != Share::ENTITY_TYPE) {
            return true;
        }

        /**
         * @var Content $item
         */
        $item = $content->item;

        if (null === $item) {
            return false;
        }

        if ($item->entityType() != Feed::ENTITY_TYPE) {
            return true;
        }

        if ($item->item instanceof Entity) {
            return true;
        }

        return false;
    }
}
