<?php

namespace MetaFox\Page\Support;

use MetaFox\Page\Contracts\PageMembershipInterface;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageInvite;
use MetaFox\Page\Repositories\PageInviteRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;

class PageMembership implements PageMembershipInterface
{
    public const NO_LIKED = 0;
    public const LIKED    = 1;
    public const INVITED  = 3;

    private PageInviteRepositoryInterface $inviteRepository;

    public function __construct(PageInviteRepositoryInterface $repository)
    {
        $this->inviteRepository = $repository;
    }

    public function getMembership(Page $page, User $user): int
    {
        $memberType = PageInvite::INVITE_MEMBER;
        if ($page->isMember($user)) {
            $memberType = PageInvite::INVITE_ADMIN;
        }

        $invited = LoadReduce::get(
            sprintf('user::isPendingInvited(user:%s,owner:%s)', $user->id, $page->id),
            fn () => null != $this->inviteRepository->getPendingInvite($page->id, $user, $memberType),
        );

        if (null != $invited) {
            return self::INVITED;
        }

        if ($page->isMember($user)) {
            return self::LIKED;
        }

        return self::NO_LIKED;
    }
}
