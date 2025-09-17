<?php

namespace MetaFox\Page\Repositories;

use MetaFox\Page\Models\Page;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Activities
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface ActivityRepositoryInterface
{
    /**
     * @param User   $context
     * @param Entity $item
     * @return void
     */
    public function createActivity(User $context, Entity $item): void;

    /**
     * @param User   $context
     * @param Entity $item
     * @return void
     */
    public function deleteActivity(User $context, Entity $item): void;

    /**
     * @param Page $page
     * @param User $user
     * @return void
     */
    public function deleteActivities(Page $page, User $user): void;
}
