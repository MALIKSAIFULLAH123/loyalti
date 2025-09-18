<?php

namespace MetaFox\Page\Policies;

use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\Page as PageResource;
use MetaFox\Page\Models\PageInvite;
use MetaFox\Page\Models\PageMember;
use MetaFox\Page\Repositories\PageInviteRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Support\Facades\UserBlocked;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class PageMemberPolicy.
 */
class PageMemberPolicy
{
    use HasPolicyTrait;

    protected string $type = PageMember::ENTITY_TYPE;

    /**
     * Determine whether the user can view any models.
     *
     * @param User         $user
     * @param PageResource $resource
     *
     * @return bool
     */
    public function viewAny(User $user, PageResource $resource): bool
    {
        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }

        if ($resource->is_approved) {
            return true;
        }

        if ($user->hasPermissionTo('page.approve')) {
            return true;
        }

        return $resource->isAdmin($user);
    }

    /**
     * @param User         $user
     * @param PageResource $resource
     *
     * @return bool
     */
    public function unlikePage(User $user, PageResource $resource): bool
    {
        if (!$resource->isMember($user)) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if ($resource->isUser($user)) {
            return false;
        }

        return true;
    }

    /**
     * @param User         $user
     * @param PageResource $resource
     *
     * @return bool
     */
    public function likePage(User $user, PageResource $resource): bool
    {
        if ($resource->isMember($user)) {
            return false;
        }

        if ($resource->isAdmin($user)) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if ($resource->isUser($user)) {
            return false;
        }

        return PrivacyPolicy::checkPermissionOwner($user, $resource);
    }

    /**
     * @param User         $user
     * @param PageResource $resource
     *
     * @return bool
     */
    public function deletePageMember(User $user, PageResource $resource): bool
    {
        if ($resource->isAdmin($user)) {
            return true;
        }

        return $user->hasPermissionTo('page.moderate');
    }

    /**
     * @param User       $context
     * @param PageMember $resource
     *
     * @return bool
     */
    public function reassignOwner(User $context, PageMember $resource): bool
    {
        $page = $resource->page;

        if ($page->isUser($resource->user)) {
            return false;
        }

        if ($context->hasPermissionTo('page.moderate')) {
            return true;
        }

        if (!$resource->isAdminRole()) {
            return false;
        }

        if ($context->entityId() == $resource->userId()) {
            return false;
        }

        if (UserBlocked::isBlocked($context, $resource->user)) {
            return false;
        }

        if (UserBlocked::isBlocked($resource->user, $context)) {
            return false;
        }

        return $page->isUser($context);
    }

    /**
     * @param User       $context
     * @param PageMember $resource
     *
     * @return bool
     */
    public function removeMemberFromPage(User $context, PageMember $resource): bool
    {
        if (!$resource->isMemberRole()) {
            return false;
        }

        $page = $resource->page;
        $user = $resource->user;

        return $this->hasPermissionForMember($context, $user, $page);
    }

    /**
     * @param User       $context
     * @param PageMember $resource
     *
     * @return bool
     */
    public function setMemberAsAdmin(User $context, PageMember $resource): bool
    {
        if ($context->hasPermissionTo('page.moderate')) {
            return true;
        }

        $policy = PolicyGate::getPolicyFor(Page::class);

        return $policy->isPageAdmin($context, $resource->page);
    }

    /**
     * @param User       $context
     * @param PageMember $resource
     *
     * @return bool
     */
    public function removeAsAdmin(User $context, PageMember $resource): bool
    {
        if (!$resource->isAdminRole()) {
            return false;
        }
        $page = $resource->page;

        if ($page->isUser($resource->user)) {
            return false;
        }

        if ($context->entityId() == $resource->userId()) {
            return true;
        }

        return $page->isUser($context);
    }

    /**
     * @param User       $context
     * @param PageMember $resource
     *
     * @return bool
     */
    public function blockFromPage(User $context, PageMember $resource): bool
    {
        $user = $resource->user;
        $page = $resource->page;

        return match ($resource->member_type) {
            PageMember::MEMBER => $this->deletePageMember($context, $page) &&
                $this->hasPermissionForMember($context, $user, $page),
            default            => false,
        };
    }

    protected function hasPermissionForMember(User $context, User $member, Page $page): bool
    {
        $pagePolicy = PolicyGate::getPolicyFor(Page::class);

        if ($pagePolicy->isPageOwner($member, $page)) {
            return false;
        }

        return $context->hasPermissionTo('page.moderate')
            || $pagePolicy->isPageOwner($context, $page)
            || $pagePolicy->isPageAdmin($context, $page);
    }

    public function viewAdmins(User $user, Page $resource): bool
    {
        return UserPrivacy::hasAccess($user, $resource, 'core.view_admins');
    }

    public function cancelInvite(PageMember $resource): bool
    {
        $inviteRepository = resolve(PageInviteRepositoryInterface::class);
        $invite           = $inviteRepository->getPendingInvite(
            $resource->page->entityId(),
            $resource->user,
            PageInvite::INVITE_ADMIN
        );

        if (empty($invite)) {
            return false;
        }

        return true;
    }
}
