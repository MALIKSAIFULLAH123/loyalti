<?php

namespace MetaFox\Page\Support\Facade;

use Illuminate\Support\Facades\Facade;
use MetaFox\Page\Contracts\PageMembershipInterface;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageInvite;
use MetaFox\Platform\Contracts\User;

/**
 * Class PageMembership.
 *
 * @method static int             getMembership(Page $page, User $user)
 * @method static PageInvite|null getPendingInvite(Page $page, User $user)
 */
class PageMembership extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PageMembershipInterface::class;
    }
}
