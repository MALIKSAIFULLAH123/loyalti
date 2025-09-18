<?php

namespace MetaFox\Group\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Query\Builder;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\GroupInviteCode;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface Group.
 * @mixin BaseRepository
 * @method Group getModel()
 * @method Group find($id, $columns = ['*'])()
 */
interface InfoSettingRepositoryInterface extends HasSponsor, HasFeature
{
    /**
     * @param  int   $groupId
     * @param  User  $context
     * @return array
     */
    public function getInfoSettingsGroup(User $context, int $groupId): array;

    /**
     * @param  User  $context
     * @param  int   $groupId
     * @return array
     */
    public function getAboutSettingsGroup(User $context, int $groupId): array;
}
