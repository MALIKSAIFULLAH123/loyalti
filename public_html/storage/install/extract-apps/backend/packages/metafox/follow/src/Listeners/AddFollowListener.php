<?php

namespace MetaFox\Follow\Listeners;

use MetaFox\Follow\Repositories\FollowRepositoryInterface;
use MetaFox\Follow\Support\Traits\IsFollowTrait;
use MetaFox\Platform\Contracts\User;

/**
 * Class AddFollowListener.
 * @ignore
 * @codeCoverageIgnore
 */
class AddFollowListener
{
    use IsFollowTrait;

    public function handle(User $context, User $owner): void
    {
        if (!$this->canFollow($context, $owner)) {
            return;
        }

        resolve(FollowRepositoryInterface::class)->follow($context, $owner);
    }
}
