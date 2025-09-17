<?php

namespace MetaFox\Story\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Story\Models\Story;
use MetaFox\Story\Models\StoryReaction;
use MetaFox\Story\Models\StoryReactionData;
use MetaFox\Story\Policies\StoryPolicy;
use MetaFox\Story\Repositories\StoryReactionRepositoryInterface;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class StoryReactionRepository
 */
class StoryReactionRepository extends AbstractRepository implements StoryReactionRepositoryInterface
{
    use UserMorphTrait;

    public function model()
    {
        return StoryReaction::class;
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function createReaction(User $context, Story $story, array $attributes): Story
    {
        policy_authorize(StoryPolicy::class, 'like', $context, $story);

        $data = [
            'story_id'  => $story->entityId(),
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ];

        $reaction = $this->getReaction($context, $story);

        if (!$reaction instanceof StoryReaction) {
            $reaction = $this->getModel()->fill($data);
            $reaction->save();
        }

        $this->handleReactionData($reaction, $attributes);

        return $story->refresh();
    }

    protected function handleReactionData(StoryReaction $reaction, array $attributes): void
    {
        $model = new StoryReactionData();

        $model->fill([
            'story_reaction_id' => $reaction->entityId(),
            'item_id'           => Arr::get($attributes, 'reaction_id', 1),
            'item_type'         => StoryReactionData::ITEM_TYPE_DEFAULT,
        ])->save();
    }

    /**
     * @inheritDoc
     */
    public function getReaction(User $user, Story $story): ?Model
    {
        $userId  = $user->entityId();
        $storyId = $story->entityId();

        return LoadReduce::get(
            sprintf('story::reaction(user:%s,story:%s)', $userId, $storyId),
            fn() => $this->getModel()->newQuery()
                ->where('story_id', $storyId)
                ->where('user_id', $userId)
                ->first()
        );
    }

    public function deleteNotification(StoryReaction $reaction): void
    {
        $response = $reaction->toNotification();

        if (is_array($response)) {
            return;
        }

        app('events')->dispatch('notification.delete_mass_notification_by_item', [$reaction], true);
    }
}
