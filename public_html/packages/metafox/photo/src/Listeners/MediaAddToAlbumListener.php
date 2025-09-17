<?php

namespace MetaFox\Photo\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

class MediaAddToAlbumListener
{
    private PhotoRepositoryInterface $repository;

    public function __construct(PhotoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param User                 $user
     * @param array<string, mixed> $params
     * @return Content|null
     * @throws AuthorizationException
     */
    public function handle(User $user, array $params): ?Content
    {
        $type = Arr::get($params, 'type');
        $id = Arr::get($params, 'id');

        unset($params['type']);

        unset($params['id']);

        if (Photo::ENTITY_TYPE != $type) {
            return null;
        }

        return $this->repository->updatePhoto($user, $id, $params);
    }
}
