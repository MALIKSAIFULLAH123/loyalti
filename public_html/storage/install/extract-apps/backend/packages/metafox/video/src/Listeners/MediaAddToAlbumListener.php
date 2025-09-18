<?php

namespace MetaFox\Video\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Repositories\VideoRepositoryInterface;

class MediaAddToAlbumListener
{
    private VideoRepositoryInterface $repository;

    public function __construct(VideoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  User                                           $user
     * @param  array<string, mixed>                           $params
     * @return Content|null
     * @throws AuthorizationException|AuthenticationException
     */
    public function handle(User $user, array $params): ?Content
    {
        $type = $params['type'] ?? null;

        $id = Arr::get($params, 'id');

        unset($params['type']);

        unset($params['id']);

        if (Video::ENTITY_TYPE != $type) {
            return null;
        }

        return $this->repository->updateVideo($user, $id, $params);
    }
}
