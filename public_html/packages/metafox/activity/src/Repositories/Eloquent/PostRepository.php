<?php

namespace MetaFox\Activity\Repositories\Eloquent;

use MetaFox\Activity\Models\Post;
use MetaFox\Activity\Repositories\PostRepositoryInterface;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;

class PostRepository extends AbstractRepository implements PostRepositoryInterface
{
    use CollectTotalItemStatTrait;

    public function model()
    {
        return Post::class;
    }

    public function createPost(User $user, User $owner, array $params): ?Post
    {
        $post = new Post();

        $params = array_merge($params, [
            'user_id'    => $user->entityId(),
            'user_type'  => $user->entityType(),
            'owner_id'   => $owner->entityId(),
            'owner_type' => $owner->entityType(),
        ]);

        $post->fill($params);

        if ($post->privacy == MetaFoxPrivacy::CUSTOM) {
            $post->setPrivacyListAttribute($params['list']);
        }

        $post->save();

        return $post;
    }

    public function deleteUserData(int $userId): void
    {
        $posts = $this->getModel()->newModelQuery()
            ->where([
                'user_id' => $userId,
            ])
            ->get();

        foreach ($posts as $post) {
            $post->delete();
        }
    }

    public function deleteOwnerData(int $ownerId): void
    {
        $posts = $this->getModel()->newModelQuery()
            ->where([
                'owner_id' => $ownerId,
            ])
            ->get();

        foreach ($posts as $post) {
            $post->delete();
        }
    }
}
