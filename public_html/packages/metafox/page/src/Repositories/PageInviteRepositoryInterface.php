<?php

namespace MetaFox\Page\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageInvite;
use MetaFox\Page\Models\PageInvite as Model;
use MetaFox\Platform\Contracts\User as UserContract;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface Page.
 *
 * @mixin BaseRepository
 * @method Model getModel()
 * @method Model find($id, $columns = ['*'])
 * @mixin UserMorphTrait
 */
interface PageInviteRepositoryInterface
{
    /**
     * @param UserContract         $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     */
    public function viewInvites(UserContract $context, array $attributes): Paginator;

    /**
     * @param int          $pageId
     * @param UserContract $context
     *
     * @return Model|null
     */
    public function getInvite(int $pageId, UserContract $context): ?Model;

    /**
     * @param UserContract $context
     * @param int          $pageId
     * @param array<int>   $userIds
     */
    public function inviteFriends(UserContract $context, int $pageId, array $userIds): void;

    /**
     * @param Page         $page
     * @param UserContract $user
     * @param string       $inviteType
     *
     * @return bool
     */
    public function acceptInviteOnly(Page $page, UserContract $user, string $inviteType = PageInvite::INVITE_MEMBER): bool;

    /**
     * @param Page         $page
     * @param UserContract $user
     *
     * @return bool
     * @throws ValidatorException
     */
    public function acceptInvite(Page $page, UserContract $user): bool;

    /**
     * @param Page         $page
     * @param UserContract $user
     *
     * @return bool
     */
    public function declineInvite(Page $page, UserContract $user): bool;

    /**
     * @param UserContract $context
     * @param int          $pageId
     * @param int          $userId
     *
     * @return bool
     */
    public function deleteInvite(UserContract $context, int $pageId, int $userId): bool;

    /**
     * @param int          $pageId
     * @param UserContract $user
     * @param string       $inviteType
     *
     * @return Model|null
     */
    public function getPendingInvite(
        int          $pageId,
        UserContract $user,
        string       $inviteType = PageInvite::INVITE_MEMBER
    ): ?Model;

    /**
     * @param Page   $page
     * @param string $inviteType
     */
    public function getPendingInvites(Page $page, string $inviteType = PageInvite::INVITE_MEMBER);

    /**
     * @param UserContract $context
     * @param UserContract $user
     * @param int          $pageId
     * @param string       $inviteType
     *
     * @return void
     */
    public function createInvite(UserContract $context, UserContract $user, int $pageId, string $inviteType): void;

    /**
     * @param Page         $page
     * @param UserContract $user
     * @param string       $inviteType
     *
     * @return string
     */
    public function getMessageAcceptInvite(Page $page, UserContract $user, string $inviteType): string;

    /**
     * @param int          $pageId
     * @param UserContract $user
     * @param bool         $notInviteAgain
     *
     * @return bool
     */
    public function handelInviteUnLikedPage(int $pageId, UserContract $user, bool $notInviteAgain): bool;

    /**
     * @param Model $invite
     *
     * @return void
     */
    public function deleteNotification(PageInvite $invite): void;

    /**
     * @param Page   $page
     * @param string $inviteType
     *
     * @return mixed
     */
    public function getBuilderPendingInvites(Page $page, string $inviteType = PageInvite::INVITE_MEMBER): Builder;
}
