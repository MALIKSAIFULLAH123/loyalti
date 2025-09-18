<?php

namespace MetaFox\Page\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageInvite;
use MetaFox\Page\Models\PageMember;
use MetaFox\Page\Policies\PageMemberPolicy;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\ActivityRepositoryInterface;
use MetaFox\Page\Repositories\PageClaimRepositoryInterface;
use MetaFox\Page\Repositories\PageInviteRepositoryInterface;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Page\Support\Browse\Scopes\PageMember\ViewScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Models\UserEntity as UserEntityModel;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Support\Browse\Scopes\User\BlockedScope;
use MetaFox\User\Support\Facades\UserBlocked;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class PageMemberRepository.
 * @method PageMember getModel()
 * @method PageMember find($id, $columns = ['*'])
 */
class PageMemberRepository extends AbstractRepository implements PageMemberRepositoryInterface
{
    use UserMorphTrait;

    public function model(): string
    {
        return PageMember::class;
    }

    private function pageRepository(): PageRepositoryInterface
    {
        return resolve(PageRepositoryInterface::class);
    }

    private function claimRepository(): PageClaimRepositoryInterface
    {
        return resolve(PageClaimRepositoryInterface::class);
    }

    private function inviteRepository(): PageInviteRepositoryInterface
    {
        return resolve(PageInviteRepositoryInterface::class);
    }

    private function userRepository(): UserRepositoryInterface
    {
        return resolve(UserRepositoryInterface::class);
    }

    private function activityRepository(): ActivityRepositoryInterface
    {
        return resolve(ActivityRepositoryInterface::class);
    }

