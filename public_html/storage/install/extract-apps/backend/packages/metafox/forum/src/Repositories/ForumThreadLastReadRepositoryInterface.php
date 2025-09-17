<?php

namespace MetaFox\Forum\Repositories;

use MetaFox\Platform\Contracts\User;

interface ForumThreadLastReadRepositoryInterface
{
    /**
     * @param  int  $threadId
     * @param  int  $postId
     * @param  User $context
     * @return void
     */
    public function updateLastRead(int $threadId, int $postId, User $context): bool;

    /**
     * @param  User $context
     * @param  int  $threadId
     * @return bool
     */
    public function hasRead(User $context, int $threadId): bool;

    /**
     * @param  User $context
     * @param  int  $threadId
     * @return bool
     */
    public function updateLastView(User $context, int $threadId): bool;
}
