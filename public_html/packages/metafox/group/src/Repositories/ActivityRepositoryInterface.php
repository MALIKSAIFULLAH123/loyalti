<?php

namespace MetaFox\Group\Repositories;

use MetaFox\Group\Models\Group;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Block
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 * @mixin UserMorphTrait
 */
interface ActivityRepositoryInterface
{
    /**
     * @param User   $context
     * @param Entity $item
     *
     * @return void
     */
    public function createActivity(User $context, Entity $item): void;

    /**
     * @param User   $context
     * @param Entity $item
     * @param array  $attributes
     *
     * @return void
     */
    public function deleteActivity(User $context, Entity $item): void;

    /**
     * @param Group $group
     * @param User  $user
     *
     * @return void
     */
    public function deleteActivities(Group $group, User $user): void;
}
