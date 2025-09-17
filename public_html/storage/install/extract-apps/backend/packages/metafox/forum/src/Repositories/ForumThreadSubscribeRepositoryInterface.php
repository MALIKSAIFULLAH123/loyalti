<?php

namespace MetaFox\Forum\Repositories;

use Illuminate\Support\Collection;
use MetaFox\Forum\Models\ForumThreadSubscribe;
use MetaFox\Platform\Contracts\User;

interface ForumThreadSubscribeRepositoryInterface
{
    /**
     * @param  User $context
     * @param  int  $threadId
     * @return bool
     */
    public function subscribe(User $context, int $threadId): bool;

    /**
     * @param  User $context
     * @param  int  $threadId
     * @return bool
     */
    public function unsubscribe(User $context, int $threadId): bool;

    /**
     * @param  User                      $context
     * @param  int                       $threadId
     * @return ForumThreadSubscribe|null
     */
    public function getSubscribed(User $context, int $threadId): ?ForumThreadSubscribe;

    /**
     * @param  array           $threadIds
     * @return Collection|null
     */
    public function getSubscribersOfThreads(array $threadIds = []): ?Collection;
}
