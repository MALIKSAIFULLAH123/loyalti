<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Models\Post;
use MetaFox\Activity\Repositories\PostRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;

class FeedComposerListener
{
    /**
     * @param  User|null  $user
     * @param  User|null  $owner
     * @param  string     $postType
     * @param  array      $params
     * @return array|null
     */
    public function handle(?User $user, ?User $owner, string $postType, array $params): ?array
    {
        if (!$user) {
            return null;
        }

        if ($postType != Post::FEED_POST_TYPE) {
            return null;
        }

        $post = resolve(PostRepositoryInterface::class)->createPost($user, $owner, $params);

        LoadReduce::flush();

        $post->load('activity_feed');

        return [
            'id' => $post->activity_feed ? $post->activity_feed?->entityId() : 0,
        ];
    }
}
