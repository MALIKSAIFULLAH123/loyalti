<?php

namespace MetaFox\Follow\Listeners;

use MetaFox\Follow\Support\Traits\IsFollowTrait;
use MetaFox\Platform\Contracts\User;

/**
 * Class CanFollowListener.
 * @ignore
 * @codeCoverageIgnore
 */
class CanFollowListener
{
    use IsFollowTrait;

    public function handle(User $context, User $owner): bool
    {
        return $this->canFollow($context, $owner);
    }
}
