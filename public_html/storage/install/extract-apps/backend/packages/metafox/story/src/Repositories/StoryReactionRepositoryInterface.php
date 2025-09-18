<?php

namespace MetaFox\Story\Repositories;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Story\Models\Story;
use MetaFox\Story\Models\StoryReaction;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface StoryReaction
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 * @method StoryReaction find($id, $columns = ['*'])
 * @method StoryReaction getModel()
 * @mixin UserMorphTrait
 */
interface StoryReactionRepositoryInterface
{
    /**
     * @param User  $context
     * @param Story $story
     * @param array $attributes
     * @return Story
     */
    public function createReaction(User $context, Story $story, array $attributes): Story;

    /**
     * @param User  $user
     * @param Story $story
     * @return Model|null
     */
    public function getReaction(User $user, Story $story): ?Model;

    /**
     * @param StoryReaction $reaction
     * @return void
     */
    public function deleteNotification(StoryReaction $reaction): void;
}
