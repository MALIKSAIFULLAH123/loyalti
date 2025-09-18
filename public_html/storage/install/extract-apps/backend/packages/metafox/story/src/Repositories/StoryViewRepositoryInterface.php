<?php

namespace MetaFox\Story\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Platform\Contracts\User;
use MetaFox\Story\Models\Story;
use MetaFox\Story\Models\StoryView;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface StoryView.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 * @method StoryView find($id, $columns = ['*'])
 * @method StoryView getModel()
 * @mixin UserMorphTrait
 */
interface StoryViewRepositoryInterface
{
    /**
     * @param User  $context
     * @param array $attributes
     * @return Story
     */
    public function createViewer(User $context, array $attributes): Story;

    /**
     * @param User  $context
     * @param int   $storyId
     * @param array $attributes
     * @return Paginator
     */
    public function viewStoryViewers(User $context, int $storyId, array $attributes): Paginator;

    /**
     * @param User $context
     * @param int  $storyId
     * @return bool
     */
    public function hasSeenStory(User $context, int $storyId): bool;
}
