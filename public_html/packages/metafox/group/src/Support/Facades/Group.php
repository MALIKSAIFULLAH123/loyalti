<?php

namespace MetaFox\Group\Support\Facades;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use MetaFox\Group\Contracts\SupportContract;
use MetaFox\Platform\Contracts\User;

/**
 * Class Group.
 * @method static int        getMaximumNumberMembershipQuestionOption()
 * @method static int        getMaximumNumberGroupRule()
 * @method static int        getMaximumMembershipQuestion()
 * @method static array      getPrivacyList()
 * @method static array      getMentions(string $content)
 * @method static Collection getGroupsForMention(array $ids)
 * @method static Builder    getGroupBuilder(User $user)
 * @method static Builder    getPublicGroupBuilder(User $user)
 * @method static array      getListTypes()
 * @method static bool       isFollowing(User $context, User $user)
 * @method static array      getProfileMenuSettings(\MetaFox\Group\Models\Group $group)
 * @method static array      getTabNameDefaults(\MetaFox\Group\Models\Group $group)
 * @method static array      getAllowApiRules()
 * @method static array      getInfoSettingsSupportByResolution(string $resolution)
 * @method static int        getTotalMemberByPrivacy(\MetaFox\Group\Models\Group $group)
 * @method static string     getCacheKeyDefaultTabActive(\MetaFox\Group\Models\Group $group)
 * @method static array      statusInviteInfo(int $statusId)
 */
class Group extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SupportContract::class;
    }
}
