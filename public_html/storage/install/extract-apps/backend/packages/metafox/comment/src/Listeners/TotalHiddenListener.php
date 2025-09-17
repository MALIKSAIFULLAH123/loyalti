<?php

namespace MetaFox\Comment\Listeners;

use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\User;

class TotalHiddenListener
{
    public function __construct(protected CommentRepositoryInterface $repository)
    {
    }

    public function handle(?User $context, HasTotalComment $item): int
    {
        return $this->repository->getTotalHidden($context, $item);
    }
}
