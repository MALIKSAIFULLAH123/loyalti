<?php

namespace MetaFox\Group\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use MetaFox\Group\Models\Group;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Group.
 * @mixin BaseRepository
 * @method Group getModel()
 * @method Group find($id, $columns = ['*'])()
 * @mixin UserMorphTrait
 */
interface GroupAdminRepositoryInterface extends HasSponsor, HasSponsorInFeed
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     * @throws AuthorizationException
     */
    public function viewGroups(User $context, array $attributes): Builder;

    /**
     * @param User $context
     * @param int  $id
     * @return Content
     */
    public function approve(User $context, int $id): Content;

    /**
     * @param User $context
     * @param int  $id
     * @return bool
     */
    public function deleteGroup(User $context, int $id): bool;

}
