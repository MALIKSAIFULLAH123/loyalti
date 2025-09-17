<?php

namespace MetaFox\Story\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Story\Models\StorySet;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface StorySet
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 * @method StorySet find($id, $columns = ['*'])
 * @method StorySet getModel()
 *
 * @mixin UserMorphTrait
 */
interface StorySetRepositoryInterface
{
    /**
     * @param User  $context
     * @param array $attributes
     * @return Model
     */
    public function createStorySet(User $context, array $attributes): Model;

    /**
     * @param User  $context
     * @param array $attributes
     * @return Builder
     */
    public function getStorySets(User $context, array $attributes): Builder;

    /**
     * @param User $context
     * @param User $user
     * @return Model|null
     */
    public function getStorySet(User $context, User $user): ?Model;
}
