<?php

namespace MetaFox\Forum\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Forum\Models\Forum;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Support\Browse\Traits\Moderate\ModeratorPermissionTrait;
use MetaFox\Forum\Support\Browse\Traits\Moderate\UserRolePermissionTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class ForumPostPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ForumPostPolicy implements ResourcePolicyInterface
{
    use HandlesAuthorization;
    use HasPolicyTrait;
    use CheckModeratorSettingTrait;
    use ModeratorPermissionTrait;
    use UserRolePermissionTrait;

    protected string $type = 'forum_post';

    public function create(User $user, ?User $owner = null): bool
    {
        return false;
    }

    public function viewAny(User $user, ?Content $thread = null): bool
    {
        if (!$this->viewForum($user)) {
            return false;
        }

        if (!$this->checkViewThreadPermission($user, $thread)) {
            return false;
        }

        return true;
    }

    private function viewForum(User $user)
    {
        if ($user->hasPermissionTo('forum_thread.moderate')) {
            return true;
        }

        return policy_check(ForumPolicy::class, 'viewAny', $user);
    }

    private function checkViewThreadPermission(User $user, ?Content $thread = null): bool
    {
        if ($thread instanceof ForumThread) {
            return policy_check(ForumThreadPolicy::class, 'view', $user, $thread);
        }

        return true;
    }

    public function view(User $user, Entity $resource = null): bool
    {
        if (null === $resource) {
            return false;
        }

        if (!$this->checkViewThreadPermissionForResource($user, $resource)) {
            return false;
        }

        if (!$this->hasUserRolePermissionAccess($user->roleId(), $resource->thread?->forum?->id, 'can_view_thread_content')) {
            return false;
        }

        if (!PrivacyPolicy::checkPermission($user, $resource)) {
            return false;
        }

        return true;
    }

    private function checkViewThreadPermissionForResource(User $user, ?Content $resource = null): bool
    {
        if (!$this->viewForum($user)) {
            return false;
        }

        if ($resource instanceof Content) {
            $thread = $resource->thread;

            if (!$this->checkViewThreadPermission($user, $thread)) {
                return false;
            }
        }

        return true;
    }

    public function reply(User $user, ?Content $thread, bool $isCloned = false): bool
    {
        if (!$this->viewForum($user)) {
            return false;
        }

        if ($isCloned) {
            return true;
        }

        if (!$thread instanceof ForumThread) {
            return false;
        }

        if (!$this->checkViewThreadPermission($user, $thread)) {
            return false;
        }

        if (!$thread->isApproved()) {
            return false;
        }

        if ($thread->is_closed || $thread->is_wiki) {
            return false;
        }

        if (null === $thread->forum) {
            return false;
        }

        if ($thread->forum->is_closed) {
            return false;
        }

        if ($user->hasPermissionTo('forum_thread.moderate')) {
            return true;
        }

        if (!UserPrivacy::hasAccess($user, $thread->owner, 'forum.reply_forum_thread')) {
            return false;
        }

        if ($this->hasAccess($user->entityId(), $thread->forum->entityId(), 'can_reply')) {
            return true;
        }

        if ($user->hasPermissionTo($this->type . '.reply')) {
            return true;
        }

        return $this->replyOwn($user, $thread);
    }

    public function replyOwn(User $user, ?Content $thread): bool
    {
        if (!$this->viewForum($user)) {
            return false;
        }

        if (!$this->checkViewThreadPermission($user, $thread)) {
            return false;
        }

        if (!$user->hasPermissionTo($this->type . '.reply_own')) {
            return false;
        }

        if ($user->entityId() != $thread->userId()) {
            return false;
        }

        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        /**
         * @var ForumPost $resource
         */
        if (!$this->checkViewThreadPermissionForResource($user, $resource)) {
            return false;
        }

        if (!$resource->thread instanceof ForumThread) {
            return false;
        }

        if (!$resource->thread->is_wiki) {
            if (null === $resource->thread->forum) {
                return false;
            }

            if ($resource->thread->forum->is_closed) {
                return false;
            }
        }

        if ($this->canModerate($user)) {
            return true;
        }

        if ($resource->thread->is_wiki) {
            return $this->updateOwn($user, $resource);
        }

        if ($this->hasAccess($user->entityId(), $resource->thread->forum->entityId(), 'edit_post')) {
            return true;
        }

        return $this->updateOwn($user, $resource);
    }

    protected function updateOwn(User $user, ForumPost $resource): bool
    {
        if (!$user->hasPermissionTo($this->type . '.update_own')) {
            return false;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if (!$this->checkViewThreadPermissionForResource($user, $resource)) {
            return false;
        }

        if ($this->canModerate($user)) {
            return true;
        }

        if (!$resource instanceof ForumPost) {
            return false;
        }

        if (!$resource->isApproved() && $user->hasPermissionTo($this->type . '.approve')) {
            return true;
        }

        if (!$resource->thread instanceof ForumThread) {
            return false;
        }

        if ($resource->thread->is_wiki) {
            return $this->deleteOwn($user, $resource);
        }

        if (!$resource->thread->forum instanceof Forum) {
            return false;
        }

        if ($this->hasAccess($user->entityId(), $resource->thread->forum->entityId(), 'delete_post')) {
            return true;
        }

        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$this->checkViewThreadPermissionForResource($user, $resource)) {
            return false;
        }

        if (!$resource instanceof Content) {
            return false;
        }

        if ($resource->userId() != $user->entityId()) {
            return false;
        }

        return $user->hasPermissionTo($this->type . '.delete_own');
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

    public function approve(User $user, ?Content $content = null): bool
    {
        if ($user->isGuest()) {
            return false;
        }

        if (!$content instanceof ForumPost) {
            return $user->hasPermissionTo($this->type . '.approve');
        }

        if ($content->isApproved()) {
            return false;
        }

        if (!$content->thread instanceof ForumThread) {
            return false;
        }

        if ($content->thread->is_wiki) {
            return $user->hasPermissionTo($this->type . '.approve');
        }

        if (!$content->thread->forum instanceof Forum) {
            return false;
        }

        /*if ($this->hasAccess($user->entityId(), $content->thread->forum->entityId(), 'approve_post')){
            return true;
        }*/

        if ($user->hasPermissionTo($this->type . '.approve')) {
            return true;
        }

        return false;
    }

    public function quote(User $user, ?Content $resource): bool
    {
        if (null === $resource) {
            return false;
        }

        if (!$this->view($user, $resource)) {
            return false;
        }

        if ($resource instanceof Content) {
            if (!$resource->isApproved()) {
                return false;
            }
        }

        $thread = $resource->thread;

        if (null === $thread) {
            return false;
        }

        if ($thread->is_closed) {
            return false;
        }

        if (!$thread->is_wiki) {
            if (null === $thread->forum) {
                return false;
            }

            if ($thread->forum->is_closed) {
                return false;
            }
        }

        if (!$this->reply($user, $thread)) {
            return false;
        }

        return $user->hasPermissionTo($this->type . '.quote');
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        return false;
    }

    //TODO
    public function viewOnProfilePage(User $context, User $owner): bool
    {
        return true;
    }

    public function canModerate(User $user): bool
    {
        return $user->hasPermissionTo('forum_thread.moderate');
    }
}
