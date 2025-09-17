<?php

namespace MetaFox\Page\Contracts;

use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageInvite;
use MetaFox\Platform\Contracts\User;

interface PageMembershipInterface
{
    public function getMembership(Page $page, User $user): int;
}
