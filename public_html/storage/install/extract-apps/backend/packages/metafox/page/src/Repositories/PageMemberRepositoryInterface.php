<?php

namespace MetaFox\Page\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface PageMember.
 * @mixin BaseRepository
 * @method PageMember getModel()
 * @method PageMember find($id, $columns = ['*'])
 * @mixin  UserMorphTrait
 */
interface PageMemberRepositoryInterface
{
    /**
     * Add an admin into group.
     *
     * @param Page $page
     * @param int  $userId
     *
     * @return bool
     * @throws ValidatorException
     */
    public function addPageAdmin(Page $page, int $userId): bool;

    /**
     * @param int $pageId
     * @param int $userId
     *
     * @return bool
     */
    public function isPageMember(int $pageId, int $userId): bool;

    /**
     * @param Page $page
     * @param int  $userId
     * @param int  $memberType
     *
     * @return bool
     * @throws ValidatorException
     */
    public function addPageMember(Page $page, int $userId, int $memberType = PageMember::MEMBER): bool;

    /**
     * @param User $context
     * @param int  $pageId
     *
     * @return array<string,          mixed>
     * @throws ValidatorException
     * @throws AuthorizationException
     */
    public function likePage(User $context, int $pageId): array;

    /**
     * @param User $context
     * @param int  $pageId
     *
     * @return array<string,          mixed>
     * @throws AuthorizationException
     */
    public function unLikePage(User $context, int $pageId): array;

    /**
     * @param User                 $context
     * @param int                  $pageId
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewPageMembers(User $context, int $pageId, array $attributes): Paginator;

    /**
     * @param User                 $context
     * @param int                  $pageId
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewPageAdmins(User $context, int $pageId, array $attributes): Paginator;

    /**
     * @param User  $context
     * @param int   $pageId
     * @param int[] $userIds
     *
     * @return bool
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function addPageAdmins(User $context, int $pageId, array $userIds): bool;

    /**
     * @param User $context
     * @param int  $pageId
     * @param int  $userId
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deletePageAdmin(User $context, int $pageId, int $userId): bool;

    /**
     * @param Page $page
     * @param int  $userId
     *
     * @return bool
     */
    public function removePageMember(Page $page, int $userId, array $attributes = []): bool;

    /**
     * @param User $context
     * @param int  $pageId
     * @param int  $userId
     * @param int  $memberType
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function updatePageMember(User $context, int $pageId, int $userId, int $memberType): bool;

    /**
     * @param User $context
     * @param int  $pageId
     * @param int  $userId
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function reassignOwner(User $context, int $pageId, int $userId): bool;

    /**
     * @param User $context
     * @param int  $pageId
     * @param int  $userId
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deletePageMember(User $context, int $pageId, int $userId, array $attributes = []): bool;

    /**
     * @param User $context
     * @param int  $pageId
     * @param int  $userId
     * @param bool $isDelete
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function removePageAdmin(User $context, int $pageId, int $userId, bool $isDelete): bool;

    /**
     * @param  int        $pageId
     * @return Collection
     */
    public function getPageMembers(int $pageId): Collection;

    /**
     * @param  int             $pageId
     * @param  int             $userId
     * @return PageMember|null
     */
    public function getPageMember(int $pageId, int $userId): ?PageMember;

    /**
     * @param  User $context
     * @param  int  $pageId
     * @param  int  $userId
     * @return bool
     */
    public function cancelAdminInvite(User $context, int $pageId, int $userId): bool;

    /**
     * @param  PageMember $member
     * @return void
     */
    public function deleteNotification(PageMember $member): void;

    /**
     * @param  UserModel $user
     * @return void
     */
    public function followPagesOnSignup(UserModel $user): void;

    /**
     * @param UserModel $user
     * @param array $pageIds
     * @return void
     */
    public function followPagesByIds(UserModel $user, array $pageIds): void;
}
