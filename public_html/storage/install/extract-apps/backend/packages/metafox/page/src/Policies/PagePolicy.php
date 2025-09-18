<?php

namespace MetaFox\Page\Policies;

use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\Page as Resource;
use MetaFox\Page\Repositories\BlockRepositoryInterface;
use MetaFox\Page\Support\Facade\Page as PageFacade;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Policies\Traits\UserAvatarTrait;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class PagePolicy.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PagePolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;
    use UserAvatarTrait;

    protected string $type = Resource::ENTITY_TYPE;

    public function getEntityType(): string
    {
        return Resource::ENTITY_TYPE;
    }

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('page.view')) {
            return false;
        }

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

        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        $isApproved = $resource->isApproved();

        if (!$isApproved && $user->isGuest()) {
            return false;
        }

        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('page.view')) {
            return false;
        }

        if (!$isApproved) {
            if (!$user->hasPermissionTo('page.approve') && $user->entityId() != $resource->userId()) {
                return false;
            }
        }

        $blockRepository = resolve(BlockRepositoryInterface::class);

        if ($blockRepository->isBlocked($resource->entityId(), $user->entityId())) {
            return false;
        }

        // Check can view on owner.
        if (!PrivacyPolicy::checkPermissionOwner($user, $resource->user)) {
            return false;
        }

        return true;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        return $user->hasPermissionTo('page.create');
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Resource) {
            return false;
        }

        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('page.update')) {
            return false;
        }

        return $resource->isAdmin($user);
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        // todo check
        if (!$resource instanceof Resource) {
            return false;
        }

        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }

        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('page.delete')) {
            return false;
        }

        if ($resource instanceof Content) {
            if ($user->entityId() != $resource->userId()) {
                return false;
            }
        }

        return true;
    }

    public function share(User $user, ?Content $resource = null): bool
    {
        return $user->hasPermissionTo('page.share');
    }

    /**
     * @param User          $user
     * @param resource|null $resource
     *
     * @return bool
     */
    public function claim(User $user, Resource $resource = null): bool
    {
        if (!$user->hasPermissionTo('page.claim')) {
            return false;
        }

        if (null == $resource) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if ($resource->isAdmin($user)) {
            return false;
        }

        return true;
    }

    public function moderate(User $user, Resource $resource = null): bool
    {
        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }
        if ($resource == null) {
            return false;
        }

        return $resource->isUser($user);
    }

    /**
     * @param User    $user
     * @param Content $resource
     *
     * @return bool
     */
    public function isPageOwner(User $user, Content $resource): bool
    {
        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    /**
     * @param User    $user
     * @param Content $resource
     *
     * @return bool
     */
    public function isPageAdmin(User $user, Content $resource): bool
    {
        if (!$resource instanceof HasPrivacyMember) {
            return false;
        }

        if (!$resource->isAdmin($user)) {
            return false;
        }

        return true;
    }

    public function message(User $user, Resource $resource = null): bool
    {
        return false;

        if ($user->entityId() == $resource->user->entityId()) {
            return false;
        }

        if ($resource instanceof Content) {
            if (!$resource->isApproved()) {
                return false;
            }
        }

        return true;
    }

    public function viewPublishedDate(User $user, ?Resource $page): bool
    {
        if (null === $page) {
            return false;
        }

        return UserPrivacy::hasAccess($user, $page, 'core.view_publish_date');
    }

    public function report(User $user, ?Resource $resource = null): bool
    {
        if (!$resource instanceof Content) {
            return false;
        }
        if (!$resource->isApproved()) {
            return false;
        }

        if ($resource->userId() == $user->entityId()) {
            return false;
        }

        return $user->hasPermissionTo('page.report');
    }

    public function inviteFriends(User $user, ?Resource $resource = null): bool
    {
        return !$user->isGuest();
    }

    public function postAsParent(User $user, ?Resource $page): bool
    {
        if (null === $page) {
            return false;
        }

        if ($page->isAdmin($user)) {
            return true;
        }

        return false;
    }

    public function uploadCover(User $user, Page $page): bool
    {
        if (!$user->hasPermissionTo('photo.create')) {
            return false;
        }

        if (!$page->isApproved()) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('page.upload_cover')) {
            return false;
        }

        return $this->update($user, $page);
    }

    public function uploadAvatar(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('photo.create')) {
            return false;
        }

        if (!$owner instanceof Resource) {
            return false;
        }

        if (!$owner->isApproved()) {
            return false;
        }

        return $this->update($user, $owner);
    }

    public function editCover(User $user, Page $page): bool
    {
        if (!$page->isApproved()) {
            return false;
        }

        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }

        if (!$this->isPageAdmin($user, $page)) {
            return false;
        }

        return true;
    }

    public function follow(User $user, Page $page): bool
    {
        if (!$page->isApproved()) {
            return false;
        }

        $follow = app('events')->dispatch('follow.can_follow', [$user, $page], true);

        if ($follow == null) {
            return false;
        }

        return $follow;
    }

    public function unfollow(User $user, Page $page): bool
    {
        if (!$page->isApproved()) {
            return false;
        }

        return PageFacade::isFollowing($user, $page);
    }

    public function addNewAdmin(User $user, Page $page): bool
    {
        if (!$page->isApproved()) {
            return false;
        }

        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }

        return $page->isAdmin($user);
    }

    /**
     * @param User        $user
     * @param Entity|null $resource
     *
     * @return bool
     */
    public function manageMenuSetting(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Page) {
            return false;
        }

        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }

        return $resource->isAdmin($user);
    }

    public function unblockFromPage(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Page) {
            return false;
        }

        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }

        return $resource->isAdmin($user);
    }

    public function notifyFollowers(?User $context, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Content) {
            return false;
        }

        if ($resource instanceof Resource) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if (!method_exists($resource, 'toFollowerNotification')) {
            return false;
        }

        if (null === $resource->toFollowerNotification()) {
            return false;
        }

        $owner = $resource->owner;
        $user  = $resource->user;

        if (!$owner instanceof Resource) {
            return false;
        }

        if (!$owner->isAdmin($user)) {
            return false;
        }

        return true;
    }

    public function decline(User $user, ?Resource $page): bool
    {
        if (!$page instanceof Resource) {
            return false;
        }

        if ($user->hasPermissionTo('page.approve')) {
            return true;
        }

        return $this->delete($user, $page);
    }

    public function viewComment(User $user, ?Resource $page): bool
    {
        if (!$page instanceof Resource) {
            return false;
        }

        if (!PrivacyPolicy::checkPermissionOwner($user, $page)) {
            return false;
        }

        return UserPrivacy::hasAccess($user, $page, 'comment.view_browse_comments');
    }

    public function viewBlockedMember(User $user, ?Resource $resource): bool
    {
        if (!$resource instanceof Resource) {
            return false;
        }

        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }

        return $resource->isAdmin($user);
    }
}
