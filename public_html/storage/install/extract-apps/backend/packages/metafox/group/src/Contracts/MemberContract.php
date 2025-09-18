<?php

namespace MetaFox\Group\Contracts;

use Illuminate\Database\Query\Builder;
use MetaFox\Group\Models\Group;
use MetaFox\Platform\Contracts\User;

interface MemberContract
{
    /**
     * @param  User    $user
     * @param  Group   $group
     * @return Builder
     */
    public function getMemberBuilder(User $user, Group $group): Builder;
}
