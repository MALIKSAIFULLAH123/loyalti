<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface HasBlockMember.
 */
interface HasBlockMember
{
    public function canBlock(User $context, User $user, Content $resource = null);
}
