<?php

namespace MetaFox\Page\Listeners;

use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\User\Models\User;

class UserVerifiedListener
{
    public function handle(mixed $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        resolve(PageMemberRepositoryInterface::class)->followPagesOnSignup($user);
    }
}
