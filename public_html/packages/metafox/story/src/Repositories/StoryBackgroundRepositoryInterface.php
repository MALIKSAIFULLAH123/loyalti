<?php

namespace MetaFox\Story\Repositories;

use MetaFox\Platform\Contracts\User;
use MetaFox\Story\Models\BackgroundSet;
use MetaFox\Story\Models\StoryBackground;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface StoryBackground.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 * @method StoryBackground find($id, $columns = ['*'])
 * @method StoryBackground getModel()
 */
interface StoryBackgroundRepositoryInterface
{
    /**
     * @param  User          $context
     * @param  BackgroundSet $backgroundSet
     * @param  array         $attributes
     * @return void
     */
    public function uploadBackgrounds(User $context, BackgroundSet $backgroundSet, array $attributes): void;
}
