<?php
namespace MetaFox\Featured\Listeners;

use MetaFox\Featured\Repositories\ItemRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class DeletedUserListener
{
    public function handle(User $user): void
    {
        resolve(ItemRepositoryInterface::class)->deleteUserData($user);
    }
}
