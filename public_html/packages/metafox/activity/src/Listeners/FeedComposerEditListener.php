<?php

namespace MetaFox\Activity\Listeners;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use MetaFox\Activity\Models\Post;
use MetaFox\Activity\Models\Share;
use MetaFox\Activity\Repositories\ShareRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;

class FeedComposerEditListener
{
    /**
     * @param  User|null  $user
     * @param  User|null  $owner
     * @param  mixed      $item
     * @param  array      $params
     * @return bool|array
     */
    public function handle(?User $user, ?User $owner, mixed $item, array $params): ?array
    {
        if (!$user) {
            return null;
        }
        if (!in_array($item?->entityType(), [Post::ENTITY_TYPE, Share::ENTITY_TYPE])) {
            return null;
        }

        if ($item->privacy == MetaFoxPrivacy::CUSTOM) {
            $item->loadPrivacyListAttribute();
        }

        $success = match ($item->entityType()) {
            Post::ENTITY_TYPE  => $this->handlePost($item, $params),
            Share::ENTITY_TYPE => $this->handleShare($item, $params),
        };

        return [
            'success' => $success,
        ];
    }

    /**
     * @param  mixed $share
     * @param  array $params
     * @return bool
     */
    protected function handleShare(mixed $share, array $params): bool
    {
        if (!$share instanceof Share) {
            throw new ModelNotFoundException();
        }

        resolve(ShareRepositoryInterface::class)->updateShare($share, $params);

        return true;
    }

    /**
     * @param  mixed $post
     * @param  array $params
     * @return bool
     */
    protected function handlePost(mixed $post, array $params): bool
    {
        if (!$post instanceof Post) {
            throw new ModelNotFoundException();
        }

        $post->fill($params);

        if ($post->privacy == MetaFoxPrivacy::CUSTOM) {
            $post->setPrivacyListAttribute($params['list']);
        }

        $post->save();

        return true;
    }
}