    public function viewPageMembers(User $context, int $pageId, array $attributes): Paginator
    {
        $page = $this->pageRepository()->find($pageId);

        policy_authorize(PageMemberPolicy::class, 'viewAny', $context, $page);

        $search = $attributes['q'];
        $limit = $attributes['limit'];
        $view = $attributes['view'];
        $notInviteRole = $attributes['not_invite_role'] ?? null;
        $excludedUserId = $attributes['excluded_user_id'] ?? null;
        $query = $this->getModel()->newQuery();

        if (in_array($view, [ViewScope::VIEW_ADMIN])) {
            policy_authorize(PageMemberPolicy::class, 'viewAdmins', $context, $page);
        }

        if ($notInviteRole) {
            $invite = $this->inviteRepository()->getPendingInvites($page, PageInvite::INVITE_ADMIN);

            $ownerIds = $invite->collect()->pluck('owner_id')->toArray();
            $query->whereNotIn('page_members.user_id', $ownerIds);
        }

        $viewScope = new ViewScope();
        $viewScope
            ->setView($view)
            ->setPageId($pageId)
            ->setUserContext($context);

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['full_name', 'user_name'], 'users'));
        }

        if ($excludedUserId != null) {
            $query->whereNot('page_members.user_id', $excludedUserId);
        }

        $blockedScope = new BlockedScope();
        $blockedScope->setTable('page_members')
            ->setPrimaryKey('user_id')
            ->setContextId($context->entityId());

        return $query->with(['user', 'page'])
            ->addScope($viewScope)
            ->addScope($blockedScope)
            ->simplePaginate($limit, ['page_members.*']);
    }

    /**
     * @param User $context
     * @param int $pageId
     * @param array $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     * @deprecated v5.2 should use method viewPageMembers instead of viewPageAdmins
     */
    public function viewPageAdmins(User $context, int $pageId, array $attributes): Paginator
    {
        $page = $this->pageRepository()->find($pageId);
        policy_authorize(PageMemberPolicy::class, 'viewAny', $context, $page);

        $search = $attributes['q'];
        $limit = $attributes['limit'];
        $excludedUserId = $attributes['excluded_user_id'] ?? null;

        $query = $this->userRepository()->getModel()->newQuery();

        $viewScope = new ViewScope();
        $viewScope->setPageId($pageId)->setIsViewAdmin(true);

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['full_name']));
        }

        if ($excludedUserId != null) {
            $query->whereNot('page_members.user_id', $excludedUserId);
        }

        return $query->with('profile')
            ->addScope($viewScope)
            ->simplePaginate($limit);
    }

    /**
     * @throws AuthenticationException
     */
    public function addPageAdmin(Page $page, int $userId): bool
    {
        $context = user();
        /** @var User $user */
        $user = $this->userRepository()->find($userId);

        if (!$this->isPageMember($page->entityId(), $user->entityId())) {
            return false;
        }

        if (UserBlocked::isBlocked($context, $user)) {
            return false;
        }

        if (UserBlocked::isBlocked($user, $context)) {
            return false;
        }

        $this->inviteRepository()->createInvite($context, $user, $page->entityId(), PageInvite::INVITE_ADMIN);

        return true;
    }

    public function addPageMember(Page $page, int $userId, int $memberType = PageMember::MEMBER): bool
    {
        /** @var User $user */
        $user = $this->userRepository()->find($userId);

        if ($page->isMember($user) && $memberType == PageMember::ADMIN) {
            $page->incrementAmount('total_admin');

            return $this->getPageMember($page->entityId(), $user->entityId())->update([
                'member_type' => PageMember::ADMIN,
            ]);
        }

        // Create page member.
        parent::create([
            'page_id'     => $page->entityId(),
            'user_id'     => $user->entityId(),
            'user_type'   => $user->entityType(),
            'member_type' => $memberType,
        ]);

        if ($memberType == PageMember::ADMIN) {
            $page->incrementAmount('total_admin');
        }

        return true;
    }

    public function isPageMember(int $pageId, int $userId): bool
    {
        return $this->getModel()->newQuery()
            ->where('page_id', $pageId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function removePageMember(Page $page, int $userId, array $attributes = []): bool
    {
        /** @var User $user */
        $user = $this->userRepository()->find($userId);
        $deleteAllActivities = (bool)Arr::get($attributes, 'delete_activities', false);

        if (!$this->isPageMember($page->entityId(), $user->entityId())) {
            return false;
        }

        /**
         * @var $record PageMember
         */
        $record = $this->getModel()->newModelQuery()
            ->where('page_id', $page->entityId())
            ->where('user_id', $user->entityId())
            ->first();

        if ($deleteAllActivities) {
            app('events')->dispatch('feed.delete_item_by_user_and_owner', [$user, $page], true);
            $this->activityRepository()->deleteActivities($page, $user);
            $this->handleRemoveMemberInvite($page, $user);
        }

        $this->inviteRepository()->handelInviteUnLikedPage($page->entityId(), $user, false);

        return (bool)$record->delete();
    }

    public function likePage(User $context, int $pageId): array
    {
        $page = $this->pageRepository()->find($pageId);

        $this->addPageMember($page, $context->entityId());

        $page->refresh();

        $this->inviteRepository()->acceptInviteOnly($page, $context);

        return [
            'id'         => $page->entityId(),
            'total_like' => $page->total_member,
            'membership' => PageMember::LIKED,
        ];
    }

    public function unLikePage(User $context, int $pageId): array
    {
        $page = $this->pageRepository()->find($pageId);

        policy_authorize(PageMemberPolicy::class, 'unlikePage', $context, $page);

        $this->removePageMember($page, $context->entityId());
        $page->refresh();

        return [
            'id'         => $page->entityId(),
            'total_like' => $page->total_member,
            'membership' => PageMember::NO_LIKE,
        ];
    }

    /**
     * @throws AuthorizationException
     */
    private function checkAddPageAdminPermission(User $context, Page $page): void
    {
        policy_authorize(PagePolicy::class, 'addNewAdmin', $context, $page);
    }

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function addPageAdmins(User $context, int $pageId, array $userIds): bool
    {
        $page = $this->pageRepository()->find($pageId);

        $this->checkAddPageAdminPermission($context, $page);

        foreach ($userIds as $userId) {
            $this->addPageAdmin($page, $userId);
        }

        return true;
    }

    public function deletePageAdmin(User $context, int $pageId, int $userId): bool
    {
        $page = $this->pageRepository()->find($pageId);

        $this->checkAddPageAdminPermission($context, $page);

        return $this->removePageMember($page, $userId);
    }

    public function updatePageMember(User $context, int $pageId, int $userId, int $memberType): bool
    {
        $page = $this->pageRepository()->find($pageId);

        if ($memberType == PageMember::ADMIN) {
            $this->checkAddPageAdminPermission($context, $page);
        }

        $pageMember = $this->getModel()->newQuery()
            ->where('page_id', $page->entityId())
            ->where('user_id', $userId)
            ->firstOrFail();

        $pageMember->update(['member_type' => $memberType]);

        if ($memberType == PageMember::ADMIN) {
            $page->incrementAmount('total_admin');
        }

        return true;
    }

    public function reassignOwner(User $context, int $pageId, int $userId): bool
    {
        $page = $this->pageRepository()->find($pageId);
        $oldUser = $page->user;
        $member = $this->getPageMember($pageId, $userId);

        if ($member instanceof PageMember && $page->isUser($member->user)) {
            abort(403, __p('page::phrase.the_user_is_owner_page', [
                'userFullName' => $member->user->full_name,
            ]));
        }

        policy_authorize(PageMemberPolicy::class, 'reassignOwner', $context, $member);

        $this->getPageMember($pageId, $oldUser->entityId())->update(['member_type' => PageMember::MEMBER]);

        $result = $page->update([
            'user_id'   => $member->userId(),
            'user_type' => $member->userType(),
        ]);

        if ($result) {
            $this->claimRepository()->deleteClaimByUser($member->user, $pageId);
            app('events')->dispatch('page.reassign_owner_end', $page->refresh());
        }

        return $result;
    }

    public function deletePageMember(User $context, int $pageId, int $userId, array $attributes = []): bool
    {
        $page = $this->pageRepository()->find($pageId);

        policy_authorize(PageMemberPolicy::class, 'deletePageMember', $context, $page);

        return $this->removePageMember($page, $userId, $attributes);
    }

    public function removePageAdmin(User $context, int $pageId, int $userId, bool $isDelete): bool
    {
        $page = $this->pageRepository()->find($pageId);
        $member = $this->getPageMember($pageId, $userId);
        policy_authorize(PageMemberPolicy::class, 'removeAsAdmin', $context, $member);

        /** @var User $user */
        $user = $this->userRepository()->find($userId);

        if (!$page->isAdmin($user)) {
            abort(403, __p('page::phrase.the_user_is_not_a_page_admin'));
        }

        if ($page->total_admin > 1) {
            $page->decrementAmount('total_admin');
        }

        if ($isDelete) {
            return $this->deletePageAdmin($context, $pageId, $userId);
        }

        return $this->updatePageMember($context, $pageId, $userId, PageMember::MEMBER);
    }

    /**
     * @inheritDoc
     */
    public function getPageMembers(int $pageId): Collection
    {
        return $this->getModel()->newQuery()
            ->with(['user'])
            ->where('page_id', $pageId)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getPageMember(int $pageId, int $userId): ?PageMember
    {
        return $this->getModel()->newModelQuery()
            ->where('user_id', $userId)
            ->where('page_id', $pageId)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function cancelAdminInvite(User $context, int $pageId, int $userId): bool
    {
        return $this->inviteRepository()->deleteInvite($context, $pageId, $userId);
    }

    private function handleRemoveMemberInvite(Page $page, User $user): void
    {
        $invites = $this->inviteRepository()->getModel()->newModelQuery()
            ->where('page_id', $page->entityId())
            ->where(function ($query) use ($user) {
                $query->where('owner_id', $user->entityId())
                    ->orWhere('user_id', $user->entityId());
            })
            ->get();

        if (empty($invites)) {
            return;
        }

        $invites->each(function (PageInvite $invite) use ($user) {
            $invite->delete();
        });
    }

    public function deleteNotification(PageMember $member): void
    {
        $response = $member->toNotification();

        if (is_array($response)) {
            return;
        }

        app('events')->dispatch('notification.delete_mass_notification_by_item', [$member], true);
    }

    public function followPagesOnSignup(UserModel $user): void
    {
        if (!$user->isApproved() || !$user->hasVerified()) {
            return;
        }

        $pageIds = Settings::get('page.auto_follow_pages_on_signup');

        if (!is_array($pageIds) || !count($pageIds)) {
            return;
        }

        $this->followPagesByIds($user, $pageIds);
    }

    public function followPagesByIds(UserModel $user, array $pageIds): void
    {
        $userEntities = UserEntityModel::query()
            ->with(['detail'])
            ->whereIn('id', $pageIds)
            ->get();

        if (!$userEntities->count()) {
            return;
        }

        $userEntities->each(function (UserEntityModel $userEntity) use ($user) {
            $page = $userEntity->detail;

            if (!$page->isApproved()) {
                return;
            }

            $this->likePage($user, $page->entityId());
        });
    }
}
