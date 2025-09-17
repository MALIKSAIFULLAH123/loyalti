<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Friend\Repositories\FriendListRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class GetFriendListListener
{
    public function __construct(protected FriendListRepositoryInterface $listRepository) { }

    /**
     * @throws AuthorizationException
     */
    public function handle(?User $context, array $params = []): Collection
    {
        return $this->listRepository->viewFriendLists($context, $params)->get();
    }
}
