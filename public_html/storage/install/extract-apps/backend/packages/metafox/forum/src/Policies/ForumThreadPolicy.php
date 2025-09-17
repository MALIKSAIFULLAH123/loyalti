<?php

namespace MetaFox\Forum\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Forum\Models\Forum;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Support\Browse\Traits\Moderate\ModeratorPermissionTrait;
use MetaFox\Forum\Support\Browse\Traits\Moderate\UserRolePermissionTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasUserProfile;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class ForumThreadPolicy.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ForumThreadPolicy implements ResourcePolicyInterface
{
    use HandlesAuthorization;
    use CheckModeratorSettingTrait;
    use HasPolicyTrait;
    use ModeratorPermissionTrait;
    use UserRolePermissionTrait;

    protected string $type = ForumThread::ENTITY_TYPE;

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($user->hasPermissionTo('forum_thread.moderate')) {
            return true;
        }

        if (!$this->viewForum($user)) {
            return false;
        }

        if ($owner instanceof User) {
            if (!$this->viewOwner($user, $owner)) {
                return false;
            }
        }

        return true;
    }

    private function viewForum(User $user)
    {
        return policy_check(ForumPolicy::class, 'viewAny', $user);
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        if ($owner == null) {
            return false;
        }

        // Check can view on owner.
        if (PrivacyPolicy::checkPermissionOwner($user, $owner) === false) {
            return false;
        }

        if ($user->hasPermissionTo('forum_thread.moderate')) {
            return true;
        }

        if (!$this->viewForum($user)) {
            return false;
        }

        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        $owner = $resource->owner;

        if (!$owner instanceof User) {
            return false;
        }

        $isApproved = $resource->isApproved();

        if (!$isApproved && $user->isGuest()) {
            return false;
        }

        /*
         * The 'moderator' permission of the item should be checked before checking the privacy setting of the item on the owner (User/Page/Group).
         */
        if ($user->hasPermissionTo($this->type . '.moderate')) {
            return true;
        }

        if ($resource->forum_id && !$this->hasUserRolePermissionAccess($user->roleId(), $resource?->forum?->id, 'can_view_thread_content')) {
            return false;
        }

        if (!$this->viewForum($user)) {
            return false;
        }

        if (!$this->viewOwner($user, $owner)) {
            return false;
        }

        // Check can view on resource.
        if (PrivacyPolicy::checkPermission($user, $resource) == false) {
            return false;
        }

        if (!$isApproved) {
            if ($user->hasPermissionTo('forum_thread.approve')) {
                return true;
            }

            if ($user->entityId() == $resource->userId()) {
                return true;
            }

            return false;
        }

        return true;
    }

    public function updateLastRead(User $context, Content $resource): bool
    {
        if (!$this->view($context, $resource)) {
            return false;
        }

        return $context->entityId() > 0;
    }

    /**
     * @deprecated
     */
    public function create(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo($this->type . '.create')) {
            return false;
        }

        if ($owner instanceof User) {
            // Check can view on owner.
            if ($owner->entityId() != $user->entityId()) {
                if ($owner->entityType() == 'user') {
                    return false;
                }

                if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
                    return false;
                }

                if (!UserPrivacy::hasAccess($user, $owner, 'forum.share_forum_thread')) {
                    return false;
                }
            }
        }

        if (!$this->checkCreateInPageGroup($user, $owner)) {
            return false;
        }

        return true;
    }

    public function createGenerate(User $user, ?User $owner = null): bool
    {
        if ($owner instanceof User) {
            // Check can view on owner.
            if ($owner->entityId() != $user->entityId()) {
                if ($owner->entityType() == 'user') {
                    return false;
                }

                if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
                    return false;
                }

                if (!UserPrivacy::hasAccess($user, $owner, 'forum.share_forum_thread')) {
                    return false;
                }
            }
        }

        if (!$this->checkCreateInPageGroup($user, $owner)) {
            return false;
        }

        return true;
    }

    protected function hasModeratorPermissionOnCreating(User $user, ?int $forumId = null): bool
    {
        if (!$forumId) {
            return false;
        }

        if ($this->hasAccess($user->entityId(), $forumId, 'add_thread')) {
            return false;
        }

        if ($this->hasUserRolePermissionAccess($user->roleId(), $forumId, 'can_start_thread')) {
            return true;
        }

        return false;
    }

    public function hasCreationPermission(User $user, ?int $forumId = null): bool
    {
        if ($forumId && resolve(ForumRepositoryInterface::class)->isClosed($forumId)) {
            return false;
        }

        if ($user->hasPermissionTo($this->type . '.create')) {
            return true;
        }

        if ($this->hasModeratorPermissionOnCreating($user, $forumId)) {
            return true;
        }

        return false;
    }

    public function createOnForum(User $user, ?User $owner = null, ?int $forumId = null): bool
    {
        if (!$this->hasCreationPermission($user, $forumId)) {
            return false;
        }

        if ($owner instanceof User) {
            // Check can view on owner.
            if ($owner->entityId() != $user->entityId()) {
                if ($owner->entityType() == 'user') {
                    return false;
                }

                if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
                    return false;
                }

                if (!UserPrivacy::hasAccess($user, $owner, 'forum.share_forum_thread')) {
                    return false;
                }
            }
        }

        if (!$this->checkCreateInPageGroup($user, $owner)) {
            return false;
        }

        return true;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo($this->type . '.moderate')) {
            return true;
        }

        if (!$this->viewForum($user)) {
            return false;
        }

        if (!$resource instanceof Content) {
            return false;
        }

        if (!$resource->isApproved() && $user->hasPermissionTo($this->type . '.approve')) {
            return true;
        }

        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo($this->type . '.delete_own')) {
            return false;
        }

        if (!$resource instanceof Content) {
            return false;
        }

        $owner = $resource->owner;

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        if (!$owner instanceof HasPrivacyMember) {
            return $user->entityId() == $resource->userId();
        }

        if (!$resource->isApproved()) {
            return $this->checkModeratorSetting($user, $owner, 'approve_or_deny_post');
        }

        return $this->checkModeratorSetting($user, $owner, 'remove_post_and_comment_on_post');
    }

    public function subscribe(User $user, Content $resource): bool
    {
        if (!$this->checkResourcePermission('subscribe', $user, $resource, false, false)) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        return true;
    }

    public function checkResourcePermission(
        string $permission,
        User $user,
        ?Content $resource = null,
        bool $disallowWiki = false,
        bool $checkViewForum = true
    ): bool {
        if (null === $resource) {
            return false;
        }

        if ($checkViewForum && !$this->viewForum($user)) {
            return false;
        }

        if (!$user->hasPermissionTo($this->type . '.' . $permission)) {
            return false;
        }

        if (!PrivacyPolicy::checkPermission($user, $resource)) {
            return false;
        }

        if ($disallowWiki && $resource->isWiki()) {
            return false;
        }

        return true;
    }

    public function stick(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof ForumThread) {
            return false;
        }

        if (!$resource?->owner instanceof HasUserProfile) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if (!$this->checkClosedForum($resource)) {
            return false;
        }

        if ($resource->is_wiki) {
            return false;
        }

        if (!$resource->forum instanceof Forum) {
            return false;
        }

        if ($this->hasAccess($user->entityId(), $resource->forum->entityId(), 'post_sticky')) {
            return true;
        }

        if ($this->checkResourcePermission('stick', $user, $resource)) {
            return true;
        }

        return false;
    }

    public function close(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof ForumThread) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if ($resource->is_wiki) {
            return false;
        }

        if (!$this->checkClosedForum($resource)) {
            return false;
        }

        if (!$resource->forum instanceof Forum) {
            return false;
        }

        if ($user->hasPermissionTo($this->type . '.moderate')) {
            return true;
        }

        if (!$this->viewForum($user)) {
            return false;
        }

        if ($this->hasAccess($user->entityId(), $resource->forum->entityId(), 'close_thread')) {
            return true;
        }

        if (!$user->hasPermissionTo($this->type . '.close_own')) {
            return false;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function move(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof ForumThread) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if ($resource->is_wiki) {
            return false;
        }

        if (!$this->checkClosedForum($resource)) {
            return false;
        }

        if ($user->hasPermissionTo($this->type . '.moderate')) {
            return true;
        }

        if ($this->hasAccess($user->entityId(), $resource->forum->entityId(), 'move_thread')) {
            return true;
        }

        if (!$user->hasPermissionTo($this->type . '.move')) {
            return false;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if (null === $resource) {
            return false;
        }

        if ($resource->forum instanceof Forum && $resource->forum->is_closed) {
            return false;
        }

        if ($user->hasPermissionTo($this->type . '.moderate')) {
            return true;
        }

        if (!$this->viewForum($user)) {
            return false;
        }

        return $this->updateOwn($user, $resource);
    }

    public function updateOwn(User $user, ?Content $resource = null): bool
    {
        if ($user->hasPermissionTo($this->type . '.update_own') === true) {
            if ($user->entityId() == $resource->userId()) {
                return true;
            }
        }

        return false;
    }

    public function copy(User $user, ?User $owner, ?Content $resource): bool
    {
        if (!$resource instanceof ForumThread) {
            return false;
        }

        if (null === $owner) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if (!$this->viewForum($user)) {
            return false;
        }

        if ($resource->forum instanceof Forum && $resource->forum->is_closed) {
            return false;
        }

        if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
            return false;
        }

        if (!$this->checkCreateInPageGroup($user, $owner)) {
            return false;
        }

        if ($user->hasPermissionTo($this->type . '.copy')) {
            return true;
        }

        if (null === $resource->forum) {
            return false;
        }

        if (!$this->hasAccess($user->entityId(), $resource->forum->entityId(), 'copy_thread')) {
            return false;
        }

        return true;
    }

    public function merge(User $user, ?Content $resource = null): bool
    {
        if (null === $resource) {
            return true;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if ($resource->is_wiki) {
            return false;
        }

        if (!$this->checkClosedForum($resource)) {
            return false;
        }

        if (!$resource->forum instanceof Forum) {
            return false;
        }

        if ($user->hasPermissionTo($this->type . '.moderate')) {
            return true;
        }

        if (!$this->viewForum($user)) {
            return false;
        }

        if ($this->hasAccess($user->entityId(), $resource->forum->entityId(), 'merge_thread')) {
            return true;
        }

        if (!$user->hasPermissionTo($this->type . '.merge_own')) {
            return false;
        }

        if ($resource->userId() != $user->entityId()) {
            return false;
        }

        return true;
    }

    public function approve(User $user, ?Content $resource = null): bool
    {
        if ($user->isGuest()) {
            return false;
        }

        if (!$resource instanceof ForumThread) {
            return $user->hasPermissionTo($this->type . '.approve');
        }

        if ($resource->isApproved()) {
            return false;
        }

        if ($resource->is_wiki) {
            return $this->checkResourcePermission('approve', $user, $resource);
        }

        if (!$resource->forum instanceof Forum) {
            return false;
        }

        /*if ($this->hasAccess($user->entityId(), $resource->forum->entityId(), 'approve_thread')) {
            return true;
        }*/

        if ($this->checkResourcePermission('approve', $user, $resource)) {
            return true;
        }

        return false;
    }

    public function attachPoll(User $user, ?Content $resource = null): bool
    {
        if (!$user->hasPermissionTo('forum_thread.attach_poll')) {
            return false;
        }

        if (null === $resource) {
            return true;
        }

        return $this->update($user, $resource);
    }

    public function autoApprove(User $context, User $owner): bool
    {
        if (!$owner instanceof HasPrivacyMember) {
            return $context->hasPermissionTo("$this->type.auto_approved");
        }

        if (!$owner->hasPendingMode()) {
            return true;
        }

        if ($owner->isPendingMode()) {
            return $this->checkModeratorSetting($context, $owner, 'approve_or_deny_post');
        }

        return true;
    }

    protected function checkCreateInPageGroup(User $user, ?User $owner): bool
    {
        if (!$owner instanceof HasPrivacyMember) {
            return true;
        }

        if ($owner->isAdmin($user)) {
            return true;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if (!UserPrivacy::hasAccess($user, $owner, 'forum.share_forum_thread')) {
            return false;
        }

        return true;
    }

    protected function checkClosedForum(Content $resource): bool
    {
        if (null === $resource->forum) {
            return false;
        }

        if ($resource->forum->is_closed) {
            return false;
        }

        return true;
    }

    public function createWiki(User $user, User $owner, ?Content $resource = null): bool
    {
        if (!$resource instanceof Content) {
            return false;
        }

        if (!$resource instanceof ForumThread) {
            return false;
        }

        if ($owner instanceof HasPrivacyMember) {
            return false;
        }

        return $user->hasPermissionTo('forum_thread.create_as_wiki');
    }
}
