<?php

namespace MetaFox\Story\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Story\Models\Story;
use MetaFox\Story\Models\StoryView;
use MetaFox\Story\Policies\StoryPolicy;
use MetaFox\Story\Repositories\StoryRepositoryInterface;
use MetaFox\Story\Repositories\StoryViewRepositoryInterface;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class StoryViewRepository.
 *
 * @method StoryView find($id, $columns = ['*'])
 * @method StoryView getModel()
 */
class StoryViewRepository extends AbstractRepository implements StoryViewRepositoryInterface
{
    use UserMorphTrait;

    public function model()
    {
        return StoryView::class;
    }

    protected function storyRepository(): StoryRepositoryInterface
    {
        return resolve(StoryRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function createViewer(User $context, array $attributes): Story
    {
        $storyId = Arr::get($attributes, 'story_id');
        $story   = $this->storyRepository()->find($storyId);

        policy_authorize(StoryPolicy::class, 'view', $context, $story);

        $attributes = [
            'story_id'  => $story->entityId(),
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ];

        if ($this->getModel()->newQuery()->where($attributes)->exists()) {
            return $story;
        }

        $this->getModel()->fill($attributes)->save();

        return $story->refresh();
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function viewStoryViewers(User $context, int $storyId, array $attributes): Paginator
    {
        $story = $this->storyRepository()->find($storyId);
        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        policy_authorize(StoryPolicy::class, 'viewViewer', $context, $story);

        return $this->getModel()->newQuery()
            ->with(['story'])
            ->where('story_id', $storyId)
            ->whereNot('user_id', $story->userId())
            ->simplePaginate($limit);
    }

    /**
     * @inheritDoc
     */
    public function hasSeenStory(User $context, int $storyId): bool
    {
        $contextId = $context->entityId();

        return LoadReduce::get(
            sprintf('story_view::exists(user:%s,story:%s)', $contextId, $storyId),
            fn() => $this->getModel()->newQuery()
                ->where('story_id', $storyId)
                ->where('user_id', $contextId)
                ->exists()
        );
    }
}
