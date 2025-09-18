<?php

namespace MetaFox\Group\Support\Facades;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Facade;
use MetaFox\Group\Contracts\MemberContract;
use MetaFox\Group\Models\Group as GroupModel;
use MetaFox\Platform\Contracts\User;

/**
 * @method static Builder getMemberBuilder(User $user, GroupModel $group)
 */
class GroupMember extends Facade
{
    protected static function getFacadeAccessor()
    {
        return MemberContract::class;
    }
}
