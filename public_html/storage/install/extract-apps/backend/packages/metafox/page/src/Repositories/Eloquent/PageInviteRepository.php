<?php

namespace MetaFox\Page\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageInvite;
use MetaFox\Page\Models\PageMember;
use MetaFox\Page\Policies\InvitePolicy;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\PageInviteRepositoryInterface;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\Contracts\User as UserContract;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\User\Models\UserEntity as UserEntityModel;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class PageInviteRepository.
 * @method PageInvite getModel()
 * @method PageInvite find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PageInviteRepository extends AbstractRepository implements PageInviteRepositoryInterface
{
    use IsFriendTrait;
    use UserMorphTrait;

    public function model(): string
    {
        return PageInvite::class;
    }

    public function pageRepository(): PageRepositoryInterface
    {
        return resolve(PageRepositoryInterface::class);
    }

    public function pageMemberRepository(): PageMemberRepositoryInterface
    {
        return resolve(PageMemberRepositoryInterface::class);
    }

    /**
     * @throws AuthorizationException
     */
    public function viewInvites(UserContract $context, array $attributes): Paginator
    {
        $pageId = $attributes['page_id'];
        $limit  = $attributes['limit'];
        $search = Arr::get($attributes, 'q', '');

        $page  = $this->pageRepository()->find($pageId);
        $query = $this->getModel()->newQuery();
        policy_authorize(PagePolicy::class, 'view', $context, $page);

        if ($search != '') {
            $query = $query->join('users', 'users.id', '=', 'page_invites.owner_id')
                ->where(function (Builder $builder) use ($search) {
                    $builder->where('users.full_name', $this->likeOperator(), '%' . $search . '%')
                        ->orWhere('users.user_name', $this->likeOperator(), '%' . $search . '%');
                });
        }

        $query->where(function (Builder $builder) {
            $builder->whereNull('expired_at')
                ->orWhere('expired_at', '>=', Carbon::now()->toDateTimeString());
        });

        return $query
            ->with(['userEntity', 'ownerEntity'])
            ->where('page_id', $pageId)
            ->where('status_id', PageInvite::STATUS_PENDING)
            ->where('invite_type', PageInvite::INVITE_MEMBER)
            ->simplePaginate($limit);
    }

    /**
     * @throws AuthorizationException
     */
    public function inviteFriends(UserContract $context, int $pageId, array $userIds): void
    {
        $page = $this->pageRepository()->find($pageId);
        policy_authorize(PagePolicy::class, 'inviteFriends', $context, $page);

        /** @var UserEntityModel[] $users */
        $users       = UserEntity::getByIds($userIds);
        $totalInvite = 0;

        foreach ($users as $user) {
            //Continue if already liked the page
            if ($this->pageMemberRepository()->isPageMember($pageId, $user->entityId())) {
                continue;
            }

            if (!$this->isFriend($context, $user->detail)) {
                continue;
            }

            $invite = $this->getPendingInvite($pageId, $user->detail);

            if (null != $invite) {
                if ($invite->status_id != PageInvite::STATUS_NOT_USE) {
                    continue;
                }

                $invite->update([
                    'user_id'   => $context->entityId(),
                    'user_type' => $context->entityType(),
                    'status_id' => PageInvite::STATUS_PENDING,
                ]);

                $response = $invite->toNotification();
                if (is_array($response)) {
                    Notification::send(...$response);
                }

                continue;
            }

            $this->createInvite($context, $user->detail, $pageId, PageInvite::INVITE_MEMBER);
            $totalInvite++;
        }

        $page->incrementAmount('total_invite', $totalInvite);
    }

    /**
     * @throws AuthorizationException
     */
    public function deleteInvite(UserContract $context, int $pageId, int $userId): bool
    {
        $owner = UserEntity::getById($userId)->detail;
        /** @var PageInvite $invite */
        $invite = $this->builderInviteByOwner($owner, $pageId)
            ->where('status_id', PageInvite::STATUS_PENDING)
            ->first();

        policy_authorize(InvitePolicy::class, 'delete', $context, $invite);

        return (bool) $invite->delete();
    }

    protected function builderInviteByOwner(UserContract $context, int $pageId): Builder
    {
        return $this->getModel()->newQuery()
            ->where('page_id', $pageId)
            ->where('owner_id', $context->entityId())
            ->where('owner_type', $context->entityType());
    }

    public function getInvite(int $pageId, UserContract $context): ?PageInvite
    {
        $invite = $this->builderInviteByOwner($context, $pageId)->first();

        if (!$invite instanceof PageInvite) {
            return null;
        }

        return $invite;
    }

    public function acceptInviteOnly(
        Page         $page,
        UserContract $user,
        string       $inviteType = PageInvite::INVITE_MEMBER
    ): bool
    {
        $invite = $this->getPendingInvite($page->entityId(), $user, $inviteType);

        if (!$invite instanceof PageInvite) {
            return false;
        }

        $invite->update([
            'status_id' => PageInvite::STATUS_APPROVED,
        ]);

        if ($inviteType == PageInvite::INVITE_MEMBER) {
            $page->decrementAmount('total_invite');
        }

        return true;
    }

    public function acceptInvite(Page $page, UserContract $user): bool
    {
        $inviteType = PageInvite::INVITE_MEMBER;

        $memberType = PageMember::MEMBER;

        if ($page->isMember($user)) {
            $inviteType = PageInvite::INVITE_ADMIN;
            $memberType = PageMember::ADMIN;
        }

        $result = $this->acceptInviteOnly($page, $user, $inviteType);

        if (!$result) {
            return false;
        }

        return $this->pageMemberRepository()->addPageMember($page, $user->entityId(), $memberType);
    }

    public function declineInvite(Page $page, UserContract $user): bool
    {
        $inviteType = PageInvite::INVITE_MEMBER;
        if ($page->isMember($user)) {
            $inviteType = PageInvite::INVITE_ADMIN;
        }

        $invite = $this->getPendingInvite($page->entityId(), $user, $inviteType);

        if (!$invite instanceof PageInvite) {
            return false;
        }

        if ($inviteType == PageInvite::INVITE_MEMBER) {
            $page->decrementAmount('total_invite');
        }

        $this->removeNotificationForPendingInvite('page_invite', $invite->entityId(), $invite->entityType());

        return $invite->update(['status_id' => PageInvite::STATUS_NOT_USE]);
    }

    public function getPendingInvite(
        int          $pageId,
        UserContract $user,
        string       $inviteType = PageInvite::INVITE_MEMBER
    ): ?PageInvite
    {
        $invite = $this->getModel()->newQuery()
            ->with(['userEntity', 'ownerEntity'])
            ->where('page_id', $pageId)
            ->where('owner_id', $user->entityId())
            ->where('status_id', PageInvite::STATUS_PENDING)
            ->where('invite_type', $inviteType)
            ->whereDate('expired_at', '>=', Carbon::now()->toDateTimeString())
            ->first();

        if (!$invite instanceof PageInvite) {
            return null;
        }

        return $invite;
    }

    /**
     * @inheritDoc
     */
    public function createInvite(UserContract $context, UserContract $user, int $pageId, string $inviteType): void
    {
        $invite = new PageInvite();
        $data   = [
            'page_id'    => $pageId,
            'owner_id'   => $user->entityId(),
            'owner_type' => $user->entityType(),
        ];
        $invite->fill(array_merge($data, [
            'user_id'     => $context->entityId(),
            'user_type'   => $context->entityType(),
            'status_id'   => PageInvite::STATUS_PENDING,
            'invite_type' => $inviteType,
        ]));
        $invite->save();
    }

    /**
     * @inheritDoc
     */
    public function getMessageAcceptInvite(Page $page, UserContract $user, string $inviteType): string
    {
        $invite = $this->getPendingInvite($page->entityId(), $user, $inviteType);
        if (empty($invite)) {
            return '';
        }

        return match ($invite->getInviteType()) {
            PageInvite::INVITE_ADMIN => __p('page::phrase.you_are_now_a_admin_for_the_page'),
            default                  => __p('page::phrase.liked_successfully'),
        };
    }

    /**
     * @inheritDoc
     */
    public function handelInviteUnLikedPage(int $pageId, UserContract $user, bool $notInviteAgain): bool
    {
        $data = [
            'page_id'    => $pageId,
            'owner_id'   => $user->entityId(),
            'owner_type' => $user->entityType(),
        ];

        $invite = $this->getPendingInvite($pageId, $user);

        if ($invite instanceof PageInvite) {
            $invite->update(['status_id' => $notInviteAgain ? PageInvite::STATUS_NOT_INVITE_AGAIN : PageInvite::STATUS_NOT_USE]);
        }

        if ($notInviteAgain && null == $invite) {
            $invite = (new PageInvite(array_merge($data, [
                'user_id'   => $user->entityId(),
                'user_type' => $user->entityType(),
                'status_id' => PageInvite::STATUS_NOT_INVITE_AGAIN,
            ])))->save();
        }

        if ($invite instanceof PageInvite) {
            app('events')->dispatch(
                'notification.delete_notification_by_type_and_item',
                ['page_invite', $invite->entityId(), $invite->entityType()],
                true
            );
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getPendingInvites(Page $page, string $inviteType = PageInvite::INVITE_MEMBER)
    {
        return $this->getBuilderPendingInvites($page, $inviteType)->get();
    }

    private function removeNotificationForPendingInvite(string $notificationType, int $itemId, string $itemType): void
    {
        app('events')->dispatch(
            'notification.delete_notification_by_type_and_item',
            [$notificationType, $itemId, $itemType],
            true
        );
    }

    public function deleteNotification(PageInvite $invite): void
    {
        $response = $invite->toNotification();

        if (is_array($response)) {
            return;
        }

        app('events')->dispatch('notification.delete_mass_notification_by_item', [$invite], true);
    }

    public function getBuilderPendingInvites(Page $page, string $inviteType = PageInvite::INVITE_MEMBER): Builder
    {
        return $this->getModel()->newQuery()
            ->where('page_id', $page->entityId())
            ->where('status_id', PageInvite::STATUS_PENDING)
            ->whereDate('expired_at', '>=', Carbon::now()->toDateTimeString())
            ->where('invite_type', $inviteType);
    }
}
